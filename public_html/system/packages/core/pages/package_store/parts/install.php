<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;

$errors = [];
if (!isset($_GET['install']) && !isset($_GET['update']) && !isset($_GET['uninstall'])) {
    Core::redirectTo('');
}

$version_map = [];
$args = [];

// parse arguments
foreach (['install', 'update', 'uninstall'] as $arg) {
    $args[$arg] = [];
    $pkgs = explode(',', str_ireplace(' ', '', $_GET[$arg]));
    foreach ($pkgs as $pkg) {
        if (strlen(trim($pkg)) == 0)
            continue;
        $parts = explode('==', $pkg);
        if (count($parts) != 2)
            Core::throwError(sprintf(
                'Format error. Package name "%s" must have the format "PACKAGE==VERSION"', $pkg
            ));
        array_push($args[$arg], $parts[0]);
        $version_map[$parts[0]] = $parts[1];
    }
}

$to_install = $args['install'];
$to_update = $args['update'];
$to_uninstall = $args['uninstall'];

// get info about codebase
$codebase_info = Core::getCodebaseInfo();
$compose_version =
    ($codebase_info['latest_tag'] == 'ND')? null : explode('-', $codebase_info['latest_tag'])[0];

// read index
$assets_url = Configuration::$ASSETS_STORE_URL;
$assets_branch = Configuration::$ASSETS_STORE_VERSION;
$assets_index_url = join_path($assets_url, $assets_branch, 'index.json');
$content = file_get_contents($assets_index_url);
if (!$content) {
    $error = error_get_last();
    Core::throwError(
        sprintf(
            'An error occurred while retrieving the assets index. The error is (%s)',
            $error
        )
    );
}
$index = json_decode($content, true);
$available_packages = array_keys($index['packages']);

// validate given versions
foreach ($version_map as $pkg => $pkg_version) {
    if (!array_key_exists($pkg, $index['packages'])) {
        Core::throwError(sprintf('Package "%s" not found in the Assets Store index.', $pkg));
    }
    if (!array_key_exists($pkg_version, $index['packages'][$pkg]['versions'])) {
        Core::throwError(sprintf('Version "%s" not found for package "%s".', $pkg_version, $pkg));
    }
}

// get list of installed packages
$installed_packages = Core::getPackagesList();
$installed_packages_keys = array_keys($installed_packages);

// remove packages that are not available
$to_install = array_intersect($to_install, $available_packages);
$to_update = array_intersect($to_update, $available_packages);
$to_uninstall = array_diff(
    array_intersect($to_uninstall, $installed_packages_keys),
    ['core']
);

// compute dependency tree (uninstall)
$processed = [];
$dependencies = $to_uninstall;
$num_dependencies = count($dependencies);
while ($num_dependencies > 0) {
    $num_dependencies = count($dependencies);
    foreach (array_diff($dependencies, $processed) as $package_id) {
        $deps = [];
        foreach ($installed_packages as $installed_pack_id => $installed_pack) {
            if (in_array($package_id, $installed_pack['dependencies']['packages'])) {
                array_push($deps, $installed_pack_id);
            }
        }
        $dependencies = array_merge($dependencies, $deps);
        array_push($processed, $package_id);
    }
    $dependencies = array_unique($dependencies);
    $num_dependencies = count($dependencies) - $num_dependencies;
}
$to_uninstall_full = array_diff(
    array_intersect($dependencies, $installed_packages_keys),
    ['core']
);

// function that returns the latest (yet compatible) version of a package
function get_package_latest_version ($pkg) {
    global $index;
    global $available_packages;
    global $compose_version;
    // ---
    $version = null;
    $ver = function ($v){
        return substr($v, 1);
    };
    // ---
    if (!in_array($pkg, $available_packages))
        return null;
    // ---
    foreach ($index['packages'][$pkg]['versions'] as $v => $vinfo) {
        $compatibility = $vinfo['compatibility']['compose'];
        if (version_compare($ver($compose_version), $ver($compatibility['minimum']), '>=') &&
            version_compare($ver($compose_version), $ver($compatibility['maximum']), '<=')
            ) {
            if (is_null($version)) $version = $v;
            if (version_compare($ver($v), $ver($version), '>')) $version = $v;
        }
    }
    // ---
    return $version;
}

// compute dependency tree (install/update)
$processed = [];
$dependencies = array_unique(array_merge($to_install, $to_update));
$num_dependencies = count($dependencies);
while ($num_dependencies > 0) {
    $num_dependencies = count($dependencies);
    foreach (array_diff($dependencies, $processed) as $pkg) {
        $pkg_version = array_key_exists($pkg, $version_map)?
            $version_map[$pkg] : get_package_latest_version($pkg);
        if (is_null($pkg_version)) {
            Core::throwError(
                    sprintf('No compatible versions found for dependency package "%s".', $pkg));
        }
        $version_map[$pkg] = $pkg_version;
        $deps = $index['packages'][$pkg]['versions'][$pkg_version]['dependencies'];
        $dependencies = array_merge($dependencies, $deps);
        array_push($processed, $pkg);
    }
    $dependencies = array_unique($dependencies);
    $num_dependencies = count($dependencies) - $num_dependencies;
}
$to_install_full = array_intersect(
    array_diff($dependencies, $installed_packages_keys),
    $available_packages
);
$to_update_full = array_intersect(
    $to_update, $installed_packages_keys
);

// make sure that there is no conflict between install/uninstall operations
$in_out_conflicts = array_intersect($to_install_full, $to_uninstall_full);
$up_out_conflicts = array_intersect($to_update_full, $to_uninstall_full);
if (count($in_out_conflicts) + count($up_out_conflicts) > 0) {
    foreach ($in_out_conflicts as $package) {
        array_push(
            $errors,
            sprintf('ERROR: The package "%s" is in the list of packages to install and uninstall', $package)
        );
    }
    foreach ($up_out_conflicts as $package) {
        array_push(
            $errors,
            sprintf('ERROR: The package "%s" is in the list of packages to update and uninstall', $package)
        );
    }
    Core::openAlert('danger', implode('\n', $errors));
} else {
    if (isset($_GET['confirm']) && $_GET['confirm'] == '1') {
        // append version to packages
        $fcn = function ($x) use ($version_map){return sprintf("%s==%s", $x, $version_map[$x]);};
        $to_install_versioned = array_map($fcn, $to_install_full);
        $to_update_versioned = array_map($fcn, $to_update_full);
        // perform install/uninstall operations in batch
        $res = Core::packageManagerBatch(
            $to_install_versioned, $to_update_versioned, $to_uninstall_full
        );
        if (!$res['success']) {
            $error_str = implode('<br/>&nbsp;', [
                "Package Manager Errors:",
                implode('<br/>&nbsp;&nbsp;', $res['data'])
            ]);
            Core::throwError($error_str);
        }
        // redirect to verification page
        $href = sprintf(
            'package_store/verify?install=%s&update=%s&uninstall=%s',
            implode(',', $to_install_full),
            implode(',', $to_update_full),
            implode(',', $to_uninstall_full)
        );
        Core::redirectTo($href);
    }
}
?>

<style type="text/css">
    .panel > .panel-heading a {
        color: inherit;
        text-decoration: none;
    }

    select.form-control {
        height: 26px !important;
    }
</style>


<h2 class="page-title"></h2>

<?php
$actions = [
    [
        'name' => 'install',
        'color' => 'green',
        'data' => $to_install,
        'data_full' => $to_install_full
    ],
    [
        'name' => 'update',
        'color' => 'deepskyblue',
        'data' => $to_update,
        'data_full' => $to_update_full
    ],
    [
        'name' => 'uninstall',
        'color' => 'red',
        'data' => $to_uninstall,
        'data_full' => $to_uninstall_full
    ]
];

foreach ($actions as $action) {
    $name = $action['name'];
    $color = $action['color'];
    $data = $action['data'];
    $data_full = $action['data_full'];
    if (count($data_full) <= 0) {
        continue;
    }
    ?>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab">
            <a aria-expanded="true" aria-controls="">
                <h4 class="panel-title">
                    <span class="fa fa-download" style="color:<?php echo $color ?>"
                          aria-hidden="true"></span>
                    &nbsp;
                    <strong>Packages to <?php echo $name ?>:</strong>
                </h4>
            </a>
        </div>
        <div class="panel-collapse collapse in" role="tabpanel" aria-labelledby="">
            <div class="panel-body">
                <?php
                foreach ($data as $package_id) {
                    $package_name = $index['packages'][$package_id]['name'];
                    echo sprintf('<h5>&bullet; %s (<span class="mono" style="color:grey">%s</span>)</h5>',
                        $package_name,
                        $package_id
                    );
                }
                if (count(array_diff($data_full, $data)) > 0) {
                    ?>
                    <h5 class="text-bold" style="margin-top:20px">Dependencies:</h5>
                    <div style="padding-left:20px">
                        <?php
                        foreach (array_diff($data_full, $data) as $package_id) {
                            $package_name = $index['packages'][$package_id]['name'];
                            echo sprintf('<h5>&bullet; %s (<span class="mono" style="color:grey">%s</span>)</h5>',
                                $package_name,
                                $package_id
                            );
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}
?>

<a class="btn btn-success <?php echo (count($errors) > 0) ? 'disabled' : '' ?>"
   role="button"
   style="float:right"
   onclick="process_packages<?php echo count($to_uninstall_full) > 0 ? '_confirm' : '' ?>()"
   href="javascript:void(0);">
    <i class="fa fa-check" aria-hidden="true"></i>
    &nbsp;
    Confirm
</a>


<?php
$href = sprintf(
    'install?install=%s&update=%s&uninstall=%s&confirm=%s',
    $_GET['install'],
    $_GET['update'],
    $_GET['uninstall'],
    '1'
);
?>

<script type="text/javascript">
    function process_packages() {
        showPleaseWait();
        location.href = "<?php echo $href ?>";
    }

    function process_packages_confirm() {
        openYesNoModal(
            'Are you sure you want to proceed?<br/>The data associated with these packages will be deleted as well.',
            process_packages,
            true /*silentMode*/
        );
    }
</script>

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

$to_install = explode(',', str_ireplace(' ', '', $_GET['install']));
$to_update = explode(',', str_ireplace(' ', '', $_GET['update']));
$to_uninstall = explode(',', str_ireplace(' ', '', $_GET['uninstall']));

// read index
$assets_url = Configuration::$ASSETS_STORE_URL;
$assets_branch = Configuration::$ASSETS_STORE_BRANCH;
$assets_index_url = sprintf('%s/%s/index', $assets_url, $assets_branch);
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
$data = json_decode($content, true);
$available_packages = [];
foreach ($data['packages'] as $package) {
    $available_packages[$package['id']] = $package;
}

// get list of installed packages
$installed_packages = Core::getPackagesList();

// remove packages that are not available
$to_install = array_intersect($to_install, array_keys($available_packages));
$to_update = array_intersect($to_update, array_keys($available_packages));
$to_uninstall = array_diff(
    array_intersect($to_uninstall, array_keys($installed_packages)),
    ['core']
);

// compute tree of dependencies (uninstall)
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
    array_intersect($dependencies, array_keys($installed_packages)),
    ['core']
);

// compute tree of dependencies (install/update)
$providers_source = [
    'github.com' => 'https://raw.githubusercontent.com/%s/%s/%s',
    'bitbucket.org' => 'https://bitbucket.org/%s/%s/raw/%s'
];
$processed = [];
$dependencies = array_unique(array_merge($to_install, $to_update));
$num_dependencies = count($dependencies);
while ($num_dependencies > 0) {
    $num_dependencies = count($dependencies);
    foreach (array_diff($dependencies, $processed) as $package_id) {
        $package_info = $available_packages[$package_id];
        if (!array_key_exists($package_info['git_provider'], $providers_source)) {
            Core::throwError(
                sprintf(
                    'Provider "%s" for package "%s" not supported.',
                    $package_info['git_provider'],
                    $package_id
                )
            );
        }
        $package_metadata_url = sprintf(
            $providers_source[$package_info['git_provider']] . '/metadata.json',
            $package_info['git_owner'],
            $package_info['git_repository'],
            $package_info['git_branch']
        );
        $content = file_get_contents($package_metadata_url);
        if (!$content) {
            $error = error_get_last();
            Core::throwError(
                sprintf(
                    'An error occurred while retrieving info about the package "%s". The error is (%s)',
                    $package_id,
                    $error
                )
            );
        }
        $package_metadata = json_decode($content, True);
        $available_packages[$package_id]['metadata'] = $package_metadata;
        $deps = $package_metadata['dependencies']['packages'];
        if (is_null($deps)) {
            $deps = [];
        }
        $dependencies = array_merge($dependencies, $deps);
        array_push($processed, $package_id);
    }
    $dependencies = array_unique($dependencies);
    $num_dependencies = count($dependencies) - $num_dependencies;
}
$to_install_full = array_intersect(
    array_diff($dependencies, array_keys($installed_packages)),
    array_keys($available_packages)
);
$to_update_full = array_intersect(
    $to_update, array_keys($installed_packages)
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
        // perform install/uninstall operations in batch
        $res = Core::packageManagerBatch($to_install_full, $to_update_full, $to_uninstall_full);
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
$packages_metadata = $installed_packages;
foreach ($to_install_full as $package_id) {
    $packages_metadata[$package_id] = $available_packages[$package_id]['metadata'];
}

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
                    $package_metadata = $packages_metadata[$package_id];
                    $package_name = $package_metadata['name'];
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
                            $package_metadata = $packages_metadata[$package_id];
                            $package_name = $package_metadata['name'];
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

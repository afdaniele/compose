<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use system\classes\Core;
use system\classes\Cache;
use system\classes\EditableConfiguration;

// update
if (isset($_GET['base_update']) && $_GET['base_update']) {
    include_once "update.php";
    return;
}
?>

<style>

    .panel-default > .panel-heading {
        text-shadow: 0 1px 0 #fff;
        background-image: -webkit-linear-gradient(top, #fff 0%, #e0e0e0 100%);
        background-image: -o-linear-gradient(top, #fff 0%, #e0e0e0 100%);
        background-image: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#e0e0e0));
        background-image: linear-gradient(to bottom, #fff 0%, #e0e0e0 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffffff', endColorstr='#ffe0e0e0', GradientType=0);
        filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
        background-repeat: repeat-x;
        border-color: #dbdbdb;
        border-color: #ccc;
    }

    .panel-default > .panel-heading > a {
        color: inherit;
        text-decoration: none;
    }

    .panel-group .panel {
        border: 1px solid #d8d7d7;
    }

    .panel-group .panel .panel-heading {
        border-bottom: 1px solid #d8d7d7;
    }

    .panel-group .panel .panel-body {
        padding: 30px 40px;
    }
</style>


<h2 class="page-title"></h2>

<?php
include_once "sections/package_specific.php";
include_once "sections/packages.php";
include_once "sections/pages.php";
include_once "sections/api.php";
include_once "sections/cache.php";
include_once "sections/codebase.php";
include_once "sections/user_roles.php";

include_once "sections/php_info.php";

//include_once "sections/theme.php";


$settings_tabs = [
    // [0-20] reserved for \compose\ tabs
    0 => [
        'id' => 'general',
        'title' => 'General',
        'icon' => 'sliders',
        'content' => "settings_custom_package_tab",
        'content_args' => ['core', Core::getPackageSettings('core')]
    ],
    1 => [
        'id' => 'packages',
        'title' => 'Packages',
        'icon' => 'boxes',
        'content' => 'settings_packages_tab',
        'content_args' => null
    ],
    2 => [
        'id' => 'pages',
        'title' => 'Pages',
        'icon' => 'file-text',
        'content' => 'settings_pages_tab',
        'content_args' => null
    ],
    3 => [
        'id' => 'api',
        'title' => 'API End-points',
        'icon' => 'diagram-3',
        'content' => 'settings_api_tab',
        'content_args' => null
    ],
    4 => [
        'id' => 'roles',
        'title' => 'User roles',
        'icon' => 'people-fill',
        'content' => 'settings_user_roles_tab',
        'content_args' => null
    ],
//    10 => [
//        'id' => 'theme',
//        'title' => 'Theme',
//        'icon' => 'fa fa-paint-brush',
//        'content' => settings_theme_tab,
//        'content_args' => null
//    ],
    
    // [21-100] reserved for packages
    
    // [101-400] free to use
    
    // #501 reserved for cache tab
    
    // [502-600] reserved for \compose\ tabs
    502 => [
        'id' => 'php',
        'title' => 'PHP Info',
        'icon' => 'info-square',
        'content' => 'settings_phpinfo_tab',
        'content_args' => null
    ],
    580 => [
        'id' => 'codebase',
        'title' => 'Codebase',
        'icon' => 'code-slash',
        'content' => 'settings_codebase_tab',
        'content_args' => null
    ]
];

if (Cache::enabled()) {
    // add cache tab if the flag is active
    $settings_tabs[501] = [
        'id' => 'cache',
        'title' => 'Cache',
        'icon' => 'clock-history',
        'content' => 'settings_cache_tab',
        'content_args' => null
    ];
}

$i = 21;
foreach (Core::getPackagesList() as $pkg_id => $pkg) {
    if ($pkg_id == 'core') {
        continue;
    }
    $pkg_setts = Core::getPackageSettings($pkg_id);
    // skip package if it is not configurable
    if (!$pkg_setts->is_configurable()) {
        continue;
    }
    // render package-specific tab
    $settings_tabs[$i] = [
        'id' => 'package_'.$pkg_id,
        'title' => 'Package:&nbsp;<b>'.$pkg['name'].'</b>',
        'icon' => 'box',
        'content' => 'settings_custom_package_tab',
        'content_args' => [$pkg_id, $pkg_setts]
    ];
    // ---
    $i += 1;
}
?>


<div class="accordion" id="settings-accordion">
    <?php
    $tab_idxs = array_keys($settings_tabs);
    sort($tab_idxs);
    foreach ($tab_idxs as $tab_idx) {
        $settings_tab = $settings_tabs[$tab_idx];
        $header = $settings_tab['id'] . '_header';
        $collapse = $settings_tab['id'] . '_collapse';
        ?>

        <div class="accordion-item">
            <h2 class="accordion-header" id="<?php echo $header ?>">
                <button class="accordion-button" id="collapse_a_<?php echo $collapse ?>"
                        type="button" data-bs-toggle="collapse"
                        data-bs-target="#<?php echo $collapse ?>" aria-expanded="true"
                        href="#<?php echo $collapse ?>" aria-controls="<?php echo $collapse ?>">
                    <i class="bi bi-<?php echo $settings_tab['icon'] ?>"></i>
                    &nbsp;
                    <?php echo $settings_tab['title'] ?>
                    <!--  -->
                    <span id="<?php echo $settings_tab['id'] ?>_unsaved_changes_mark"
                          style="position: absolute; right: 60px; color: darkorange; font-size: 11pt; display: none">
                            Unsaved changes &nbsp;
                            <i class="bi bi-exclamation-triangle"></i>
                        </span>
                </button>
            </h2>
            <div id="<?php echo $collapse ?>"
                 class="accordion-collapse collapse <?php echo ($tab_idx == 0) ? 'show' : '' ?>"
                 aria-labelledby="<?php echo $header ?>" data-bs-parent="#settings-accordion">
                <div class="accordion-body">
                    <?php
                    call_user_func($settings_tab['content'], $settings_tab['content_args'], $settings_tab['id']);
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>


<script type="text/javascript">
    // append hash to URL so that if we reload the page we can go back to the previous tab
    $('.collapse').on('shown.bs.collapse', function () {
        location.hash = 'sel:{0}'.format($(this).attr('id'));
    });

    $(document).ready(function () {
        let collapsible_id = location.hash.replace('#sel:', '');
        if (collapsible_id.length > 2 && collapsible_id !== 'general_collapse') {
            // show selected tab
            $('#collapse_a_' + collapsible_id).trigger('click');
        }
    });
</script>

<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

require_once $GLOBALS['__SYSTEM__DIR__'] . '/classes/RESTfulAPI.php';

use system\classes\Configuration;
use system\classes\Core;
use system\classes\RESTfulAPI;

?>

<style>
    .settings_api_list .input-group.lvl0 {
        margin-top: 30px;
        width: 900px;
    }

    .settings_api_list .input-group.lvl1 {
        width: 860px;
    }

    .settings_api_list .input-group.lvl2 {
        width: 820px;
    }

    .settings_api_list .input-group .form-control .entity-type {
        font-weight: normal;
        font-family: monospace;
        color: #b3b3b3;
        font-size: 10pt;
    }

    .settings_api_list .indent-box {
        width: 40px;
        background: url("<?php echo Configuration::$BASE ?>images/tree-view-link.png") no-repeat;
    }

    .settings_api_list .indent-box.empty {
        background-position: -20px 20px;
    }

    .settings_api_list .indent-box.line {
        background-position: 20px 0;
    }

    .settings_api_list .indent-box.fork {
        background-position: 20px -22px;
    }

    .settings_api_list .indent-box.end {
        background-position: 20px -66px;
    }

    .settings_api_list .content-box {
        height: 40px;
        vertical-align: bottom;
    }
</style>

<?php
function settings_api_tab() {
    RESTfulAPI::init();
    //
    $api_setup = RESTfulAPI::getEndpoints();
    $packages_list = array_keys(Core::getPackagesList());
    ?>

    <p>
        The following table reports all the API Services and Actions available on the platform.
    </p>
    <div style="padding:10px 0">

        <span>
            <strong style="float:left; margin:4px 10px 0 0;">Version: </strong>
        </span>
        <?php
        foreach ($api_setup as $version => $_) {
            $active = ($version == Configuration::$WEBAPI_VERSION) ? 'active' : '';
            ?>
            <a href="#api_version_<?php echo $version ?>"
               aria-controls="api_version_<?php echo $version ?>">
            <span class="badge rounded-pill bg-<?php echo $active ? 'primary' : 'dark' ?>">
                    <?php echo $version ?>
            </span>
            </a>
            <?php
        }
        ?>

        <!-- Tab panes -->
        <div class="tab-content">
            <?php
            foreach ($api_setup as $version => $api_services) {
                $api_version = $version;
                $active = ($version == Configuration::$WEBAPI_VERSION) ? 'active' : '';
                ?>
                <div role="tabpanel" class="tab-pane <?php echo $active ?>"
                     id="api_version_<?php echo $version ?>">

                    <div class="form-group settings_api_list">

                        <table>
                            <?php
                            foreach ($packages_list as $api_package) {
                                $services_per_package = 0;
                                $services_in_package = [];
                                foreach ($api_services as $api_service => $api_service_desc) {
                                    if ($api_service_desc->package() == $api_package) {
                                        $services_in_package[] = $api_service;
                                        $services_per_package += 1;
                                    }
                                }
                                if ($services_per_package == 0) {
                                    continue;
                                }
                                //
                                ?>
                                <tr>
                                    <td class="content-box" colspan="3">
                                        <div class="input-group lvl0">
                                            <div class="form-control">
                                                <i class="bi bi-box"></i>&nbsp;
                                                <?php echo $api_package ?>
                                                &nbsp;
                                                <span class="entity-type">
                                                    (package)
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $i = 1;
                                foreach ($services_in_package as $api_service) {
                                    $api_service_desc = $api_services[$api_service];
                                    $api_actions = $api_service_desc->getActions();
                                    $actions_per_service = count($api_actions);
                                    $service_tree_line = ($i == $services_per_package) ? "end" : (($i > 0) ? "fork" : "line");
                                    $service_btns_id = sprintf("%s_%s", $api_service, str_replace('.', '_', $api_version));
                                    ?>
                                    <tr>
                                        <td class="indent-box <?php echo $service_tree_line ?>"></td>
                                        <td class="content-box" colspan="2">
                                            <div class="input-group lvl1">
                                                <div class="form-control">
                                                    <i class="bi bi-code-square"></i>&nbsp;
                                                    <?php echo $api_service ?>
                                                    &nbsp;
                                                    <span class="entity-type">
                                                        (service)
                                                    </span>

                                                    <div style="float:right">
                                                        Status: <?php echo format($api_service_desc->enabled(), 'boolean') ?>
                                                        &nbsp; | &nbsp;
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-dark api-service-info-button"
                                                                id="api-service-<?php echo $service_btns_id ?>-info-button"
                                                                data-service="<?php echo $api_service ?>"
                                                                data-details="<?php echo $api_service_desc->description() ?>"
                                                        >
                                                            <i class="bi bi-info-circle"></i>
                                                            &nbsp;Info
                                                        </button>
                                                        &nbsp;
                                                        <?php
                                                        if ($api_service == 'api') {
                                                            ?>
                                                            <span style="padding:0 28px">
                                                                <i class="bi bi-ban-circle"
                                                                   style="color:lightgray"></i>
                                                            </span>
                                                            <?php
                                                        } elseif ($api_service_desc->enabled()) {
                                                            ?>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-warning api-service-disable-button"
                                                                    data-version="<?php echo $api_version ?>"
                                                                    data-service="<?php echo $api_service ?>"
                                                            >
                                                                <i class="bi bi-pause-fill"></i>
                                                                &nbsp;Disable
                                                            </button>
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-success api-service-enable-button"
                                                                    style="padding: 0 7px"
                                                                    data-version="<?php echo $api_version ?>"
                                                                    data-service="<?php echo $api_service ?>"
                                                            >
                                                                <i class="bi bi-play-fill"></i>
                                                                &nbsp;Enable
                                                            </button>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>

                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                    $j = 1;
                                    foreach ($api_actions as $api_action => $api_action_desc) {
                                        $service_tree_line = ($service_tree_line == "fork") ? "line" : (($service_tree_line == "end") ? "empty" : $service_tree_line);
                                        $action_tree_line = ($j == $actions_per_service) ? "end" : (($j > 0) ? "fork" : "line");
                                        $action_btns_id = sprintf("%s_%s_%s", $api_service, $api_action, str_replace('.', '_', $api_version));
                                        ?>
                                        <tr>
                                            <td class="indent-box <?php echo $service_tree_line ?>"></td>
                                            <td class="indent-box <?php echo $action_tree_line ?>"></td>
                                            <td class="content-box">
                                                <div class="input-group lvl2">
                                                    <div class="form-control">
                                                        <i class="bi bi-code-slash"></i>&nbsp;
                                                        <?php echo $api_action ?>
                                                        &nbsp;
                                                        <span class="entity-type">
                                                            (action)
                                                        </span>
                                                        <div style="float:right">
                                                            Status: <?php echo format($api_action_desc->enabled(), 'boolean') ?>
                                                            &nbsp; | &nbsp;
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-dark api-action-info-button"
                                                                    id="api-action-<?php echo $action_btns_id ?>-info-button"
                                                                    data-service="<?php echo $api_service ?>"
                                                                    data-action="<?php echo $api_action ?>"
                                                                    data-details="<?php echo $api_action_desc->description() ?>"
                                                            >
                                                                <i class="bi bi-info-circle"></i>
                                                                &nbsp;Info
                                                            </button>
                                                            &nbsp;
                                                            <?php
                                                            if ($api_service == 'api' && in_array($api_action, ['service_enable', 'action_enable'])) {
                                                                ?>
                                                                <span style="padding:0 28px">
                                                                    <i class="bi bi-ban-circle" style="color:lightgray"></i>
                                                                </span>
                                                                <?php
                                                            } elseif ($api_action_desc->enabled()) {
                                                                ?>
                                                                <button type="button"
                                                                        class="btn btn-sm btn-warning api-action-disable-button"
                                                                        data-version="<?php echo $api_version ?>"
                                                                        data-service="<?php echo $api_service ?>"
                                                                        data-action="<?php echo $api_action ?>"
                                                                >
                                                                    <i class="bi bi-pause-fill"></i>
                                                                    &nbsp;Disable
                                                                </button>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <button type="button"
                                                                        class="btn btn-sm btn-success api-action-enable-button"
                                                                        style="padding: 0 7px"
                                                                        data-version="<?php echo $api_version ?>"
                                                                        data-service="<?php echo $api_service ?>"
                                                                        data-action="<?php echo $api_action ?>"
                                                                >
                                                                    <i class="bi bi-play-fill"></i>
                                                                    &nbsp;Enable
                                                                </button>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        $j += 1;
                                    }
                                    $i += 1;
                                }
                            }
                            ?>
                        </table>

                    </div>


                </div>
                <?php
            }
            ?>
        </div>

    </div>


    <script type="text/javascript">

        $('.api-service-disable-button').on('click', function () {
            let api_version = $(this).data('version');
            let api_service = $(this).data('service');
            //
            let url = "<?php echo Configuration::$BASE ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/api/service_disable/json?version=" + api_version + "&service=" + api_service + "&token=<?php echo $_SESSION["TOKEN"] ?>";
            //
            callAPI(url, true, true);
        });

        $('.api-service-enable-button').on('click', function () {
            let api_version = $(this).data('version');
            let api_service = $(this).data('service');
            //
            let url = "<?php echo Configuration::$BASE ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/api/service_enable/json?version=" + api_version + "&service=" + api_service + "&token=<?php echo $_SESSION["TOKEN"] ?>";
            //
            callAPI(url, true, true);
        });

        $('.api-action-disable-button').on('click', function () {
            let api_version = $(this).data('version');
            let api_service = $(this).data('service');
            let api_action = $(this).data('action');
            //
            let url = "<?php echo Configuration::$BASE ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/api/action_disable/json?version=" + api_version + "&service=" + api_service + "&action=" + api_action + "&token=<?php echo $_SESSION["TOKEN"] ?>";
            //
            callAPI(url, true, true);
        });

        $('.api-action-enable-button').on('click', function () {
            let api_version = $(this).data('version');
            let api_service = $(this).data('service');
            let api_action = $(this).data('action');
            //
            let url = "<?php echo Configuration::$BASE ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/api/action_enable/json?version=" + api_version + "&service=" + api_service + "&action=" + api_action + "&token=<?php echo $_SESSION["TOKEN"] ?>";
            //
            callAPI(url, true, true);
        });

        $('.api-service-info-button').on('click', function () {
            let api_service = $(this).data('service');
            let title = "API service <b>{0}</b>".format(api_service);
            let content = $(this).data('details');
            //
            openPop($(this).attr('id'), title, content, "left", 0, 5000, false, true);
        });

        $('.api-action-info-button').on('click', function () {
            let api_service = $(this).data('service');
            let api_action = $(this).data('action');
            let title = "API action <b>{0}</b>/<b>{1}</b>".format(api_service, api_action);
            let content = $(this).data('details');
            //
            openPop($(this).attr('id'), title, content, "left", 0, 5000, false, true);
        });

    </script>
    
    <?php
}

?>

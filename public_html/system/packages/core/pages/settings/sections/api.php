<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 17th 2018

?>

<style type="text/css">
.settings_api_list .input-group.lvl0{
    margin-top: 30px;
    width: 700px;
}

.settings_api_list .input-group.lvl1{
    width: 660px;
}

.settings_api_list .input-group.lvl2{
    width: 620px;
}

.settings_api_list .input-group .form-control .entity-type{
    font-weight: normal;
    font-family: monospace;
    color: #b3b3b3;
    font-size: 10pt;
}

.settings_api_list .indent-box{
    width: 40px;
    background: url("<?php echo \system\classes\Configuration::$BASE_URL ?>images/tree-view-link.png");
    background-repeat: no-repeat;
}

.settings_api_list .indent-box.empty{
    background-position: -20px 20px;
}

.settings_api_list .indent-box.line{
    background-position: 20px 0;
}

.settings_api_list .indent-box.fork{
    background-position: 20px -22px;
}

.settings_api_list .indent-box.end{
    background-position: 20px -66px;
}

.settings_api_list .content-box{
    height: 40px;
    vertical-align: bottom;
}

#settings_api_version_selector{
    padding-bottom: 20px;
    border-bottom: 1px solid lightgray;
}
</style>

<?php
function settings_api_tab(){
    $api_setup = \system\classes\Core::getAPIsetup();
    $packages_list = array_keys( \system\classes\Core::getPackagesList() );
    ?>

    <p>
        The following table reports all the API Services and Actions available on the platform.
    </p>
    <div style="padding:10px 0">

        <!-- Nav tabs -->
        <ul class="nav nav-pills" role="tablist" id="settings_api_version_selector">
            <strong style="float:left; margin:4px 10px 0 0;">Version: </strong>
            <?php
            foreach( $api_setup as $version => $_ ){
                $active = ( $version == \system\classes\Configuration::$WEBAPI_VERSION )? 'active' : '';
                ?>
                <li role="presentation" class="<?php echo $active ?>">
                    <a href="#api_version_<?php echo $version ?>"
                        aria-controls="api_version_<?php echo $version ?>"
                        role="pill" data-toggle="pill" style="font-weight:bold; padding:4px 15px"
                        >
                        <?php echo $version ?>
                    </a>
                </li>
                <?php
            }
            ?>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <?php
            foreach( $api_setup as $version => $api ){
                $api_version = $version;
                $active = ( $version == \system\classes\Configuration::$WEBAPI_VERSION )? 'active' : '';
                ?>
                <div role="tabpanel" class="tab-pane <?php echo $active ?>" id="api_version_<?php echo $version ?>">

                    <div class="form-group settings_api_list">

                        <table style="margin:auto">
                            <?php
                            $api_services = $api['services'];
                            foreach($packages_list as $api_package){
                                $services_per_package = 0;
                                $services_in_package = [];
                                foreach ($api_services as $api_service => $api_service_desc) {
                                    if( $api_service_desc['package'] == $api_package ){
                                        array_push( $services_in_package, $api_service );
                                        $services_per_package += 1;
                                    }
                                }
                                if( $services_per_package == 0 ) continue;
                                //
                                ?>
                                <tr>
                                    <td class="content-box" colspan="3">
                                        <div class="input-group lvl0">
                                            <div class="input-group-addon">
                                                <i class="fa fa-cube" aria-hidden="true"></i>
                                            </div>
                                            <div class="form-control">
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
                                foreach ($services_in_package as $api_service ) {
                                    $api_service_desc = $api_services[$api_service];
                                    $api_actions = $api_service_desc['actions'];
                                    $actions_per_service = count($api_actions);
                                    $service_tree_line = ($i == $services_per_package)? "end" : ( ($i > 0)? "fork" : "line" );
                                    $service_btns_id = sprintf("%s_%s", $api_service, str_replace('.', '_', $api_version));
                                    ?>
                                    <tr>
                                        <td class="indent-box <?php echo $service_tree_line ?>"></td>
                                        <td class="content-box" colspan="2">
                                            <div class="input-group lvl1">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-gears" aria-hidden="true"></i>
                                                </div>
                                                <div class="form-control">
                                                    <?php echo $api_service ?>
                                                    &nbsp;
                                                    <span class="entity-type">
                                                        (service)
                                                    </span>

                                                    <div style="float:right">
                                                        Status: <?php echo format($api_service_desc['enabled'], 'boolean') ?>
                                                        &nbsp; | &nbsp;
                                                        <button type="button" class="btn btn-xs btn-info api-service-info-button"
                                                            id="api-service-<?php echo $service_btns_id ?>-info-button"
                                                            data-service="<?php echo $api_service ?>"
                                                            data-details="<?php echo $api_service_desc['details'] ?>"
                                                            >
                                                            <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                                                            &nbsp;Info
                                                        </button>
                                                        &nbsp;
                                                        <?php
                                                        if( $api_service == 'api' ){
                                                            ?>
                                                            <span style="padding:0 28px">
                                                                <span class="glyphicon glyphicon-ban-circle" aria-hidden="true" style="color:lightgray"></span>
                                                            </span>
                                                            <?php
                                                        }elseif( $api_service_desc['enabled'] ){
                                                            ?>
                                                            <button type="button" class="btn btn-xs btn-warning api-service-disable-button"
                                                                data-version="<?php echo $api_version ?>"
                                                                data-service="<?php echo $api_service ?>"
                                                                >
                                                                <span class="glyphicon glyphicon-pause" aria-hidden="true"></span>
                                                                &nbsp;Disable
                                                            </button>
                                                            <?php
                                                        }else{
                                                            ?>
                                                            <button type="button" class="btn btn-xs btn-success api-service-enable-button"
                                                                style="padding: 0 7px"
                                                                data-version="<?php echo $api_version ?>"
                                                                data-service="<?php echo $api_service ?>"
                                                                >
                                                                <span class="glyphicon glyphicon-play" aria-hidden="true"></span>
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
                                        $service_tree_line = ($service_tree_line == "fork")? "line" : ( ($service_tree_line == "end")? "empty" : $service_tree_line );
                                        $action_tree_line = ($j == $actions_per_service)? "end" : ( ($j > 0)? "fork" : "line" );
                                        $action_btns_id = sprintf("%s_%s_%s", $api_service, $api_action, str_replace('.', '_', $api_version));
                                        ?>
                                        <tr>
                                            <td class="indent-box <?php echo $service_tree_line ?>"></td>
                                            <td class="indent-box <?php echo $action_tree_line ?>"></td>
                                            <td class="content-box">
                                                <div class="input-group lvl2">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-gear" aria-hidden="true"></i>
                                                    </div>
                                                    <div class="form-control">
                                                        <?php echo $api_action ?>
                                                        &nbsp;
                                                        <span class="entity-type">
                                                            (action)
                                                        </span>
                                                        <div style="float:right">
                                                            Status: <?php echo format($api_action_desc['enabled'], 'boolean') ?>
                                                            &nbsp; | &nbsp;
                                                            <button type="button" class="btn btn-xs btn-info api-action-info-button"
                                                                id="api-action-<?php echo $action_btns_id ?>-info-button"
                                                                data-service="<?php echo $api_service ?>"
                                                                data-action="<?php echo $api_action ?>"
                                                                data-details="<?php echo $api_action_desc['details'] ?>"
                                                                >
                                                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                                                                &nbsp;Info
                                                            </button>
                                                            &nbsp;
                                                            <?php
                                                            if( $api_service == 'api' && in_array($api_action, ['service_enable', 'action_enable']) ){
                                                                ?>
                                                                <span style="padding:0 28px">
                                                                    <span class="glyphicon glyphicon-ban-circle" aria-hidden="true" style="color:lightgray"></span>
                                                                </span>
                                                                <?php
                                                            }elseif($api_action_desc['enabled']){
                                                                ?>
                                                                <button type="button" class="btn btn-xs btn-warning api-action-disable-button"
                                                                    data-version="<?php echo $api_version ?>"
                                                                    data-service="<?php echo $api_service ?>"
                                                                    data-action="<?php echo $api_action ?>"
                                                                    >
                                                                    <span class="glyphicon glyphicon-pause" aria-hidden="true"></span>
                                                                    &nbsp;Disable
                                                                </button>
                                                                <?php
                                                            }else{
                                                                ?>
                                                                <button type="button" class="btn btn-xs btn-success api-action-enable-button"
                                                                    style="padding: 0 7px"
                                                                    data-version="<?php echo $api_version ?>"
                                                                    data-service="<?php echo $api_service ?>"
                                                                    data-action="<?php echo $api_action ?>"
                                                                    >
                                                                    <span class="glyphicon glyphicon-play" aria-hidden="true"></span>
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

        $('.api-service-disable-button').on('click', function(){
            var api_version = $(this).data('version');
            var api_service = $(this).data('service');
            //
            var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/api/service_disable/json?version="+api_version+"&service="+api_service+"&token=<?php echo $_SESSION["TOKEN"] ?>";
            //
            callAPI( url, true, true );
        });

        $('.api-service-enable-button').on('click', function(){
            var api_version = $(this).data('version');
            var api_service = $(this).data('service');
            //
            var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/api/service_enable/json?version="+api_version+"&service="+api_service+"&token=<?php echo $_SESSION["TOKEN"] ?>";
            //
            callAPI( url, true, true );
        });

    	$('.api-action-disable-button').on('click', function(){
    		var api_version = $(this).data('version');
            var api_service = $(this).data('service');
            var api_action = $(this).data('action');
    		//
    		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/api/action_disable/json?version="+api_version+"&service="+api_service+"&action="+api_action+"&token=<?php echo $_SESSION["TOKEN"] ?>";
    		//
    		callAPI( url, true, true );
    	});

        $('.api-action-enable-button').on('click', function(){
    		var api_version = $(this).data('version');
            var api_service = $(this).data('service');
            var api_action = $(this).data('action');
    		//
    		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/api/action_enable/json?version="+api_version+"&service="+api_service+"&action="+api_action+"&token=<?php echo $_SESSION["TOKEN"] ?>";
    		//
    		callAPI( url, true, true );
    	});

        $('.api-service-info-button').on('click', function(){
            var api_service = $(this).data('service');
            var title = "API service <b>{0}</b>".format( api_service );
            var content = $(this).data('details');
    		//
            openPop( $(this).attr('id'), title, content, "left", 0, 5000, false, true );
    	});

        $('.api-action-info-button').on('click', function(){
            var api_service = $(this).data('service');
            var api_action = $(this).data('action');
            var title = "API action <b>{0}</b>/<b>{1}</b>".format( api_service, api_action );
            var content = $(this).data('details');
    		//
            openPop( $(this).attr('id'), title, content, "left", 0, 5000, false, true );
    	});

    </script>

<?php
}
?>

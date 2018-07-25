<?php
use \system\classes\Configuration;

function _api_page_menu_part( &$api_setup, &$version, &$sget, &$aget){
    $services = $api_setup[$version]['services'];
    ?>
    <style type="text/css">
        #services_actions_accordion{
            margin-bottom: 0;
        }

        .api_menu_item{
            -webkit-box-shadow: none;
            box-shadow: none;
        }

        .api_menu_item .panel-title{
            font-size: 14px;
            font-weight: normal;
        }

        .api_menu_item .api_menu_item_header{
            padding: 3px;
        }

        .api_menu_item .api_menu_item_header-active{
            background-color: #337ab7;
            color: white;
        }

        .api_menu_item .panel-body{
            background-color: #efeeee;
            padding: 0;
            padding-left: 14px;
            border-bottom: 1px solid darkgrey;
        }

        .api_menu_item .panel-body h5{
            color: #2b2b2b;
            font-weight: normal;
            padding: 3px;
            padding-left: 6px;
        }

        .api_menu_item .panel-body h5.active{
            color: white;
            background-color: #337ab7;
        }
    </style>

	<strong>Configuration</strong>
    <div class="panel api_menu_item">
        <div role="tab" class="api_menu_item_header api_menu_item_header<?php echo (Configuration::$ACTION == 'keys')? '-active':''?>">
            <h4 class="panel-title">
                <a href="<?php echo _create_link('keys', $version); ?>" role="button" aria-expanded="true">
                    <span class="fa fa-key" aria-hidden="true"></span>&nbsp;
                    API Keys
                </a>
            </h4>
        </div>
    </div>

    <strong>Documentation</strong>
    <div class="panel api_menu_item">
        <div role="tab" class="api_menu_item_header api_menu_item_header<?php echo (Configuration::$ACTION == 'getting_started')? '-active':''?>">
            <h4 class="panel-title">
                <a href="<?php echo _create_link('getting_started', $version); ?>" role="button" aria-expanded="true">
                    <span class="fa fa-book" aria-hidden="true"></span>&nbsp;
                    Getting Started
                </a>
            </h4>
        </div>
    </div>

	<strong>Reference</strong>
    <div class="panel-group" id="services_actions_accordion" role="tablist" aria-multiselectable="true">
		<?php
		foreach( $services as $sname => $service ){
            $service_btn_id = "service_collapse_".$sname;
			?>
            <div class="panel api_menu_item">
                <div role="tab" class="api_menu_item_header api_menu_item_header<?php echo ($sget == $sname)? '-active':''?>">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#services_actions_accordion" href="#<?php echo $service_btn_id ?>" aria-expanded="true" aria-controls="<?php echo $service_btn_id ?>">
                            <span class="fa fa-tasks" aria-hidden="true"></span>&nbsp;
                            <?php echo $sname ?>
                        </a>
                    </h4>
                </div>
                <div id="<?php echo $service_btn_id ?>" class="panel-collapse collapse <?php echo ($sget == $sname)? 'in':''?>" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">
                        <?php
						foreach( $service['actions'] as $aname => $action ){
						?>
							<a href="<?php echo _create_link('reference', $version, $sname, $aname); ?>">
                                <h5 class="<?php echo ( ($sget == $sname && $aget == $aname)? 'active' : '' ) ?>">
    								<span class="fa fa-cog" aria-hidden="true"></span>&nbsp;
                                    <?php echo $aname ?>
                                </h5>
							</a>
						<?php
						}
						?>
                    </div>
                </div>
            </div>
		<?php
		}
		?>
    </div>
<?php
}

function _create_link( $action, $version, $serv=null, $act=null ){
    return sprintf(
        '%sapi/%s?version=%s%s',
        Configuration::$BASE,
        $action,
        $version,
        (!is_null($serv) && !is_null($serv))? '&service='.$serv.'&action='.$act : ''
    );
}//_create_link
?>

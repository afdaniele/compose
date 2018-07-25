<?php

use \system\classes\Configuration;

function _api_page_header_part( &$api_setup, &$version ){
    $api_enabled = $api_setup[$version]['enabled'];
    $services = $api_setup[$version]['services'];
    ?>

    <style type="text/css">
        .api-version-label{
            padding:4px 14px;
            padding-left: 16px;
        }

        .api-version-label a{
            color: white;
            font-size: 9pt;
        }
    </style>

    <p>
    	Versions available:
    	&nbsp;
    	<?php
    	foreach( $api_setup as $key => $_ ){
    		?>
    		<span class="label label-<?php echo ( ($version == $key)? 'primary' : 'default' ) ?> api-version-label">
    				<a href="<?php echo Configuration::$BASE.'api?version='.$key ?>">
    					<?php echo $key ?>
    				</a>
    			</span>
    		<span style="padding-left:4px"></span>
    	<?php
    	}
    	?>
    </p>

    <p class="text-right" style="display:table; clear:both; width:100%; margin-top:16px; margin-bottom:4px">
    	<span style="float:left">
    		<?php
			$servs_count = sizeof( array_keys($services) );
			$acts_count = 0;
			//
			foreach( $services as $k => $s ){
				$acts_count += sizeof( array_keys($s['actions']) );
			}
			?>
			Total: <span class="param"><?php echo $acts_count ?></span> actions in <span class="param"><?php echo $servs_count ?></span> services
    	</span>

    	<span style="float:right">
    		API (v<?php echo $version ?>)&nbsp; | &nbsp;Status: <?php echo ( ($api_enabled)? '<span class="on">OnLine</span>' : '<span class="off">OffLine</span>' ) ?>
    	</span>
    </p>
<?php
}
?>

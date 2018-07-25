<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 15th 2018

require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/RESTfulAPI.php';
use system\classes\Core;
use system\classes\RESTfulAPI;
use system\classes\Configuration;

$default_page = 'keys';
if( is_null(Configuration::$ACTION) || Configuration::$ACTION=='' )
	Configuration::$ACTION = $default_page;

// initialize RESTfulAPI
RESTfulAPI::init();
$api_setup = RESTfulAPI::getConfiguration();

// parse the version argument
$version = ( ( isset($_GET['version']) && in_array(strtolower($_GET['version']), array_keys($api_setup), true) )? strtolower($_GET['version']) : ( (!isset($_GET['version']))? Configuration::$WEBAPI_VERSION : null ) );
if( $version == null ){
	// the required version is not valid
	Core::redirectTo(
		sprintf('api/%s?version=%s', $default_page, Configuration::$WEBAPI_VERSION)
	);
}
$api_config = $api_setup[$version];

require_once __DIR__.'/parts/header.php';
require_once __DIR__.'/parts/menu.php';
?>

<style type="text/css">
	.api_page_rounded_box{
		width: 100%;
		-moz-border-radius: 4px;
		-webkit-border-radius: 4px;
		border-radius: 4px;
		border: 1px solid #d3d3d3;
		background-color: #ffffff;
		padding: 10px;
		display: inline-block;
	}

	.api-breadcrumb{
		padding-top: 8px;
		padding-bottom: 10px;
		border-bottom: 1px solid rgba(51, 122, 183, 0.2);
	}
</style>

<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:22px">
	<tr>
		<td style="width:100%">
			<h2>RESTful API</h2>
		</td>
	</tr>
</table>

<?php
_api_page_header_part( $api_setup, $version );

// parse `service` and `action` parameters
$sget = ( (isset($_GET['service']) && in_array($_GET['service'], array_keys($api_config['services'])) )? $_GET['service'] : null );
$aget = ( ($sget !== null && isset($_GET['action']) && in_array($_GET['action'], array_keys($api_config['services'][$sget]['actions'])) )? $_GET['action'] : null );
?>

<table class="api-box-container" style="width:100%">
	<tr>
		<td style="width:24%; vertical-align:top; padding-right:6px">
			<div class="api_page_rounded_box">
				<?php
				_api_page_menu_part( $api_setup, $version, $sget, $aget );
				?>
			</div>
		</td>

		<td style="width:76%; vertical-align:top; padding-left:6px">
			<div class="api_page_rounded_box" style="padding:12px 18px">
				<?php
				// go over the possible pages
				switch( Configuration::$ACTION ){
					//
					case 'keys':
						require_once __DIR__.'/sections/keys.php';
						break;
					case 'reference':
						if( is_null($sget) || is_null($aget) ){
							Core::redirectTo(
								sprintf('api/%s?version=%s', $default_page, Configuration::$WEBAPI_VERSION)
							);
						}
						// if the APIvXX is offLine then also its services are offLine
						if( $sget!== null && !$api_config['enabled'] ){
							$api_config['services'][$sget]['enabled'] = false;
						}
						// if the service is offLine then also its actions are offLine
						if( $sget!== null && $aget!== null && !$api_config['services'][$sget]['enabled'] ){
							$api_config['services'][$sget]['actions'][$aget]['enabled'] = false;
						}
						// load module and render
						require_once __DIR__.'/sections/reference.php';
						_api_page_reference_section( $api_setup, $version, $sget, $aget );
						break;
					//
					case 'getting_started':
						require_once __DIR__.'/sections/getting_started.php';
						_api_page_getting_started_section( $api_setup, $version, $sget, $aget );
						break;
					//
					default:
						Core::redirectTo(
							sprintf('api/%s?version=%s', $default_page, Configuration::$WEBAPI_VERSION)
						);
						break;
				}//switch
				?>
			</div>
		</td>
	</tr>
</table>

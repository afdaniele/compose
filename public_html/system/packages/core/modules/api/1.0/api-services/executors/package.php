<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele



require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Core.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Cache.php';
use system\classes\Core;
use system\classes\CacheProxy;

require_once $GLOBALS['__SYSTEM__DIR__'].'/api/1.0/utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$cache = new CacheProxy('core');
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'status':
			$is_enabled = Core::isPackageEnabled( $arguments['id'] );
			$data = [
				'package' => $arguments['id'],
				'enabled' => $is_enabled
			];
			//
			return response200OK( $data );
			break;
		//
		case 'enable':
			$res = Core::enablePackage( $arguments['id'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			$cache->clear();
			//
			return response200OK( null );
			break;
		//
		case 'disable':
			$res = Core::disablePackage( $arguments['id'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			$cache->clear();
			//
			return response200OK( null );
			break;
		//
		default:
			return response404NotFound( sprintf("The command '%s' was not found", $actionName) );
			break;
	}
}//execute

?>

<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


$SYSTEM = $GLOBALS['__SYSTEM__DIR__'];

use system\classes\Core;
use system\classes\CacheProxy;


function execute($service, $actionName, &$arguments): APIResponse {
	$cache = new CacheProxy('core');
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'status':
			$is_enabled = Core::isPageEnabled( $arguments['package'], $arguments['id'] );
			$data = [
				'package' => $arguments['id'],
				'enabled' => $is_enabled
			];
			//
			return response200OK( $data );
			break;
		//
		case 'enable':
			$res = Core::enablePage( $arguments['package'], $arguments['id'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			$cache->clear();
			//
			return response200OK( null );
			break;
		//
		case 'disable':
			$res = Core::disablePage( $arguments['package'], $arguments['id'] );
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

<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 14th 2018



require_once __DIR__.'/../../../../../../../classes/Core.php';
require_once __DIR__.'/../../../../../../../classes/RestfulAPI.php';
use system\classes\Core;
use system\classes\RestfulAPI;

require_once __DIR__.'/../../../../../../../api/1.0/utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'service_status':
			$is_enabled = RestfulAPI::isServiceEnabled( $arguments['version'], $arguments['service'] );
			$data = [
				'version' => $arguments['version'],
				'service' => $arguments['service'],
				'enabled' => $is_enabled
			];
			//
			return response200OK( $data );
			break;
		//
		case 'service_enable':
			$res = RestfulAPI::enableService( $arguments['version'], $arguments['service'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK();
			break;
		//
		case 'service_disable':
			$res = RestfulAPI::disableService( $arguments['version'], $arguments['service'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK();
			break;
		//
		case 'action_status':
			$is_enabled = RestfulAPI::isActionEnabled( $arguments['version'], $arguments['service'], $arguments['action'] );
			$data = [
				'version' => $arguments['version'],
				'service' => $arguments['service'],
				'action' => $arguments['action'],
				'enabled' => $is_enabled
			];
			//
			return response200OK( $data );
			break;
		//
		case 'action_enable':
			$res = RestfulAPI::enableAction( $arguments['version'], $arguments['service'], $arguments['action'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK();
			break;
		//
		case 'action_disable':
			$res = RestfulAPI::disableAction( $arguments['version'], $arguments['service'], $arguments['action'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK();
			break;
		//
		default:
			return response404NotFound( sprintf("The command '%s' was not found", $actionName) );
			break;
	}
}//execute

?>

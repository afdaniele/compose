<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 14th 2018



require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Core.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Cache.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/RESTfulAPI.php';
use system\classes\Core;
use system\classes\CacheProxy;
use system\classes\RESTfulAPI;

require_once $GLOBALS['__SYSTEM__DIR__'].'/api/1.0/utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$cache = new CacheProxy('api');
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'service_status':
			$is_enabled = RESTfulAPI::isServiceEnabled( $arguments['version'], $arguments['service'] );
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
			$res = RESTfulAPI::enableService( $arguments['version'], $arguments['service'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			$cache->clear();
			//
			return response200OK();
			break;
		//
		case 'service_disable':
			$res = RESTfulAPI::disableService( $arguments['version'], $arguments['service'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			$cache->clear();
			//
			return response200OK();
			break;
		//
		case 'action_status':
			$is_enabled = RESTfulAPI::isActionEnabled( $arguments['version'], $arguments['service'], $arguments['action'] );
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
			$res = RESTfulAPI::enableAction( $arguments['version'], $arguments['service'], $arguments['action'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			$cache->clear();
			//
			return response200OK();
			break;
		//
		case 'action_disable':
			$res = RESTfulAPI::disableAction( $arguments['version'], $arguments['service'], $arguments['action'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			$cache->clear();
			//
			return response200OK();
			break;
		//
		case 'app_create':
			$endpoints = [];
			// get one option for each service/action pair
			foreach( RESTfulAPI::getConfiguration() as $pkg_id => &$pkg_api ){
			    foreach( $pkg_api['services'] as $service_id => &$service_config ){
			        foreach( $service_config['actions'] as $action_id => &$action_config ){
						if( !in_array('app', $action_config['authentication']) ) continue;
			            $pair = sprintf('%s__%s', $service_id, $action_id);
						if( isset($arguments[$pair]) && $arguments[$pair]=='1' ){
							array_push($endpoints_up, sprintf('%s/%s', $service_id, $action_id));
						}
			        }
			    }
			}
			// create new app entry
			$res = RESTfulAPI::createApplication( $arguments['name'], $endpoints, boolval($arguments['enabled']) );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK();
			break;
		//
		case 'app_update':
			$endpoints_up = [];
			$endpoints_dw = [];
			// get one option for each service/action pair
			foreach( RESTfulAPI::getConfiguration() as $pkg_id => &$pkg_api ){
				foreach( $pkg_api['services'] as $service_id => &$service_config ){
					foreach( $service_config['actions'] as $action_id => &$action_config ){
						if( !in_array('app', $action_config['authentication']) ) continue;
						$pair = sprintf('%s__%s', $service_id, $action_id);
						if( isset($arguments[$pair]) && $arguments[$pair]=='1' ){
							array_push($endpoints_up, sprintf('%s/%s', $service_id, $action_id));
						}else{
							array_push($endpoints_dw, sprintf('%s/%s', $service_id, $action_id));
						}
					}
				}
			}
			// do not change status if `enabled` is not passed as an argument
			$enabled = isset($arguments['enabled'])? boolval($arguments['enabled']) : null;
			// update the entry
			$res = RESTfulAPI::updateApplication( $arguments['id'], $endpoints_up, $endpoints_dw, $enabled );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK();
			break;
		//
		case 'app_delete':
			// delete the app entry
			$res = RESTfulAPI::deleteApplication( $arguments['id'] );
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

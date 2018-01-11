<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018



require_once __DIR__.'/../../../../../../../classes/Core.php';
use system\classes\Core as Core;

require_once __DIR__.'/../../../../../../../api/1.0/utils/utils.php';

require_once __DIR__.'/../../../../../Duckietown.php';
use system\packages\duckietown\Duckietown as Duckietown;


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'exists':
			$exists = Duckietown::duckiebotExists( $arguments['name'] );
			//
			return array(
				'code' => 200,
				'status' => 'OK',
				'data' => array(
					'name' => $arguments['name'],
					'exists' => $exists,
					'message' => ($exists)? 'OK' : 'The Duckiebot does not exist'
				)
			);
			break;
		//
		case 'status':
			$is_online = Duckietown::isDuckiebotOnline( $arguments['name'] );
			//
			return array(
				'code' => 200,
				'status' => 'OK',
				'data' => array(
					'name' => $arguments['name'],
					'online' => $is_online,
					'message' => ($is_online)? 'OK' : 'The Duckiebot is not reachable'
				)
			);
			break;
		//
		case 'authenticate':
			$password = base64_decode( $arguments['password'] );
			$res = Duckietown::authenticateOnDuckiebot( $arguments['name'], $arguments['username'], $password );
			//
			return array(
				'code' => 200,
				'status' => 'OK',
				'data' => array(
					'name' => $arguments['name'],
					'success' => $res['success'],
					'message' => ($res['success'])? 'OK' : $res['data']
				)
			);
			break;
		//
		case 'associate':
			$res = Duckietown::linkDuckiebotToUserAccount( $arguments['name'] );
			//
			return array(
				'code' => 200,
				'status' => 'OK',
				'data' => array(
					'name' => $arguments['name'],
					'success' => $res['success'],
					'message' => ($res['success'])? 'OK' : $res['data']
				)
			);
			break;
		//
		case 'release':
			$res = Duckietown::unlinkDuckiebotFromUserAccount( $arguments['name'] );
			//
			if( !$res['success'] ){
				return array( 'code' => 500, 'status' => 'Internal Server Error', 'data' => $res['data'] );
			}else{
				return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'name' => $arguments['name'], 'success' => true ) );
			}
			break;
		//
		case 'owner':
			$owner = Duckietown::getDuckiebotOwner( $arguments['name'] );
			//
			if( $owner == null ){
				return array( 'code' => 404, 'status' => 'Not Found', 'data' => 'null' );
			}else{
				return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'owner' => $owner ) );
			}
			break;
		//
		case 'network':
			//TODO: check when $interfaces has 'success' and it is not '/true/'
			$interfaces = Duckietown::getDuckiebotNetworkConfig( $arguments['name'] );
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'name' => $arguments['name'], 'interfaces' => $interfaces ) );
			break;
		//
		case 'storage':
			//TODO: check when $storage_mountpoints has 'success' and it is not '/true/'
			$storage_mountpoints = Duckietown::getDuckiebotDiskStatus( $arguments['name'] );
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'name' => $arguments['name'], 'mountpoints' => $storage_mountpoints ) );
			break;
		//
		case 'configuration':
			//TODO: check when $bot_configuration has 'success' and it is not '/true/'
			$bot_configuration = Duckietown::getDuckiebotConfiguration( $arguments['name'] );
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'name' => $arguments['name'], 'configuration' => $bot_configuration ) );
			break;
			//
		case 'ros':
			//TODO: check when $bot_configuration has 'success' and it is not '/true/'
			$ros = Duckietown::getDuckiebotROS( $arguments['name'] );
			$ros['name'] = $arguments['name'];
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => $ros );
			break;
		//
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The command '".$actionName."' was not found" );
			break;
	}
}//execute

?>

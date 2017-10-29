<?php

require_once __DIR__.'/../../../../classes/Core.php';
use system\classes\Core as Core;

require_once __DIR__ . '/../utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'owner':
			$owner = Core::getDuckiebotOwner( $arguments['name'] );
			//
			if( $owner == null ){
				return array( 'code' => 404, 'status' => 'Not Found', 'data' => 'null' );
			}else{
				return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'owner' => $owner ) );
			}
			break;
		//
		case 'status':
			$is_online = Core::isDuckiebotOnline( $arguments['name'] );
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'name' => $arguments['name'], 'online' => $is_online ) );
			break;
		//
		case 'network':
			//TODO: check when $interfaces has 'success' and it is not '/true/'
			$interfaces = Core::getDuckiebotNetworkConfig( $arguments['name'] );
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'name' => $arguments['name'], 'interfaces' => $interfaces ) );
			break;
		//
		case 'storage':
			//TODO: check when $storage_mountpoints has 'success' and it is not '/true/'
			$storage_mountpoints = Core::getDuckiebotDiskStatus( $arguments['name'] );
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'name' => $arguments['name'], 'mountpoints' => $storage_mountpoints ) );
			break;
		//
		case 'configuration':
			//TODO: check when $bot_configuration has 'success' and it is not '/true/'
			$bot_configuration = Core::getDuckiebotConfiguration( $arguments['name'] );
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'name' => $arguments['name'], 'configuration' => $bot_configuration ) );
			break;
			//
		case 'ros':
			//TODO: check when $bot_configuration has 'success' and it is not '/true/'
			$ros = Core::getDuckiebotROS( $arguments['name'] );
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

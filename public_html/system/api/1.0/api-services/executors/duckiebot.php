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
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "Comando '".$actionName."' non trovato" );
			break;
	}
}//execute

?>

<?php

require_once __DIR__.'/../../../../classes/Core.php';
use system\classes\Core as Core;

require_once __DIR__ . '/../utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'start':
			$sessionID = session_id();
			//
			if( !isset($_SESSION['TOKEN']) || $_SESSION['TOKEN']=='' ) {
				Core::startSession();
			}
			//
			$tokenID = $_SESSION['TOKEN'];
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'sessionID' => $sessionID, 'token' => $tokenID ) );
			break;
		//
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "Comando '".$actionName."' non trovato" );
			break;
	}
}//execute

?>

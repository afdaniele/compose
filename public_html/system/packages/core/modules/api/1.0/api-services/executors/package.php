<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Tuesday, January 9th 2018



require_once __DIR__.'/../../../../../../../classes/Core.php';
use system\classes\Core as Core;

require_once __DIR__.'/../../../../../../../api/1.0/utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'status':
			//TODO
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => null );
			break;
		//
		case 'enable':
			//TODO
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => null );
			break;
		//
		case 'disable':
			//TODO
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => null );
			break;
		//
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The command '".$actionName."' was not found" );
			break;
	}
}//execute

?>

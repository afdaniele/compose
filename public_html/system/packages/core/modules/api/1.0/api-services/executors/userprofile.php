<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 15th 2018



require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Core.php';
use system\classes\Core as Core;

require_once $GLOBALS['__SYSTEM__DIR__'].'/api/1.0/utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'login_with_google':
			if( Core::isUserLoggedIn() ){
				// error
				return array( 'code' => 412, 'status' => 'Precondition Failed', 'message' => 'You are already logged in' );
			}
			//
			$id_token = $arguments['id_token'];
			$res = Core::logInUserWithGoogle( $id_token );
			if( !$res['success'] ){
				return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
			}
			// success
			return array( 'code' => 200, 'status' => 'OK' );
			break;
		//
		case 'logout':
			$res = Core::logOutUser();
			if( !$res['success'] ){
				 array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
			}
			// success
			return array( 'code' => 200, 'status' => 'OK' );
			break;
		//
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The command '".$actionName."' was not found" );
			break;
	}
}//execute

?>

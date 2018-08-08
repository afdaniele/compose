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
	Core::startSession();
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
			return response200OK( null );
			break;
		case 'edit':
			// open user profile
			$res = Core::openUserInfo( $arguments['userid'] );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			$user = $res['data'];
			// update info
			if( array_key_exists('active', $arguments) ){
				$user->set( 'active', boolval($arguments['active']) );
			}
			// commit
			$res = $user->commit();
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK( null );
			break;
		//
		case 'logout':
			$res = Core::logOutUser();
			if( !$res['success'] ){
				 array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
			}
			// success
			return response200OK( null );
			break;
		//
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The command '".$actionName."' was not found" );
			break;
	}
}//execute

?>

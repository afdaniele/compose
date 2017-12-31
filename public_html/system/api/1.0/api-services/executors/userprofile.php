<?php

require_once __DIR__.'/../../../../classes/Core.php';
use system\classes\Core as Core;

require_once __DIR__ . '/../utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'login':
			if( Core::isUserLoggedIn() ){
				// error
				return array( 'code' => 412, 'status' => 'Precondition Failed', 'message' => 'You are already logged in' );
			}
			//
			$uri = 'web-api/'.$arguments['apiversion'].'/userprofile/login/'.$arguments['format']. toQueryString(array('username','timestamp','token'),$arguments,true,false);
			//
			$username = $arguments['username'];
			// authenticate URI
			$res = Core::authenticateURIrequest( $username, $uri, $arguments, /* allowTempPassword = */ true );
			if( !$res['success'] ){
				return array( 'code' => 401, 'status' => 'Unauthorized', 'message' => $res['data'] );
			}
			// log in the user
			$recovery = $res['data']['recovery'];
			$res = Core::logInUser( $username, $recovery );
			if( !$res['success'] ){
				return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
			}
			// store the 'timestamp'
			Core::setUserLastSeen( $username, intval($arguments['timestamp']) );
			// success
			return array( 'code' => 200, 'status' => 'OK' );
			break;
		//
		case 'logout':
			Core::logOutUser();
			// success
			return array( 'code' => 200, 'status' => 'OK' );
			break;
		//
		case 'recovery':
			if( Core::isUserLoggedIn() ){
				// error
				return array( 'code' => 412, 'status' => 'Precondition Failed', 'message' => 'You are already logged in' );
			}
			//
			$username = $arguments['username'];
			//
			$res = Core::getUserInfoNoAuth( $username );
			$data = prepareResult( $res, $action, false );
			//
			if( $data !== true ){
				return $data; //error
			}
			//
			$record = $res['data'];
			//
			$res = Core::generateUserTemporaryPassword( $username );
			$password = $res['data'];
			//
			$emaildata = array( /*title*/ 'This is your new password!',  /*first_name*/ $record['name'],  /*username*/ $username,  /*password*/ $password);
			Core::sendEMail( $record['email'], 'Password recovery '.\system\classes\Configuration::$SHORT_SITE_LINK, \system\classes\enum\EmailTemplates::$PASSWORD_RECOVERY, $emaildata );
			// success
			return array( 'code' => 200, 'status' => 'OK' );
			break;
		//
		case 'updatepersonal':
			$username = Core::getUserLogged('username');
			//
			$res = Core::getUserInfoNoAuth( $username );
			//
			$data = prepareResult( $res, $action, false );
			//
			if( $data !== true ){
				return $data; //error
			}else{
				$res = Core::editPersonalInformation( $username, $arguments );
				if( !$res['success'] ){
					return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
				}else{
					// success
					return array( 'code' => 200, 'status' => 'OK' );
				}
			}
			break;
		//
		case 'updatekeys':
			$uri = 'web-api/'.$arguments['apiversion'].'/userprofile/updatekeys/'.$arguments['format']. toQueryString(array('password','passwordconfirm','timestamp','token'),$arguments,true,false);
			//
			$username = Core::getUserLogged('username');
			// authenticate URI
			$res = Core::authenticateURIrequest( $username, $uri, $arguments, /* allowTempPassword = */ true );
			if( !$res['success'] ){
				return array( 'code' => 401, 'status' => 'Unauthorized', 'message' => $res['data'] );
			}
			// make sure that password and passwordconfirm match
			if( !($arguments['password'] == $arguments['passwordconfirm']) ){
				return array( 'code' => 401, 'status' => 'Unauthorized', 'message' => "Password and Password (confirm) must match" );
			}
			// update the password
			$res = Core::editSecurityInformation( $username, $arguments );
			if( !$res['success'] ){
				return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
			}
			// store the 'timestamp'
			Core::setUserLastSeen( $username, intval($arguments['timestamp']) );
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

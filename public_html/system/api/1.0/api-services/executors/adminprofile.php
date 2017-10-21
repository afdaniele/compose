<?php

require_once __DIR__.'/../../../../classes/Core.php';
use system\classes\Core as Core;

require_once __DIR__ . '/../utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'login':
			Core::startSession();
			if( Core::isAdministratorLoggedIn() ){
				// error
				return array( 'code' => 412, 'status' => 'Precondition Failed', 'message' => 'Risulti essere già loggato!' );
			}
			//
			$uri = 'web-api/'.$arguments['apiversion'].'/adminprofile/login/'.$arguments['format']. toQueryString(array('username','timestamp','token'),$arguments,true,false);
			//
			$username = $arguments['username'];
			$res = Core::getAdministratorInfoNoAuth( $username, true );
			//
			$data = prepareResult( $res, $action, false );
			//
			if( $data !== true ){
				return $data; //error
			}else{
				$success = false;
				$data = null;
				//
				if( $res['size'] == 1 ){
					$res2 = Core::getAdministratorLastSeen( $username );
					if( $res2['success'] && $res2['size'] == 1 ){
						if( intval($res2['data'][0]['lastSeen']) < intval($arguments['timestamp']) ){
							// compute hmac
							$recovery = false;
							$secret = $res['data'][0]['password'];
							$hash = md5( base64_encode( hash_hmac('sha256', $uri, $secret, true) ) );
							//
							$success = ( strcmp($hash, $arguments['hmac']) == 0 );
							//
							if( !$success ){
								// try to login the admin in recovery mode
								$recovery = true;
								$secret = $res['data'][0]['tempPassword'];
								$hash = md5( base64_encode( hash_hmac('sha256', $uri, $secret, true) ) );
								//
								$success = ( strcmp($hash, $arguments['hmac']) == 0 );
							}
						}else{ $data = $res2['data'][0]['lastSeen'].' >= '.$arguments['timestamp']; }
					}else{ $data = null; }
				}else{ $data = null; }
				//
				if( $success ){
					// login
					$res = Core::logInAdministrator( $username, $secret, $recovery );
					if( !$res['success'] ){
						return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
					}
					// store the 'timestamp'
					Core::setAdministratorLastSeen( $username, intval($arguments['timestamp']) );
					// success
					return array( 'code' => 200, 'status' => 'OK' );
				}else{
					return array( 'code' => 401, 'status' => 'Unauthorized', 'message' => 'Tentativo di login fallito, nome utente o password errati', 'data' => $data );
				}
			}
			// error
			return array( 'code' => 401, 'status' => 'Unauthorized', 'message' => 'Tentativo di login fallito, nome utente o password errati' );
			break;
		//
		case 'logout':
			Core::logOutAdministrator();
			// success
			return array( 'code' => 200, 'status' => 'OK' );
			break;
		//
		case 'recovery':
			Core::startSession();
			if( Core::isAdministratorLoggedIn() ){
				// error
				return array( 'code' => 412, 'status' => 'Precondition Failed', 'message' => 'Risulti essere già loggato!' );
			}
			//
			$username = Core::escape_string( $arguments['username'] );
			//
			$res = Core::getAdministratorInfoNoAuth( $username );
			$data = prepareResult( $res, $action, false );
			//
			if( $data !== true ){
				return $data; //error
			}
			//
			$record = $res['data'][0];
			//
			$res = Core::generateAdministratorTemporaryPassword( $username );
			$password = $res['data'];
			//
			$emaildata = array( /*title*/ 'Ecco la tua nuova password!',  /*first_name*/ $record['name'],  /*username*/ $username,  /*password*/ $password);
			Core::sendEMail( $record['email'], 'Recupero password su '.\system\classes\Configuration::$SHORT_SITE_LINK, \system\classes\enum\EmailTemplates::$PASSWORD_RECOVERY, $emaildata );
			// success
			return array( 'code' => 200, 'status' => 'OK' );
			break;
		//
		case 'updatepersonal':
			$username = Core::getAdministratorLogged('username');
			//
			$res = Core::getAdministratorInfoNoAuth( $username );
			//
			$data = prepareResult( $res, $action, false );
			//
			if( $data !== true ){
				return $data; //error
			}else{
				$res = Core::editPersonalAdministratorInformation( $username, $arguments );
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
			$uri = 'web-api/'.$arguments['apiversion'].'/adminprofile/updatekeys/'.$arguments['format']. toQueryString(array('password','passwordconfirm','timestamp','token'),$arguments,true,false);
			//
			$username = Core::getAdministratorLogged('username');
			//
			$res = Core::getAdministratorInfoNoAuth( $username, true );
			//
			$data = prepareResult( $res, $action, false );
			//
			if( $data !== true ){
				return $data; //error
			}else{
				// compute hmac
				$secret = $res['data'][0]['password'];
				$hash = md5( base64_encode( hash_hmac('sha256', $uri, $secret, true) ) );
				//
				$success = ( strcmp($hash, $arguments['hmac']) == 0 );
				//
				if( !$success ){
					// try to login the admin in recovery mode
					$secret = $res['data'][0]['tempPassword'];
					$hash = md5( base64_encode( hash_hmac('sha256', $uri, $secret, true) ) );
					//
					$success = ( strcmp($hash, $arguments['hmac']) == 0 );
				}
				//
				if( $success ){
					if( !($arguments['password'] == $arguments['passwordconfirm']) ){
						return array( 'code' => 401, 'status' => 'Unauthorized', 'message' => "I campi 'Password' e 'Conferma Password' devono avere lo stesso valore" );
					}
					// update the password
					$arguments['password_confirm'] = $arguments['passwordconfirm'];
					$res = Core::editSecurityAdministratorInformation( $username, $arguments );
					if( !$res['success'] ){
						return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
					}
					// store the 'timestamp'
					Core::setAdministratorLastSeen( $username, intval($arguments['timestamp']) );
					// success
					return array( 'code' => 200, 'status' => 'OK' );
				}else{
					return array( 'code' => 401, 'status' => 'Unauthorized', 'message' => 'La tua vecchia password non corrisponde a quella inserita, riprova!' );
				}
			}
			break;
		//
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "Comando '".$actionName."' non trovato" );
			break;
	}
}//execute

?>

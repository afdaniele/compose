<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 14th 2018


//error_reporting(E_ALL ^ E_NOTICE); //DEBUG only
//ini_set('display_errors', 1); //DEBUG only


// error received from .htaccess due to an invalid API url
if( isset($_GET['__error__']) ){
	sendResponse( 400, 'Bad Request', 'Invalid API call, please check and retry!', 'plaintext', null );
}

// load constants
require_once __DIR__.'/../system/environment.php';

// load core classes and utility
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Core.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/RestfulAPI.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Configuration.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/enum/StringType.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/utils/utils.php';

use system\classes\Core;
use system\classes\RestfulAPI;
use system\classes\enum\StringType;
use system\classes\Configuration;

// init Core
Core::init();
RestfulAPI::init();

//init configuration
Configuration::init();

// get API settings
$webapi_settings = RestfulAPI::getSettings();


// 0. verify the web-api current status
if( !$webapi_settings["webapi-enabled"] ){
	// error : the web-api service is temporarily down
	sendResponse( 503, 'Service Unavailable', 'The API service is temporary unavailable.', 'plaintext', null );
}


// 1. parse 'format' argument
if( !isset($_GET['__format__']) || !is_string($_GET['__format__']) || strlen($_GET['__format__']) <= 0 || !in_array( $_GET['__format__'], $webapi_settings['global']['parameters']['embedded']['format']['values'] ) ){
	// error : not provided or unrecognized format
	sendResponse( 400, 'Bad Request', 'Unknown response format', 'plaintext', null );
}
$format = $_GET['__format__'];


// 2. parse 'apiversion' argument
if( !isset($_GET['__apiversion__']) || !is_string($_GET['__apiversion__']) || strlen($_GET['__apiversion__']) <= 0 || !isset($webapi_settings['versions'][$_GET['__apiversion__']]) ){
	// error : not provided or unrecognized apiversion
	sendResponse( 400, 'Bad Request', 'Invalid API version', $format, null );
}else{
	$version = $_GET['__apiversion__'];
	if( !$webapi_settings['versions'][$version]['enabled'] ){
		// the web-api-$version is too old and it has been deprecated, please upgrade your client
		sendResponse( 426, 'Upgrade Required', "The requested API is not supported anymore. Please upgrade your application and retry.", $format, null );
	}
}

// load web-api specifications
$webapi = RestfulAPI::getConfiguration();
$version = $_GET['__apiversion__'];
$webapi = $webapi[$version];
$authorized = false;


// 2. parse 'token' argument
if( isset($_GET['__service__']) && isset($_GET['__action__']) && $_GET['__service__']=='session' && $_GET['__action__']=='start' ){
	$authorized = true;
}else{
	if( !isset($_GET['token']) || !is_string($_GET['token']) || !StringType::isValid( $_GET['token'], StringType::ALPHANUMERIC, 16 ) ){
		// error : token not provided
		sendResponse( 400, 'Bad Request', 'The token provided is not valid', $format, null );
	}
}
$token = $_GET['token'];





// 4. check for requested service
if( !isset($_GET['__service__']) || !is_string($_GET['__service__']) || strlen($_GET['__service__']) <= 0 || !array_key_exists( strtolower($_GET['__service__']), $webapi['services'] ) ){
	// error : unrecognized service
	sendResponse( 404, 'Not Found', "The service '".$_GET['__service__']."' was not found", $format, null );
}
$serviceName = strtolower($_GET['__service__']);
$service = $webapi['services'][$serviceName];


// 5. check for service availability
if( !$service['enabled'] ){
	// error : the requested service is temporarily down
	sendResponse( 503, 'Service Unavailable', "The requested service ('".$serviceName."') was disabled by the administrator", $format, null );
}


// 6. check for requested action
if( !isset($_GET['__action__']) || !is_string($_GET['__action__']) || strlen($_GET['__action__']) <= 0 || !array_key_exists( strtolower($_GET['__action__']), $service['actions'] ) ){
	// error : unrecognized action
	sendResponse( 404, 'Not Found', "The action '".$_GET['__action__']."' was not found", $format, null );
}
$actionName = strtolower($_GET['__action__']);
$action = $service['actions'][$actionName];


// 7. check for action availability
if( !$action['enabled'] ){
	// error : the requested action is temporarily down
	sendResponse( 503, 'Service Unavailable', "The requested action ('".$actionName."') was disabled by the administrator", $format, null );
}


// 8. check for authorization
$access_lvl = $action['access_level'];
$read_only_session = $action['read_only_session'];
$need_login = !in_array('guest', $access_lvl);

// <= INIT SESSION (if needed)
//TODO: change this and do not open a session if this is not a webcall
if( $need_login || !$read_only_session ){
	Core::startSession();
}

if( $need_login ){
	$authorized = $authorized || ( $_SESSION['TOKEN'] == $token && Core::isUserLoggedIn() );
}else{
	$authorized = true;
}

//
if( !$authorized ){
	// error : authorization failed
	sendResponse( 401, 'Unauthorized', 'Authentication failed', $format, null );
}

// <= CLOSE SESSION (if needed)
if( $need_login && $read_only_session ){
	session_write_close();
}


// 9. decode the arguments
$arguments = array();
foreach( $_GET as $key => $value ){
	$arguments[$key] = urldecode( $value );
}
foreach( $_POST as $key => $value ){
	$arguments[$key] = urldecode( $value );
}

// <= LOAD INTERPRETER
require_once sprintf("%s/api/%s/api-interpreter/APIinterpreter.php", $GLOBALS['__SYSTEM__DIR__'], $version );
use system\api\apiinterpreter\APIinterpreter as Interpreter;


// 9. the api call is valid and authorized
$result = Interpreter::interpret( $service, $actionName, $arguments, $format );


// 10. send back the api call result
sendResponse( $result['code'], $result['status'], $result['message'], $format, $result['data'], !isset($result['formatted']) );


// ==================================================================================================================
// ==================================================================================================================
// ==================================================================================================================


function sendResponse( $code, $status, $message, $format, $data, $reFormatData=true ){
	//usleep( 10 /* sec */ * 1000 /* ms */ * 1000 /* us */ ); //DEBUG only
	$content_type = array('plaintext' => 'text/plain', 'json' => 'application/json', 'xml' => 'text/xml', 'html' => 'text/html');
	//
	if( $reFormatData ){
		$container = array();
		$container['code'] = $code;
		$container['status'] = $status;
		if( $message !== null ) $container['message'] = $message;
		if( $data !== null ) $container['data'] = $data;
		//
		require_once $GLOBALS['__SYSTEM__DIR__'].'/api/formatter/'.$format.'_formatter.php';
		$data = formatData( $container );
	}
	//
	ob_clean();
	//
	header('HTTP/1.x 200 OK');
	header('Connection: close');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Content-Type: '.$content_type[$format].'; charset=UTF-8');
	header("Content-Length: " . strlen($data) );
	//
	echo $data;
	//
	die();
	exit;
}//sendResponse

?>

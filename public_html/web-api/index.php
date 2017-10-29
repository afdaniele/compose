<?php


//error_reporting(E_ALL ^ E_NOTICE); //TODO
//ini_set('display_errors', 1); //TODO


// unset the control variables
$_GET['ADMIN_LOGGED'] = false;
unset( $_GET['ADMIN_ID'] );
//


if( isset($_GET['error']) ){
	sendResponse( 400, 'Bad Request', 'Invalid API call, please check and retry!', 'plaintext', null );
}


// load core classes and utility
require_once __DIR__.'/../system/classes/Core.php';
use system\classes\Core as Core;


require_once __DIR__.'/../system/classes/enum/StringType.php';
use system\classes\enum\StringType as StringType;


require_once __DIR__.'/../system/utils/utils.php';


require_once __DIR__.'/../system/classes/Configuration.php';
use system\classes\Configuration as Configuration;


//init configuration
Configuration::init();

$cache = null;
if( Configuration::$CACHE_ENABLED ){
	// load fast cache system
	require_once __DIR__.'/../system/classes/phpfastcache/phpfastcache.php';
	try{
		$cache = phpFastCache(Configuration::$CACHE_SYSTEM);
	}catch(Exception $e){
		$cache = null;
		Configuration::$CACHE_ENABLED = false;
	}
}
//
Configuration::$CACHE_ENABLED = ( $cache != null && $cache instanceof phpFastCache );


// load web-api settings
if( Configuration::$CACHE_ENABLED ){
	$webapi_settings = $cache->get( "WEB-API-SETTINGS" );
	if( $webapi_settings == null ){
		// read from file
		$sett_file_content = file_get_contents( __DIR__.'/../system/api/web-api-settings.json' );
		$webapi_settings = json_decode($sett_file_content, true);
		// save the web-api settings into the cache for:  60 seconds * 60 minutes * 24 hours = 86400 seconds = 1 day
		$cache->set( "WEB-API-SETTINGS" , serialize($webapi_settings) , 86400 );
	}else{
		$webapi_settings = unserialize( $webapi_settings );
	}
}else{
	// read from file
	$sett_file_content = file_get_contents( __DIR__.'/../system/api/web-api-settings.json' );
	$webapi_settings = json_decode($sett_file_content, true);
}

// 0. parse 'apiversion' argument
if( !isset($_GET['apiversion']) || !is_string($_GET['apiversion']) || strlen($_GET['apiversion']) <= 0 || !isset($webapi_settings['versions'][$_GET['apiversion']]) ){
	// error : not provided or unrecognized apiversion
	$format = ( file_exists( __DIR__.'/../system/api/formatter/'.$_GET['format'].'_formatter.php')? $_GET['format'] : 'plaintext');
	sendResponse( 400, 'Bad Request', 'Invalid API version', $format, null );
}else{
	$version = $_GET['apiversion'];
	if( !$webapi_settings['versions'][$version]['enabled'] ){
		// the web-api-$version is too old and it has been deprecated, please upgrade your client
		$format = ( file_exists( __DIR__.'/../system/api/formatter/'.$_GET['format'].'_formatter.php')? $_GET['format'] : 'plaintext');
		sendResponse( 426, 'Upgrade Required', "The requested API is not supported anymore. Please upgrade your application and retry.", $format, null );
	}
}


// load web-api configuration
require_once __DIR__.'/../system/api/'.$version.'/api-config/configuration.php';


// load web-api specifications
if( Configuration::$CACHE_ENABLED ){
	$webapi = $cache->get( "WEB-API-SPECIFICATION-".$version );
	if( $webapi == null ){
		// read from file
		$spec_file_content = file_get_contents( __DIR__.'/../system/api/'.$version.'/api-config/web-api-specification.json' );
		$webapi = json_decode($spec_file_content, true);
		// save the web-api specifications into the cache for:  60 seconds * 60 minutes * 24 hours = 86400 seconds = 1 day
		$cache->set( "WEB-API-SPECIFICATION-".$version , serialize($webapi) , 86400 );
	}else{
		$webapi = unserialize( $webapi );
	}
}else{
	// read from file
	$spec_file_content = file_get_contents( __DIR__.'/../system/api/'.$version.'/api-config/web-api-specification.json' );
	$webapi = json_decode($spec_file_content, true);
}


$authorized = false;

// 1. parse 'format' argument
if( !isset($_GET['format']) || !is_string($_GET['format']) || strlen($_GET['format']) <= 0 || !in_array( $_GET['format'], $webapi['global']['parameters']['embedded']['format']['values'] ) ){
	// error : not provided or unrecognized format
	sendResponse( 400, 'Bad Request', 'Unknown response format', 'plaintext', null );
}
$format = $_GET['format'];


// 2. parse 'token' argument
if( isset($_GET['service']) && isset($_GET['action']) && $_GET['service']=='session' && $_GET['action']=='start' ){
	$authorized = true;
}else{
	if( !isset($_GET['token']) || !is_string($_GET['token']) || strlen($_GET['token']) !== 16 || !StringType::isValid( $_GET['token'], StringType::$ALPHANUMERIC ) ){
		// error : token not provided
		sendResponse( 400, 'Bad Request', 'The token provided is not valid', $format, null );
	}
}
$token = $_GET['token'];


// 3. verify the web-api current status
if( !$WEBAPI_ENABLED ){
	// error : the web-api service is temporarily down
	sendResponse( 503, 'Service Unavailable', 'The API service is temporary unavailable.', $format, null );
}


// 4. check for requested service
if( !isset($_GET['service']) || !is_string($_GET['service']) || strlen($_GET['service']) <= 0 || !array_key_exists( strtolower($_GET['service']), $webapi['services'] ) ){
	// error : unrecognized service
	sendResponse( 404, 'Not Found', "The service '".$_GET['service']."' was not found", $format, null );
}
$serviceName = strtolower($_GET['service']);
$service = $webapi['services'][$serviceName];


// 5. check for service availability
if( !$service['enabled'] ){
	// error : the requested service is temporarily down
	sendResponse( 503, 'Service Unavailable', "The requested service ('".$serviceName."') was disabled by the administrator", $format, null );
}


// 6. check for requested action
if( !isset($_GET['action']) || !is_string($_GET['action']) || strlen($_GET['action']) <= 0 || !array_key_exists( strtolower($_GET['action']), $service['actions'] ) ){
	// error : unrecognized action
	sendResponse( 404, 'Not Found', "The command '".$_GET['action']."' was not found", $format, null );
}
$actionName = strtolower($_GET['action']);
$action = $service['actions'][$actionName];


// 7. check for action availability
if( !$action['enabled'] ){
	// error : the requested action is temporarily down
	sendResponse( 503, 'Service Unavailable', "The requested command ('".$actionName."') was disabled by the administrator", $format, null );
}


// 8. check for authorization
$access = $action['access_level'];

// <= INIT SESSION (if needed)
if( $access == 'logged' || !$action['read_only_session'] ){
	Core::startSession();
}

if( $access == 'logged' ){
	$authorized = $authorized || ( $_SESSION['TOKEN'] == $token && Core::isAdministratorLoggedIn() );
}else{
	$authorized = true;
}
//
if( Core::isAdministratorLoggedIn() ){
	$_GET['ADMIN_LOGGED'] = true;
	$_GET['ADMIN_ID'] = Core::getAdministratorLogged('username');
}
//
if( !$authorized ){
	// error : authorization failed
	sendResponse( 401, 'Unauthorized', 'Authentication failed', $format, null );
}

// <= CLOSE SESSION (if needed)
if( $access == 'logged' && $action['read_only_session'] ){
	session_write_close();
}


// 9. decode the arguments
$arguments = array();
foreach( $_GET as $key => $value ){
	$arguments[$key] = urldecode( $value );
}

// <= LOAD INTERPRETER
require_once __DIR__.'/../system/api/'.$version.'/api-interpreter/APIinterpreter.php';
use system\api\apiinterpreter\APIinterpreter as Interpreter;


// 9. the api call is valid and authorized
$result = Interpreter::interpret( $serviceName, $actionName, $arguments, $format );


// 10. send back the api call result
sendResponse( $result['code'], $result['status'], $result['message'], $format, $result['data'], !isset($result['formatted']) );


// ==================================================================================================================
// ==================================================================================================================
// ==================================================================================================================


function sendResponse( $code, $status, $message, $format, $data, $reFormatData=true ){
	//usleep( 10 /* sec */ * 1000 /* ms */ * 1000 /* us */ );//TODO
	$content_type = array('plaintext' => 'text/plain', 'json' => 'application/json', 'xml' => 'text/xml', 'html' => 'text/html');
	//
	if( $reFormatData ){
		$container = array();
		$container['code'] = $code;
		$container['status'] = $status;
		if( $message !== null ) $container['message'] = $message;
		if( $data !== null ) $container['data'] = $data;
		//
		require_once __DIR__.'/../system/api/formatter/'.$format.'_formatter.php';
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

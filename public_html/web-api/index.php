<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


//error_reporting(E_ALL ^ E_NOTICE); //DEBUG only
//ini_set('display_errors', 1); //DEBUG only

ini_set('display_errors', 0);
$DEBUG = false;

class AUTH_MODE {
    const BROWSER_COOKIES = 0;
    const API_APP = 1;
}//AUTH_MODE

const CONTENT_TYPE = [
    'plain' => 'text/plain',
    'plaintext' => 'text/plain',
    'json' => 'application/json',
    'xml' => 'text/xml',
    'html' => 'text/html'
];

$GLOBALS['__API_DEBUG__'] = [];
function API_DEBUG($key, $value) {
    $GLOBALS['__API_DEBUG__'][$key] = $value;
}

// load constants
require_once __DIR__ . '/../system/environment.php';

// error received from .htaccess due to an invalid API url
if (isset($_GET['__error__'])) {
    sendResponse(400, 'Bad Request', 'Invalid API call, please check and retry!', 'plaintext', null);
}

// load core classes and utility
require_once $GLOBALS['__SYSTEM__DIR__'] . '/classes/Core.php';
require_once $GLOBALS['__SYSTEM__DIR__'] . '/classes/RESTfulAPI.php';
require_once $GLOBALS['__SYSTEM__DIR__'] . '/classes/Configuration.php';
require_once $GLOBALS['__SYSTEM__DIR__'] . '/classes/enum/StringType.php';
require_once $GLOBALS['__SYSTEM__DIR__'] . '/utils/utils.php';

use system\classes\Core;
use system\classes\RESTfulAPI;
use system\classes\enum\StringType;
use system\classes\Configuration;

// init Core
Core::init();
RESTfulAPI::init();

//init configuration
Configuration::init();

// get API settings
$webapi_settings = RESTfulAPI::getSettings();

// merge $_GET and $_POST into $_GET with $_GET having the priority
$_GET = array_merge($_POST, $_GET);

// 0. verify the web-api current status
if (!$webapi_settings["webapi-enabled"]) {
    // error : the web-api service is temporarily down
    sendResponse(503, 'Service Unavailable', 'The API service is temporary unavailable.', 'plaintext', null);
}


// 1. parse 'format' argument
if (!isset($_GET['__format__']) || !is_string($_GET['__format__']) || strlen($_GET['__format__']) <= 0 || !in_array($_GET['__format__'], $webapi_settings['global']['parameters']['embedded']['format']['values'])) {
    // error : not provided or unrecognized format
    sendResponse(400, 'Bad Request', 'Unknown response format', 'plaintext', null);
}
$format = $_GET['__format__'];


// 2. parse 'apiversion' argument
if (!isset($_GET['__apiversion__']) || !is_string($_GET['__apiversion__']) || strlen($_GET['__apiversion__']) <= 0 || !isset($webapi_settings['versions'][$_GET['__apiversion__']])) {
    // error : not provided or unrecognized apiversion
    sendResponse(400, 'Bad Request', 'Invalid API version', $format, null);
} else {
    $version = $_GET['__apiversion__'];
    if (!$webapi_settings['versions'][$version]['enabled']) {
        // the web-api-$version is too old and it has been deprecated, please upgrade your client
        sendResponse(426, 'Upgrade Required', "The requested API is not supported anymore. Please upgrade your application and retry.", $format, null);
    }
}


// 3. load web-api specifications
$webapi = RESTfulAPI::getConfiguration();
$version = $_GET['__apiversion__'];
$webapi = $webapi[$version];


// 4. select authorization mode
$auth_mode = AUTH_MODE::API_APP;
if (isset($_GET['token'])) {
    $auth_mode = AUTH_MODE::BROWSER_COOKIES;
}
Core::setVolatileSession($auth_mode == AUTH_MODE::API_APP);


// 5. parse optional global arguments [token, app_id, app_secret]
if (($auth_mode == AUTH_MODE::BROWSER_COOKIES) && (!isset($_GET['token']) || !is_string($_GET['token']) || !StringType::isValid($_GET['token'], StringType::ALPHANUMERIC, 16))) {
    // error : token not provided
    sendResponse(400, 'Bad Request', 'The `token` provided is not valid', $format, null);
}
if (($auth_mode == AUTH_MODE::API_APP) && (!isset($_GET['app_id']) || !is_string($_GET['app_id']))) {
    // error : app_id not provided
    sendResponse(400, 'Bad Request', 'The `app_id` argument is mandatory', $format, null);
}
if (($auth_mode == AUTH_MODE::API_APP) && (!isset($_GET['app_secret']) || !is_string($_GET['app_secret']) || !StringType::isValid($_GET['app_secret'], StringType::ALPHANUMERIC, 48))) {
    // error : app_secret not provided
    sendResponse(400, 'Bad Request', 'The `app_secret` argument is not valid', $format, null);
}


// 6. check for requested service
if (!isset($_GET['__service__']) || !is_string($_GET['__service__']) || strlen($_GET['__service__']) <= 0 || !array_key_exists(strtolower($_GET['__service__']), $webapi['services'])) {
    // error : unrecognized service
    sendResponse(404, 'Not Found', "The service '" . $_GET['__service__'] . "' was not found", $format, null);
}
$serviceName = strtolower($_GET['__service__']);
$service = $webapi['services'][$serviceName];


// 7. check for service availability
if (!$service['enabled']) {
    // error : the requested service is temporarily down
    sendResponse(503, 'Service Unavailable', "The requested service ('" . $serviceName . "') was disabled by the administrator", $format, null);
}


// 8. check for requested action
if (!isset($_GET['__action__']) || !is_string($_GET['__action__']) || strlen($_GET['__action__']) <= 0 || !array_key_exists(strtolower($_GET['__action__']), $service['actions'])) {
    // error : unrecognized action
    sendResponse(404, 'Not Found', "The action '" . $_GET['__action__'] . "' was not found", $format, null);
}
$actionName = strtolower($_GET['__action__']);
$action = $service['actions'][$actionName];


// 9. check for action availability
if (!$action['enabled']) {
    // error : the requested action is temporarily down
    sendResponse(503, 'Service Unavailable', "The requested action ('" . $actionName . "') was disabled by the administrator", $format, null);
}


// 10. authorize call
$authorized = false;
// get access level info for the selected action
$access_lvl = $action['access_level'];
$error_msg = 'Authentication error. Contact the administrator';
// try to authorize the call
switch ($auth_mode) {
    case AUTH_MODE::BROWSER_COOKIES:
        // make sure this action supports this authentication mode
        if (!in_array('web', $action['authentication'])) {
            $error_msg = sprintf('The API end-point `%s/%s` cannot be used with authentication via Cookies', $serviceName, $actionName);
            break;
        }
        // authorize based on the access level. The user's role must be in $action['access_level']
        $access_lvl_success = False;
        foreach ($access_lvl as $lvl) {
            $parts = explode(':', $lvl);
            $package = (count($parts) == 1) ? 'core' : $parts[0];
            $cur_lvl = (count($parts) == 1) ? $parts[0] : $parts[1];
            $user_role = Core::getUserRole($package);
            //
            $access_lvl_success = boolval($access_lvl_success || boolval($user_role == $cur_lvl));
        }
        if (!$access_lvl_success) {
            $error_msg = 'The selected action cannot be executed by the current user. No role matches the access level.';
            break;
        }
        // check if the selected action has an access level that requires login
        $need_login = !in_array('guest', $access_lvl);
        // init a PHP session (if needed)
        $user_logged_in = false;
        $user_session_token = null;
        if ($need_login) {
            Core::startSession();
            //
            $user_logged_in = Core::isUserLoggedIn();
            $user_session_token = $user_logged_in ? $_SESSION['TOKEN'] : null;
            //
            Core::closeSession();
        }
        // authorize based on login info available on the server if the access level is higher than `guest`
        $token = $_GET['token'];
        $token_success = boolval(!$need_login || ($user_logged_in && $user_session_token == $token));
        if (!$token_success) {
            $error_msg = 'The token provided is not correct';
            break;
        }
        // an authorized user has the right bits to access the action and the right token
        $authorized = boolval($token_success && $access_lvl_success);
        break;
    case AUTH_MODE::API_APP:
        // make sure this action supports this authentication mode
        if (!in_array('app', $action['authentication'])) {
            $error_msg = sprintf('The API end-point `%s/%s` cannot be used with authentication via API Application', $serviceName, $actionName);
            break;
        }
        // get `app_id` and `app_secret`
        $app_id = urldecode($_GET['app_id']);
        $app_secret = urldecode($_GET['app_secret']);
        // authorize using the app
        $res = Core::authorizeUserWithAPIapp($app_id, $app_secret);
        if (!$res['success']) {
            $error_msg = $res['data'];
            break;
        }
        $user = $res['data']['user'];
        $app = $res['data']['app'];
        // check if the app has access to the requested service/action pair
        $requested_pair = sprintf("%s/%s", $serviceName, $actionName);
        if (!in_array($requested_pair, $app['endpoints'])) {
            $error_msg = sprintf('The application `%s` does not have access to the API end-point `%s`', $app['id'], $requested_pair);
            break;
        }
        // check if the user has access to the requested service/action pair
        if (!in_array($user['role'], $access_lvl)) {
            $error_msg = sprintf('The selected action cannot be executed by a user with role `%s`', $user['role']);
            break;
        }
        // user is authorized
        $authorized = true;
        break;
    default:
        $authorized = false;
        break;
}
// return error if the call cannot be authorized
if (!$authorized) {
    // error : authorization failed
    sendResponse(401, 'Unauthorized', $error_msg, $format, null);
}


// 11. decode the string arguments
$arguments = array();
foreach ($_GET as $key => $value) {
    $arguments[$key] = is_string($value) ? urldecode($value) : $value;
}

// <= LOAD INTERPRETER
require_once sprintf("%s/api/%s/api-interpreter/APIinterpreter.php", $GLOBALS['__SYSTEM__DIR__'], $version);

use system\api\apiinterpreter\APIinterpreter as Interpreter;


// 12. the api call is valid and authorized
$result = Interpreter::interpret($service, $actionName, $arguments, $format);


// 13. send back the api call result
sendResponse($result['code'], $result['status'], $result['message'], $format, $result['data']);


// ==================================================================================================================
// ==================================================================================================================
// ==================================================================================================================


function sendResponse($code, $status, $message, $format, $data) {
    global $DEBUG;
    // prepare data
    $container = [
        'code' => $code,
        'status' => $status,
        'message' => $message ?? null,
        'data' => $data ?? null
    ];
    // debug
    if ($DEBUG) $container['debug'] = $GLOBALS['__API_DEBUG__'];
    // import formatter
    require_once $GLOBALS['__SYSTEM__DIR__'] . '/api/formatter/' . $format . '_formatter.php';
    // format data
    $data = formatData($container);
    //
    if (ob_get_length()) ob_clean();
    //
    header('HTTP/1.x 200 OK');
    header('Connection: close');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: ' . CONTENT_TYPE[$format] . '; charset=UTF-8');
    header("Content-Length: " . strlen($data));
    //
    echo $data;
    //
    die();
    exit;
}//sendResponse

?>

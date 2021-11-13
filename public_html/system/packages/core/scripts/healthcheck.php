<?php

// define fatal error
define('E_FATAL', E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR |
    E_COMPILE_ERROR | E_RECOVERABLE_ERROR);

//Custom error handling vars
define('ERROR_REPORTING', E_FATAL);

// set error handler functions
register_shutdown_function('_error_handler_shutdown_function');
set_error_handler('_error_handler_generic_error_function');


function _error_handler_shutdown_function() {
    // get error
    $error = error_get_last();
    if (is_null($error)) {
        return;
    }
    $message = null;
    // pass the error to the generic error handler function
    if ($error && ($error['type'] & E_FATAL)) {
        $message = _error_handler_generic_error_function($error['type'], $error['message'], $error['file'], $error['line']);
    }
    //
    if (is_null($message)) {
        $message = 'Generic Error';
    }
    _output(500, 'Internal Server Error', 'Unhealthy');
}//_error_handler_shutdown_function


function _error_handler_generic_error_function($errno, $errstr, $errfile, $errline) {
    // define $typestr
    switch ($errno) {
        case E_ERROR: // 1 //
            $typestr = 'E_ERROR';
            break;
        case E_WARNING: // 2 //
            $typestr = 'E_WARNING';
            break;
        case E_PARSE: // 4 //
            $typestr = 'E_PARSE';
            break;
        case E_NOTICE: // 8 //
            $typestr = 'E_NOTICE';
            break;
        case E_CORE_ERROR: // 16 //
            $typestr = 'E_CORE_ERROR';
            break;
        case E_CORE_WARNING: // 32 //
            $typestr = 'E_CORE_WARNING';
            break;
        case E_COMPILE_ERROR: // 64 //
            $typestr = 'E_COMPILE_ERROR';
            break;
        case E_CORE_WARNING: // 128 //
            $typestr = 'E_COMPILE_WARNING';
            break;
        case E_USER_ERROR: // 256 //
            $typestr = 'E_USER_ERROR';
            break;
        case E_USER_WARNING: // 512 //
            $typestr = 'E_USER_WARNING';
            break;
        case E_USER_NOTICE: // 1024 //
            $typestr = 'E_USER_NOTICE';
            break;
        case E_STRICT: // 2048 //
            $typestr = 'E_STRICT';
            break;
        case E_RECOVERABLE_ERROR: // 4096 //
            $typestr = 'E_RECOVERABLE_ERROR';
            break;
        case E_DEPRECATED: // 8192 //
            $typestr = 'E_DEPRECATED';
            break;
        case E_USER_DEPRECATED: // 16384 //
            $typestr = 'E_USER_DEPRECATED';
            break;
    }
    
    // compile an error message
    $message = '<b>' . $typestr . ': </b>' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b><br/>';
    
    // do nothing if the error ($errno) is not in ERROR_REPORTING
    if (!($errno & ERROR_REPORTING)) {
        return;
    }
    
    // return default message
    return $message;
}//_error_handler_generic_error_function

function _output($code, $status, $data) {
    if (ob_get_length()) {
        ob_clean();
    }
    //
    header(sprintf('HTTP/1.x %d %s', $code, $status));
    header('Connection: close');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: text/plain; charset=UTF-8');
    header("Content-Length: " . strlen($data));
    //
    echo $data;
    //
    die;
}//_output


// ==================================================================================================================
// ==================================================================================================================
// ==================================================================================================================


// load constants
require_once __DIR__ . '/../../../environment.php';

// load core classes and utility
require_once $GLOBALS['__SYSTEM__DIR__'] . '/classes/Core.php';

use exceptions\BaseException;
use system\classes\Core;

// init Core
try {
    Core::init();
} catch (BaseException $e) {
    _output(500, 'Internal Server Error', 'Unhealthy');
}

// perform Health Check on Core module
$healthy = Core::healthCheck();
if ($healthy) {
    _output(200, 'OK', 'Healthy');
} else {
    _output(500, 'Internal Server Error', 'Unhealthy');
}

?>

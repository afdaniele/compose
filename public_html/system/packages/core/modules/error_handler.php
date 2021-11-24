<?php

// load constants
require_once __DIR__ . '/../../../environment.php';

// load utils
require_once $GLOBALS['__SYSTEM__DIR__'] . 'classes/Utils.php';

use \system\classes\Utils;

// define fatal error
define('E_FATAL', E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR |
    E_COMPILE_ERROR | E_RECOVERABLE_ERROR);

//Custom error handling vars
define('ERROR_REPORTING', E_FATAL);
define('LOG_ERRORS', TRUE);


function _error_handler_shutdown_function() {
    // get error
    $error = error_get_last();
    // pass the error to the generic error handler function
    if ($error && ($error['type'] & E_FATAL)) {
        _error_handler_generic_error_function($error['type'], $error['message'], $error['file'], $error['line']);
    }
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
    
    // logging error on php file error log
    if (LOG_ERRORS) {
        //TODO: logging everything here could easily exhaust the hard drive space
    }
    
    if (\system\classes\Core::isInitialized()) {
        // set the message for the error page and redirect to it
        $_SESSION['_ERROR_PAGE_MESSAGE'] = $message;
        \system\classes\Core::redirectTo('error');
    } else {
        echo "ERROR: {$message}";
        die();
    }
}//_error_handler_generic_error_function

// set error handler functions
register_shutdown_function('_error_handler_shutdown_function');

function print_exception_plain(Throwable $e) {
    $e_class = get_class($e);
    $stack_trace = Utils::formatStacktrace($e);
    $msg = "
<strong>Exception</strong>: {$e_class}<br/>
<strong>Error</strong>: {$e->getMessage()}<br/>
<strong>Stack Trace</strong>:<pre>{$stack_trace}</pre>
";
    echo $msg;
}

set_error_handler(function ($code, $message) {
    // convert error to ErrorException
    throw new ErrorException($message, $code);
});

set_exception_handler("print_exception_plain");

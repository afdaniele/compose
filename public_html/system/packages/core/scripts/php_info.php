<?php

// load core classes and utility
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Core.php';

// simplify namespaces
use system\classes\Core;

// init Core
$success = Core::init();
if (!$success){
    echo "FATAL";
    die();
}

// create a Session
Core::startSession();

// only admin can see this
if (Core::isUserLoggedIn() && Core::getUserLogged('role') == "administrator") {
    if (ob_get_length() > 0)
        ob_clean();
    phpinfo();
    die();
}

echo "Only administrators can request the `php_info` script.";
die();

?>

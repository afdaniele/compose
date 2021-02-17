<?php

// load core classes and utility
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Core.php';

// simplify namespaces
use system\classes\Core;

// init Core
$res = Core::init();
if (!$res['success']){
    echo $res['data'];
    die($res['data']);
}

// create a Session
Core::startSession();

// only admin can see this
if (Core::isUserLoggedIn() && Core::getUserLogged('role') == "administrator") {
    ob_clean();
    phpinfo();
    die();
}

echo "Only administrators can request the `php_info` script.";
die();

?>

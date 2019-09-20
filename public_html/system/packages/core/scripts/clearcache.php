<?php

// load constants
require_once __DIR__.'/../../../environment.php';

// load cache class
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Configuration.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Cache.php';
use \system\classes\Configuration;
use \system\classes\Cache;

// init cache
Configuration::init();
Cache::init();

// clear cache
Cache::clearAll();

?>

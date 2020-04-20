<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

// load core libraries
require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Core.php';

// simplify namespaces
use system\classes\Core;

// create a Session
Core::startSession();

// init Core
$res = Core::init();
if (!$res['success']){
    echo $res['data'];
    die($res['data']);
}

// ==> Your code after this line
// ===============================================>


echo 'All is well!';


// <===============================================
// <== Your code before this line

// terminate
Core::close();
?>
<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


// get info
$packageName = $_GET['package'];
$scriptFile = $_GET['script'];

// make sure package and script names do not contain illegal characters
$packageName = preg_replace('/[^a-z0-9_-]/', '', $packageName);
$scriptFile = preg_replace('/[^a-zA-Z0-9_.-]/', '', $scriptFile);

// check whether the script exists
$scriptPath = sprintf("%s/../system/packages/%s/js/%s", __DIR__, $packageName, $scriptFile);

if( !file_exists($scriptPath) ){
    $scriptPath = sprintf("%s/../js/empty.js", __DIR__);
}

// open the file in a binary mode
$fp = fopen($scriptPath, 'rb');
$fsize = filesize($scriptPath);

// clean buffer
ob_clean();

// send the right headers
header( "Content-Type: application/javascript" );
header( sprintf("Content-Length: %s", $fsize), true );

// dump the file and stop the script
fpassthru($fp);
exit;

<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 15th 2018


// get info
$packageName = $_GET['package'];
$styleFile = $_GET['stylesheet'];

// make sure package and stylesheet names do not contain illegal characters
$packageName = preg_replace('/[^a-z0-9_-]/', '', $packageName);
$styleFile = preg_replace('/[^a-zA-Z0-9_.-]/', '', $styleFile);

// check whether the stylesheet exists
$stylePath = sprintf("%s/../system/packages/%s/css/%s", __DIR__, $packageName, $styleFile);
if( !file_exists($stylePath) ){
    $stylePath = sprintf("%s/../css/empty.css", __DIR__);
}

// open the file in a binary mode
$fp = fopen($stylePath, 'rb');
$fsize = filesize($stylePath);

// clean buffer
ob_clean();

// send the right headers
header( "Content-Type: text/css", true );
header( sprintf("Content-Length: %s", $fsize), true );

// dump the file and stop the script
fpassthru($fp);
exit;

<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


// get info
$packageName = $_GET['package'];
$imageName = $_GET['image'];

// make sure package and image names do not contain illegal characters
$packageName = preg_replace('/[^a-z0-9_-]/', '', $packageName);
$imageName = preg_replace('/[^a-zA-Z0-9_.-]/', '', $imageName);

// check whether the image exists
$imagePath = sprintf("%s/../system/packages/%s/images/%s", __DIR__, $packageName, $imageName);
if( !file_exists($imagePath) ){
    $imagePath = sprintf("%s/../images/placeholder.png", __DIR__);
}

// open the file in a binary mode
$fp = fopen($imagePath, 'rb');
$fsize = filesize($imagePath);

// get info about the image
$imageInfo = getimagesize($imagePath);
if( strpos($imageName, '.svg') !== false ){
    $imageInfo['mime'] = 'image/svg+xml';
}

// clean buffer
ob_clean();

// send the right headers
header( sprintf("Content-Type: %s", $imageInfo['mime']), true );
header( sprintf("Content-Length: %s", $fsize), true );

// dump the picture and stop the script
fpassthru($fp);
exit;

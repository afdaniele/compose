<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once __DIR__ . '/../system/environment.php';


// get info
$packageName = $_GET['package'];
$imageName = $_GET['image'];

// make sure package and image names do not contain illegal characters
$packageName = preg_replace('/[^a-z0-9_-]/', '', $packageName);
$imageName = preg_replace('/[^a-zA-Z0-9_.\-\/]/', '', $imageName);

// check whether the image exists
$imagePaths = [
    [__DIR__ . "/../system/packages/$packageName/images/", $imageName],
    [$GLOBALS['__USERDATA__PACKAGES__DIR__'] . "$packageName/images/", $imageName],
    [__DIR__ . "/../images/", "placeholder.png"]
];

foreach ($imagePaths as $imagePath) {
    // get absolute path to bound and image file
    $bound = realpath($imagePath[0]);
    $imageFile = realpath($imagePath[0] . $imagePath[1]);
    
    // if this does not exist, try the next
    if ($bound === FALSE || $imageFile === FALSE) {
        continue;
    }
    
    // make sure that the path does not go out of bounds
    if (strlen($imageFile) <= strlen($bound) ||
        substr($imageFile, 0, strlen($bound)) != $bound ||
        $imageFile[strlen($bound)] != '/') {
        continue;
    }
    
    // open the file in a binary mode
    $fp = fopen($imageFile, 'rb');
    $fsize = filesize($imageFile);
    
    // get info about the image
    $imageInfo = getimagesize($imageFile);
    if (strpos($imagePath[1], '.svg') !== false) {
        $imageInfo['mime'] = 'image/svg+xml';
    }
    
    // clean buffer
    if (ob_get_length()) {
        ob_clean();
    }
    
    // send the right headers
    header(sprintf("Content-Type: %s", $imageInfo['mime']), true);
    header(sprintf("Content-Length: %s", $fsize), true);
    
    // dump the picture and stop the script
    fpassthru($fp);
    exit;
}

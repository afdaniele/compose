<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once __DIR__.'/../system/environment.php';


// get info
$packageName = $_GET['package'];
$imageName = $_GET['image'];

// make sure package and image names do not contain illegal characters
$packageName = preg_replace('/[^a-z0-9_-]/', '', $packageName);
$imageName = preg_replace('/[^a-zA-Z0-9_.-]/', '', $imageName);

// check whether the image exists
$imagePaths = [
  sprintf("%s/../system/packages/%s/images/%s", __DIR__, $packageName, $imageName),
  sprintf("%s%s/images/%s", $GLOBALS['__USERDATA__PACKAGES__DIR__'], $packageName, $imageName),
  sprintf("%s/../images/placeholder.png", __DIR__)
];

foreach ($imagePaths as $imagePath) {
  // if this does not exist, try the next
  if (!file_exists($imagePath)) {
    continue;
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
  if (ob_get_length()) ob_clean();

  // send the right headers
  header( sprintf("Content-Type: %s", $imageInfo['mime']), true );
  header( sprintf("Content-Length: %s", $fsize), true );

  // dump the picture and stop the script
  fpassthru($fp);
  exit;
}

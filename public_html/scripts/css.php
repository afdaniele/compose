<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once __DIR__.'/../system/environment.php';


// get info
$packageName = $_GET['package'];
$styleFile = $_GET['stylesheet'];

// make sure package and stylesheet names do not contain illegal characters
$packageName = preg_replace('/[^a-z0-9_-]/', '', $packageName);
$styleFile = preg_replace('/[^a-zA-Z0-9_.-]/', '', $styleFile);

// check whether the stylesheet exists
$stylePaths = [
  sprintf("%s/../system/packages/%s/css/%s", __DIR__, $packageName, $styleFile),
  sprintf("%s%s/css/%s", $GLOBALS['__USERDATA__PACKAGES__DIR__'], $packageName, $styleFile),
  sprintf("%s/../css/empty.css", __DIR__)
];

foreach ($stylePaths as $stylePath) {
  // if this does not exist, try the next
  if (!file_exists($stylePath)) {
    continue;
  }

  // open the file in a binary mode
  $fp = fopen($stylePath, 'rb');
  $fsize = filesize($stylePath);

  // clean buffer
  if (ob_get_length()) ob_clean();

  // send the right headers
  header( "Content-Type: text/css", true );
  header( sprintf("Content-Length: %s", $fsize), true );

  // dump the file and stop the script
  fpassthru($fp);
  exit;
}

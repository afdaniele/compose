<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once __DIR__.'/../system/environment.php';


// get info
$packageName = $_GET['package'];
$scriptFile = $_GET['script'];

// make sure package and script names do not contain illegal characters
$packageName = preg_replace('/[^a-z0-9_-]/', '', $packageName);
$scriptFile = preg_replace('/[^a-zA-Z0-9_.-]/', '', $scriptFile);

// check whether the script exists
$scriptPaths = [
  sprintf("%s/../system/packages/%s/js/%s", __DIR__, $packageName, $scriptFile),
  sprintf("%s%s/js/%s", $GLOBALS['__USERDATA__PACKAGES__DIR__'], $packageName, $scriptFile),
  sprintf("%s/../js/empty.js", __DIR__)
];

foreach ($scriptPaths as $scriptPath) {
  // if this does not exist, try the next
  if (!file_exists($scriptPath)) {
    continue;
  }

  // open the file in a binary mode
  $fp = fopen($scriptPath, 'rb');
  $fsize = filesize($scriptPath);

  // clean buffer
  if (ob_get_length()) ob_clean();

  // send the right headers
  header( "Content-Type: application/javascript" );
  header( sprintf("Content-Length: %s", $fsize), true );

  // dump the file and stop the script
  fpassthru($fp);
  exit;
}

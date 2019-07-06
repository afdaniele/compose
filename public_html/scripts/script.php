<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

// clean buffer
ob_clean();

// check arguments
foreach (['package', 'script'] as $arg) {
  if (!isset($_GET[$arg]) || strlen(trim($_GET[$arg])) < 1) {
    echo sprintf('The argument "%s" is mandatory', $arg);
    exit;
  }
}

// get info
$packageName = $_GET['package'];
$scriptName = $_GET['script'];

// make sure package and script names do not contain illegal characters
$packageName = preg_replace('/[^a-z0-9_-]/', '', $packageName);
$scriptName = preg_replace('/\.php$/', '', $scriptName);
$scriptFile = preg_replace('/[^a-zA-Z0-9_-]/', '', $scriptName);

// check whether the script exists
$scriptPath = sprintf("%s/../system/packages/%s/scripts/%s.php", __DIR__, $packageName, $scriptFile);
if (!file_exists($scriptPath)) {
  echo sprintf("Script '%s/%s' not found!", $packageName, $scriptFile);
  return;
}

// load script
require_once $scriptPath;

// close
exit;

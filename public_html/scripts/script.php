<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once __DIR__.'/../system/environment.php';


// clean buffer
if (ob_get_length()) ob_clean();

// define default arguments' values
$default_values = [
  'package' => 'core'
];
foreach ($default_values as $arg => $dval) {
  if (!isset($_GET[$arg])) {
    $_GET[$arg] = $dval;
  }
}

// check arguments
$mandatory_arguments = [
  'package',
  'script'
];
foreach ($mandatory_arguments as $arg) {
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
$scriptPaths = [
  sprintf("%s/../system/packages/%s/scripts/%s.php", __DIR__, $packageName, $scriptFile),
  sprintf("%s%s/scripts/%s.php", $GLOBALS['__USERDATA__PACKAGES__DIR__'], $packageName, $scriptFile)
];

foreach ($scriptPaths as $scriptPath) {
  // if this does not exist, try the next
  if (!file_exists($scriptPath)) {
    continue;
  }

  // load script
  require_once $scriptPath;

  // close
  exit;
}

echo sprintf("Script '%s/%s' not found!", $packageName, $scriptFile);

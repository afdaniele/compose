<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

// init global variables
$GLOBALS['__COMPOSE__DIR__'] = __DIR__.'/../';

$GLOBALS['__SYSTEM__DIR__'] = __DIR__.'/';

$GLOBALS['__DATA__DIR__'] = __DIR__.'/../data/';

$GLOBALS['__EMBEDDED__PACKAGES__DIR__'] = __DIR__.'/packages/';

$GLOBALS['__CORE__PACKAGE__DIR__'] = __DIR__.'/packages/core/';

// user-data
if (isset($_ENV['COMPOSE_USERDATA_DIR'])) {
  $GLOBALS['__USERDATA__DIR__'] = rtrim($_ENV['COMPOSE_USERDATA_DIR'], '/').'/';
}else{
  // default to /system/user-data/
  $GLOBALS['__USERDATA__DIR__'] = __DIR__.'/user-data/';
}

$GLOBALS['__USERDATA__PACKAGES__DIR__'] = sprintf('%s%s/', $GLOBALS['__USERDATA__DIR__'], 'packages');

?>

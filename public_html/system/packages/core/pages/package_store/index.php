<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;

if (!in_array(Configuration::$ACTION, ['', 'install', 'verify'])) {
    Core::redirectTo('package_store');
    return;
}

if (Configuration::$ACTION == '') {
    include_once __DIR__ . '/parts/list.php';
} elseif (Configuration::$ACTION == 'install') {
    include_once __DIR__ . '/parts/install.php';
} elseif (Configuration::$ACTION == 'verify') {
    include_once __DIR__ . '/parts/verify.php';
} else {
    Core::redirectTo('package_store');
}
?>

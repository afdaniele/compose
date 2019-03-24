<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Core;
use \system\classes\Configuration;

if (!in_array(Configuration::$ACTION, ['', 'install', 'verify'])){
  Core::redirectTo('packages');
}

if(Configuration::$ACTION == ''){
  include_once __DIR__.'/parts/list.php';
}elseif(Configuration::$ACTION == 'install'){
  include_once __DIR__.'/parts/install.php';
}elseif(Configuration::$ACTION == 'verify'){
  include_once __DIR__.'/parts/verify.php';
}else{
  Core::redirectTo('packages');
}
?>

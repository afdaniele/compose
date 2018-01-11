<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Thursday, October 12th 2017
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018


// import Surveillance core module
require_once $GLOBALS['__PACKAGES__DIR__'].'surveillance/Surveillance.php';

if( isset($_GET['segment']) ){
	// show the videos activity page
	require_once __DIR__.'/actions/segment-details.php';
}else{
	// show the videos list page
	require_once __DIR__.'/actions/segment-list.php';
}

?>

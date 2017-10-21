<?php

if( isset($_GET['segment']) ){
	// show the videos activity page
	require_once __DIR__.'/actions/segment-details.php';
}else{
	// show the videos list page
	require_once __DIR__.'/actions/segment-list.php';
}

?>

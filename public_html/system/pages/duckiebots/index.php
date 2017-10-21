<?php

if( isset($_GET['bot']) ){
	// show the bot activity page
	require_once __DIR__.'/actions/duckiebot-activity.php';
}else{
	// show the bot list page
	require_once __DIR__.'/actions/duckiebot-list.php';
}

?>

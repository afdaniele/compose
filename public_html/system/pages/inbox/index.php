<?php

if( isset($_GET['message']) ){
	// show the message details page
	require_once __DIR__ . '/actions/read-message.php';
}else{
	// show the message list page
	require_once __DIR__ . '/actions/message-list.php';
}

?>
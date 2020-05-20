<?php

$message = '';
$type = null;

$prefix = '_ALERT_';
//
if( isset($_SESSION[$prefix.'ERROR']) ){
	$message = '<strong>Error!</strong> '.$_SESSION[$prefix.'ERROR'];
	$type = 'danger';
	//
	unset( $_SESSION[$prefix.'ERROR'] );
}
if( isset($_SESSION[$prefix.'INFO']) ){
	$message = $_SESSION[$prefix.'INFO'];
	$type = 'info';
	//
	unset( $_SESSION[$prefix.'INFO'] );
}
if( isset($_SESSION[$prefix.'WARNING']) ){
	$message = $_SESSION[$prefix.'WARNING'];
	$type = 'warning';
	//
	unset( $_SESSION[$prefix.'WARNING'] );
}

if( $type != null ){
	?>
<script type="application/javascript">
	$( document ).ready(function() {
		openAlert( '<?php echo $type ?>', "<?php echo $message ?>" );
	});
</script>
<?php
}
?>


<div style="display:none" id="page_alert_container">
	<br>

	<div class="alert alert-dismissible" role="alert" id="page_alert_object">
		<button type="button" class="close" onclick="closeAlert();"><span aria-hidden="true">&times;</span><span class="sr-only">Chiudi</span></button>
		<div id="page_alert_content">

		</div>
	</div>
</div>

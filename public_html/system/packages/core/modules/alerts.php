<?php

$message = '';
$type = null;

if (isset($_SESSION['_ALERT_ERROR'])) {
    $message = '<strong>Error!</strong> ' . $_SESSION['_ALERT_ERROR'];
    $type = 'danger';
}
if (isset($_SESSION['_ALERT_INFO'])) {
    $message = $_SESSION['_ALERT_INFO'];
    $type = 'info';
}
if (isset($_SESSION['_ALERT_WARNING'])) {
    $message = $_SESSION['_ALERT_WARNING'];
    $type = 'warning';
}
?>

<div style="display:none; padding: 0 30px" id="page_alert_container">
	<div class="alert alert-dismissible" role="alert" id="page_alert_object">
		<button type="button" class="close" onclick="closeAlert();"><span aria-hidden="true">&times;</span><span class="sr-only">Chiudi</span></button>
		<div id="page_alert_content">

		</div>
	</div>
</div>

<?php
if( $type != null ){
	?>
    <script type="application/javascript">
        $(document).ready(function() {
            openAlert('<?php echo $type ?>', "<?php echo $message ?>");
        });
    </script>
<?php
}
?>
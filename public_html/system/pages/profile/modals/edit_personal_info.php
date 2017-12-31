<!-- Edit Legal Info Modal -->

<?php

$user = \system\classes\Core::getUserLogged();

$labelName = array('Name', 'E-mail address');
$inputName = array('name', 'email');
$inputPlaceholder = array('Your name', 'Your e-mail address');
$inputValue = array( $user['name'], $user['email'] );
$inputType = array('text', 'text');

?>

<div class="modal fade" id="edit-personal-info-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Edit personal information:</h4>
			</div>


			<div class="modal-body" id="custom-body">
		<div id="edit-personal-info-modal-form-div">
					<?php
					generateForm( null, null, 'edit-personal-info-modal-form', $labelName, $inputName, $inputPlaceholder, $inputValue, $inputType, true, 'md-4', 'md-6' );
					?>
				</div>
			</div>


			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-info" id="save-legal-info-button">Save</button>
			</div>
		</div>
	</div>
</div>



<script type="text/javascript">

	$('#edit-personal-info-modal #save-legal-info-button').on('click', function(){
		$('#edit-personal-info-modal').modal('hide');
		showPleaseWait();
		// get the info
		var queryString = $('#edit-personal-info-modal #edit-personal-info-modal-form').serialize();
		//
		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/userprofile/updatepersonal/json?" + queryString + "&token=<?php echo $_SESSION["TOKEN"] ?>";
		//
		callAPI( url, true, true );
	});

</script>

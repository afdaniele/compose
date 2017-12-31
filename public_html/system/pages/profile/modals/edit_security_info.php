<!-- Edit Security Info Modal -->

<?php

$labelName = array('Old Password', 'New Password', 'New Password (confirm)');
$inputName = array('secret', 'password', 'passwordconfirm');
$inputPlaceholder = array('Your old password', 'Your new password', 'Your new password');
$inputValue = array( '', '', '' );
$inputType = array('password', 'password', 'password');
$extra = array('style="margin-bottom:20px"', '', '')

?>

<div class="modal fade" id="edit-security-info-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Edit access information:</h4>
			</div>

			<div class="modal-body" id="custom-body">
				<div id="edit-security-info-modal-form-div">
					<?php
					generateForm( null, null, 'edit-security-info-modal-form', $labelName, $inputName, $inputPlaceholder, $inputValue, $inputType, true, 'md-4', 'md-6', null, $extra );
					?>
				</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-success" id="save-security-info-button">Save</button>
			</div>
		</div>
	</div>
</div>



<script type="text/javascript">

	$('#edit-security-info-modal').on('hidden.bs.modal', function (e) {
		$('#edit-security-info-modal #secret').val( '' );
		$('#edit-security-info-modal #password').val( '' );
		$('#edit-security-info-modal #passwordconfirm').val( '' );
	});

	$('#edit-security-info-modal #save-security-info-button').on('click', function(){
		$('#edit-security-info-modal').modal('hide');
		showPleaseWait();
		// get the info
		var oldpwd = $('#edit-security-info-modal #secret').val();
		var password = $('#edit-security-info-modal #password').val();
		var passwordconfirm = $('#edit-security-info-modal #passwordconfirm').val();
		var queryString = 'password='+password+'&passwordconfirm='+passwordconfirm
		var timestamp = Math.ceil( (new Date().getTime())/1000 ); // in seconds
		//
		var uri = "web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/userprofile/updatekeys/json?"+queryString+"&timestamp="+timestamp+"&token=<?php echo $_SESSION['TOKEN'] ?>";
		//
		var hashInBase64 = computeURIhmac( uri, oldpwd );


		console.log( hashInBase64 );



		// var secret = CryptoJS.MD5( oldpwd );
		// var hash = CryptoJS.HmacSHA256( uri, CryptoJS.enc.Utf8.parse(secret));
		// var hashInBase64 = CryptoJS.enc.Base64.stringify(hash);

		//
		uri += "&hmac="+CryptoJS.MD5(hashInBase64);
		//
		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>" + encodeURI( uri );


		console.log( url );

		// call the API
		callAPI( url, true, true );
	});

</script>

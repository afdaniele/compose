<div class="modal fade" id="password-recovery-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Chiudi</span></button>
				<h4 class="modal-title">Recover my password</h4>
			</div>

			<div class="modal-body" id="custom-body">
				<p>
					In order to recover your password you have to provide <span id="what-to-insert"></span>.
				</p>
				<form class="form-horizontal" id="contact-info-form" role="form">

					<div class="form-group" style="margin-bottom:4px">
						<label class="col-md-4 control-label">Username</label>
						<div class="col-md-6">
							<input type="text" class="form-control" id="username" name="username" placeholder="Your username" value="">
						</div>
					</div>

				</form>
			</div>


			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-success" id="send-button">Get a new password</button>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo \system\classes\Configuration::$BASE_URL ?>js/serialize.js"></script>

<script type="application/javascript">

	service = null;

	$('#password-recovery-modal').on('show.bs.modal', function (e) {
		service = $(e.relatedTarget).data("service");
		var insert_what = $(e.relatedTarget).data("insert-what");
		//
		$('#password-recovery-modal #what-to-insert').html(insert_what);
	});

	$('#password-recovery-modal #send-button').on('click', function(){
		// info
		var qs = serializeForm( '#password-recovery-modal #contact-info-form' );
		//
		var url = '<?php echo \system\classes\Configuration::$BASE_URL . 'web-api/' . \system\classes\Configuration::$WEBAPI_VERSION . '/' ?>'+service+'<?php echo '/recovery/json?' ?>' + qs + '&token=<?php echo $_SESSION["TOKEN"] ?>';
		//
		$('#password-recovery-modal').modal('hide');
		//
		callAPI( url, true, false, function(){
			// clear the input fields
			$('#password-recovery-modal #username').val('');
			//
			openAlert('info', "A new password has been sent to the email associated to your account.");
		} );
	});

</script>

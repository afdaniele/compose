<div class="modal fade modal-vertical-centered" id="yes-no-modal">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 style="margin:0 4px 0 4px"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span> Confirm?</h4>
			</div>

			<div class="modal-body">
				<p style="width:100%" id="question-p"></p>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
				<button type="button" class="btn btn-success" id="yes-button">Yes</button>
			</div>

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<script type="text/javascript">

  var configured = false;
	var yesNoSize = 'sm';
  var yesfunction = null;
	var showPleaseWaitModal = true;

	$('#yes-no-modal').on('show.bs.modal', function(e){
		if( !configured ){
      // set size
      $('#yes-no-modal .modal-dialog').addClass('modal-'+yesNoSize);
      // set question
			var question = $(e.relatedTarget).data('question');
			$('#yes-no-modal #question-p').html( question );
			//
			var url = $(e.relatedTarget).data('url');
			$('#yes-no-modal #yes-button').data( 'url', url );
			//
			configured = true;
		}
	});


	$('#yes-no-modal #yes-button').on('click', function(){
		$('#yes-no-modal').modal('hide');
		if(showPleaseWaitModal){showPleaseWait()};
		if( yesfunction != null ){
			yesfunction();
      if(showPleaseWaitModal){hidePleaseWait()};
		}else{
			var url = $('#yes-no-modal #yes-button').data('url');
			//
			$.ajax({type: 'GET', url:url, dataType: 'json', success:function( result ){
				if( result.code == 200 ){
					if(showPleaseWaitModal){hidePleaseWait()};
					showSuccessDialog( 2000, function(){
						window.location.reload(true);
					} );
				}else{
					// error, open an alert
					openAlert( 'danger', result.message );
					if(showPleaseWaitModal){hidePleaseWait()};
				}
			}, error:function(){
				// error
				openAlert('danger', 'An error occurred while communicating with the server. Please, try again!');
				if(showPleaseWaitModal){hidePleaseWait()};
			}});
		}
	});

	function openYesNoModal(question, yes_fcn, silentMode, size) {
		$('#yes-no-modal #question-p').html( question );
    size = (size == undefined)? 'sm' : size;
		yesfunction = yes_fcn;
    showPleaseWaitModal = !silentMode;
    yesNoSize = size;
		//
		configured = true;
		//
		$('#yes-no-modal').modal('show');
	}//openYesNoModal

</script>

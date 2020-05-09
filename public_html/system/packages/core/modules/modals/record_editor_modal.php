<!-- Record Editor Modal -->

<?php
function generateRecordEditorModal( &$layout, $formID=null, $method=null, $action=null, &$values=array(), $size='lg' ){
	$formID = ( ($formID != null)? $formID : 'the-form' );
	?>

	<style type="text/css">
		#record-editor-modal-<?php echo $formID ?> #custom-body{
			overflow: scroll;
			max-height: 500px;
			overflow-y: visible;
			overflow-x: hidden;
		}
	</style>

	<div class="modal fade" id="record-editor-modal-<?php echo $formID ?>" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-<?php echo $size ?>">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title"></h4>
				</div>

				<div class="modal-body" id="custom-body">
					<?php
					generateFormByLayout($layout, $formID, null, $method, $action, $values);
					?>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-success" id="save-button">Save</button>
				</div>
			</div>
		</div>
	</div>



	<script type="text/javascript">

		var data = null;
		var url = '';

		$('#record-editor-modal-<?php echo $formID ?>').on('show.bs.modal', function (e) {
			var mode = $(e.relatedTarget).data('modal-mode');
			//
			if( mode == 'edit'){
				$('#record-editor-modal-<?php echo $formID ?> .modal-title').html('Modify:');
			}else{
				$('#record-editor-modal-<?php echo $formID ?> .modal-title').html('Insert:');
			}
			// load the data
			var jsonobj = $(e.relatedTarget).data('record');
			data = jsonobj;
			//
			for (var key in jsonobj) {
				var field = $('#record-editor-modal-<?php echo $formID ?> #<?php echo $formID ?> #'+key);
				//
				if( jsonobj['_lock_'+key] != undefined && jsonobj['_lock_'+key]==1 ){
					field.attr('disabled', true);
				}else{
					field.attr('disabled', false);
				}
				//
				switch( field.attr('type') ){
					case 'hidden':
						field.val( jsonobj[key] );
						$('#record-editor-modal-<?php echo $formID ?> #<?php echo $formID ?> #'+key+'_p').html( jsonobj[key] );
						break;
					case 'checkbox':
						field.prop('checked', jsonobj[key]==1).change();
						break;
					case 'select':
						field.val(jsonobj[key]).change();
						break;
					default:
						field.val(jsonobj[key]);
						break;
				}
			}
			//
			url = $(e.relatedTarget).data('url');
		});

		$('#record-editor-modal-<?php echo $formID ?>').on('hide.bs.modal', function (e) {
			// clear the form
			if( data != null ){
				for (var key in data) {
					var field = $('#record-editor-modal-<?php echo $formID ?> #<?php echo $formID ?> #'+key);
					switch( field.attr('type') ){
						case 'hidden':
							field.val( '' );
							$('#record-editor-modal-<?php echo $formID ?> #<?php echo $formID ?> #'+key+'_p').html( '' );
							break;
						case 'checkbox':
							field.bootstrapToggle('off');
							break;
						case 'select':
							//TODO: Boh!
							break;
						default:
							field.val( '' );
							break;
					}
				}
			}
		});

		$('#record-editor-modal-<?php echo $formID ?> #save-button').on('click', function(){
			showPleaseWait();
			//
			var qs = serializeForm('#<?php echo $formID ?>', true);
			url = url + qs;
			//
			successDialog = true;
			reload = true;
			funct = undefined;
			silentMode = undefined;
			suppressErrors = undefined;
			errorFcn = undefined;
			transportType = 'POST';
			callAPI( url, successDialog, reload, funct, silentMode, suppressErrors, errorFcn, transportType );
			//
			$('#record-editor-modal-<?php echo $formID ?>').modal('hide');
		});

	</script>

<?php
}
?>

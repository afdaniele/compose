<!-- Record Editor Modal -->

<?php
function generateRecordEditorModal($schema, $id = null, $method = null, $action = null, &$data = [], &$ui = ["*"], $size = 'lg') {
    $id = (($id != null) ? $id : 'the-form');
    $modal_id = "record-editor-modal-$id";
    $form_id = "$modal_id-form";
    ?>

    <style>
        #
        <?php echo $modal_id ?>
        .modal-body {
            overflow: scroll;
            max-height: 500px;
            overflow-y: visible;
            overflow-x: hidden;
        }
    </style>

    <div class="modal fade" id="<?php echo $modal_id ?>" tabindex="-1">
        <div class="modal-dialog modal-<?php echo $size ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="<?php echo $form_id ?>"></form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                    </button>
                    <button type="button" class="btn btn-success" id="save-button">Save</button>
                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        let _RECORD_EDITOR_FORM_SCHEMA = <?php print json_encode($schema) ?>;
        let _RECORD_EDITOR_FORM_DATA = <?php print json_encode($data) ?>;
        let _RECORD_EDITOR_FORM_UI = <?php print json_encode($ui) ?>;
        let _RECORD_EDITOR_FORM_URL = '';
        let modal = $('#<?php echo $modal_id ?>');


        modal.on('show.bs.modal', function (e) {
            let mode = $(e.relatedTarget).data('modal-mode');
            //
            if (mode === 'edit') {
                $('#<?php echo $modal_id ?> .modal-title').html('Modify:');
            } else {
                $('#<?php echo $modal_id ?> .modal-title').html('Insert:');
            }
            // get URL
            _RECORD_EDITOR_FORM_URL = $(e.relatedTarget).data('url');
            // load the schema (if any)
            let jsonobj = $(e.relatedTarget).data('schema');
            if (jsonobj !== undefined)
                _RECORD_EDITOR_FORM_SCHEMA = jsonobj;
            // load the data (if any)
            jsonobj = $(e.relatedTarget).data('record');
            if (jsonobj !== undefined)
                _RECORD_EDITOR_FORM_DATA = jsonobj;
            // load the UI (if any)
            jsonobj = $(e.relatedTarget).data('ui');
            if (jsonobj !== undefined)
                _RECORD_EDITOR_FORM_UI = jsonobj;

            console.log(_RECORD_EDITOR_FORM_UI);

            // make form
            $('#<?php echo $form_id ?>').jsonForm({
                schema: _RECORD_EDITOR_FORM_SCHEMA,
                value: _RECORD_EDITOR_FORM_DATA,
                form: _RECORD_EDITOR_FORM_UI
            });
        });

        modal.on('hide.bs.modal', function (e) {
            // clear the form
            $('#<?php echo $form_id ?>').empty();
        });

        modal.find("#save-button").on('click', function () {
            showPleaseWait();
            //
            let qs = serializeForm('#<?php echo $form_id ?>', true);
            let url = _RECORD_EDITOR_FORM_DATA + qs;
            //
            const successDialog = true;
            const reload = true;
            const funct = undefined;
            const silentMode = undefined;
            const suppressErrors = undefined;
            const errorFcn = undefined;
            const transportType = 'POST';
            callAPI(url, successDialog, reload, funct, silentMode, suppressErrors, errorFcn, transportType);
        });
    </script>
    <?php
}

?>

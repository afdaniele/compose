<!-- Smart Form Modal -->

<?php

require_once __DIR__ . '/../../utils/utils.php';

class SmartFormModal {
    
    public $formID;
    public $modalID;
    private $values;
    private $modalSize;
    private $schema;
    // events
    public $onSaveEvent;
    public $onCloseEvent;
    
    
    function __construct(&$schema, $values = [], $modalSize = 'lg', $formID = null) {
        // default values: formID
        $this->formID = $formID ?? generateRandomString(7);
        // arguments
        $this->values = $values;
        $this->modalSize = $modalSize;
        $this->schema = $schema;
        // other
        $this->modalID = sprintf("smart-form-modal-%s", $this->formID);
        $this->onSaveEvent = sprintf("COMPOSE-EVENT-SMART-FORM-ONSAVE-%s", $this->formID);
        $this->onCloseEvent = sprintf("COMPOSE-EVENT-SMART-FORM-ONCLOSE-%s", $this->formID);
    }
    
    public function render() {
        ?>
        <div class="modal fade" id="<?php echo $this->modalID ?>" tabindex="-1"
             role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-<?php echo $this->modalSize ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                    aria-hidden="true">&times;</span><span
                                    class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-mode" style="color: grey"></h4>
                        <h4 class="modal-title"></h4>
                    </div>

                    <div class="modal-body" id="modal-form-container" style="padding: 0 50px">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close
                        </button>
                        <button type="button" class="btn btn-success" id="save-button">Save
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $('#<?php echo $this->modalID ?>').on('show.bs.modal', function (e) {
                let formID = "<?php echo $this->formID ?>";
                let schema = <?php echo json_encode($this->schema) ?>;
                let values = <?php echo json_encode($this->values, JSON_FORCE_OBJECT) ?>;
                // two supported modes: insert, edit
                let mode = $(e.relatedTarget).data('modal-mode');
                let title = $(e.relatedTarget).data('modal-title');
                if (title == null) {
                    if (mode === 'edit') {
                        title = 'Edit record:';
                    } else {
                        title = 'Insert new record:';
                    }
                }
                $('#<?php echo $this->modalID ?> .modal-title').html(title);
                // ---
                // create form
                let form = new ComposeForm(null, schema, formID);
                form.render(
                    '#<?php echo $this->modalID ?> #modal-form-container',
                    values
                );
            });

            $('#<?php echo $this->modalID ?>').on('hide.bs.modal', function () {
                // trigger onClose event
                $(window).trigger("<?php echo $this->onCloseEvent ?>");
            });

            $('#<?php echo $this->modalID ?> #save-button').on('click', function () {
                // serialize form
                let form = ComposeForm.get('<?php echo $this->formID ?>');
                // trigger onSave event
                $(window).trigger("<?php echo $this->onSaveEvent ?>", form.serialize());
            });
        </script>
        <?php
    }
    
}//SmartFormModal
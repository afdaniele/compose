<!-- Smart Form -->

<?php

require_once __DIR__ . '/../../utils/utils.php';

use system\classes\Schema;
use system\classes\Utils;

class SmartForm {
    
    public $formID;
    private $values;
    private $schema;
    
    
    function __construct(&$schema, $values = [], $formID = null) {
        // default values: formID
        $this->formID = $formID ?? Utils::generateRandomString(7);
        // arguments
        $this->values = $values;
        $this->schema = ($schema instanceof Schema)? $schema->asArray() : $schema;
    }
    
    public function render() {
        ?>
        <div class="compose-smart-form" id="<?php echo $this->formID ?>">
            <!-- Compose Smart Form -->
        </div>

        <script type="text/javascript">
            $(document).ready(function(){
                let formID = "<?php echo $this->formID ?>";
                let schema = <?php echo json_encode($this->schema) ?>;
                let values = <?php echo json_encode($this->values, JSON_FORCE_OBJECT) ?>;
                // create form
                let form = new ComposeForm(null, schema, formID);
                form.render('#<?php echo $this->formID ?>', values);
            });
        </script>
        <?php
    }
    
}//SmartForm
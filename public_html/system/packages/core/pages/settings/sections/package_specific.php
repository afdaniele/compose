<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use system\classes\Core;

include_once __DIR__.'/utils/enum_fillers.php';


function settings_custom_package_tab( $args, $settings_tab_id ){
    $package_name = $args[0];
    $package_settings_res = $args[1];
    ?>
    <h5 style="font-weight:bold">
        <?php
        if( !$package_settings_res['success'] ){
            ?>
            <div class="alert alert-danger" role="alert">
                <span style="color:red">ERROR!</span>&nbsp;
                <?php echo $package_settings_res['data'] ?>
            </div>
            <?php
        }
        $package_settings = ($package_settings_res['success'])? $package_settings_res['data'] : null;
        if (is_null($package_settings)) {
            echo 'Generic Error.';
            return;
        }
        //
        if (!$package_settings->is_writable()) {
            ?>
            <div class="alert alert-warning" role="alert">
                <span class="glyphicon glyphicon-file" aria-hidden="true" style="color:#ff9818"></span>
                <span style="color:#ff9818">WARNING!</span>&nbsp;
                The server does not have the rights to edit the configuration file.
                Any change will be lost.
            </div>
            <?php
        }
        ?>
    </h5>

    <?php
    $config_schema = $package_settings->getSchema();
    $config_values = $package_settings->asArray();
    
    // fill in enums
    $w = function ($path, &$_, &$schema) use (&$config_schema) {
        $fcn_path = sprintf('.%s.%s', '__form__', 'enum_filler_fcn');
        $args_path = sprintf('.%s.%s', '__form__', 'enum_filler_args');
        if (!is_null($schema) && $schema->has($fcn_path)) {
            $fcn = $schema->get($fcn_path);
            $args = $schema->get($args_path);
            $entries = $fcn($args);
            if (!is_array($entries)) return;
            $values = [];
            $labels = [];
            array_map(function ($entry) use (&$values, &$labels) {
                array_push($values, $entry['value']);
                array_push($labels, $entry['label']);
            }, $entries);
            $values_path = sprintf('%s.%s', $path, 'values');
            $labels_path = sprintf('%s.%s.%s', $path, '__form__', 'labels');
            $config_schema->set($values_path, $values, true);
            $config_schema->set($labels_path, $labels, true);
        }
    };
    $config_schema->walk($config_values, $w);
    
    // create and render form from schema and values
    $form = new SmartForm($config_schema, $config_values);
    $form->render();
    ?>
    <br/>
    <button type="button" class="btn btn-success" id="<?php echo $package_name ?>-settings-save-button" style="float:right">
        <span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span>
        Save and Apply
    </button>
    
    <script type="text/javascript">
    	$('#<?php echo $package_name ?>-settings-save-button').on('click', function(){
    	    let form = ComposeForm.get("<?php echo $form->formID ?>");
    	    // hide changes mark on success
    	    let unsaved_mark_id = "#<?php echo $settings_tab_id ?>_unsaved_changes_mark";
    	    let succ_fcn = function(r){
                $(unsaved_mark_id).css('display', 'none');
            };
    	    // call API
    	    smartAPI('configuration', 'set', {
    	        method: 'POST',
                arguments: {
    	            package: "<?php echo $package_name ?>"
                },
                data: {
    	            configuration: form.serialize()
                },
                block: true,
                confirm: true,
                reload: true,
                on_success: succ_fcn
            });
    	});

        $(document).ready(function(){
            $('#<?php echo $form->formID ?> input').change(function(){
                let unsaved_mark_id = "#<?php echo $settings_tab_id ?>_unsaved_changes_mark";
                $(unsaved_mark_id).css('display', '');
            });
        });
    </script>

<?php
}
?>

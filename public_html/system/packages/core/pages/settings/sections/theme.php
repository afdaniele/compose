<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use exceptions\PackageNotFoundException;
use exceptions\ThemeNotFoundException;
use system\classes\Core;

require_once $GLOBALS['__SYSTEM__DIR__'] . 'templates/forms/SmartForm.php';


function settings_theme_tab($args, $settings_tab_id) {
    // get current theme
    $theme_parts = explode(':', Core::getSetting('theme', 'core', 'core:default'));
    $package = $theme_parts[0];
    $theme = $theme_parts[1];
    
    // get theme configuration schema
    try {
        $theme_schema = Core::getThemeConfigurationSchema($theme, $package);
    } catch (PackageNotFoundException $e) {
        Core::throwException($e);
        return;
    } catch (ThemeNotFoundException $e) {
        Core::throwException($e);
        return;
    }
    
    // not configurable themes
    if (count($theme_schema["properties"]) <= 0) {
        ?>
        <h4 class="text-center">(not configurable)</h4>
        <?php
        return;
    }
    
    // read theme configuration
    $res = Core::getThemeConfiguration($theme, $package);
    if (!$res['success']) {
        Core::throwError($res['data']);
        return;
    }
    $theme_cfg = $res['data'];

    // create form
    $form = new SmartForm($theme_schema, $theme_cfg);
    $form->render();
    ?>
    <button type="button" class="btn btn-success" id="theme-configuration-save-button" style="float:right">
        <span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span>Save and Apply
    </button>
    
    <script type="text/javascript">
    	$('#theme-configuration-save-button').on('click', function(){
    	    let form = ComposeForm.get("<?php echo $form->formID ?>");
    	    // hide changes mark on success
    	    let unsaved_mark_id = "#<?php echo $settings_tab_id ?>_unsaved_changes_mark";
    	    let succ_fcn = function(r){
                $(unsaved_mark_id).css('display', 'none');
            };
    	    // call API
    	    smartAPI('theme_configuration', 'set', {
    	        method: 'POST',
                arguments: {
    	            package: "<?php echo $package ?>",
    	            theme: "<?php echo $theme ?>"
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

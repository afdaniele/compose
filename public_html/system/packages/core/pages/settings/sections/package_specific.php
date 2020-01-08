<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

include_once __DIR__.'/utils/enum_fillers.php';

function create_row( $settings_entry_id, $settings_entry, $settings_entry_value ){
    ?>
    <div style="margin-bottom:4px">
        <label class="col-md-6 text-right"><?php echo $settings_entry['title'] ?></label>
        <p class="col-md-5" style="margin-bottom:20px">
            <?php
            switch( $settings_entry['type'] ) {
                case 'text':
                case 'email':
                    ?>
                    <input type="text"
                        name="<?php echo $settings_entry_id ?>"
                        style="width:100%"
                        value="<?php echo $settings_entry_value ?>"
                    >
                    <?php
                    break;
                    //
                case 'boolean':
                    ?>
                    <input type="checkbox"
                        data-toggle="toggle"
                        data-onstyle="primary"
                        data-class="fast"
                        data-size="mini"
                        name="<?php echo $settings_entry_id ?>"
                        <?php echo ($settings_entry_value)? 'checked' : '' ?>
                    >
                    <?php
                    break;
                    //
                case 'enum':
                    $enum_filler_fcn_name = $settings_entry['enum_filler_fcn_name'];
                    $enum_filler_fcn_args = $settings_entry['enum_filler_fcn_args'];
                    $enum_content = $enum_filler_fcn_name( $enum_filler_fcn_args );
                    ?>
                    <select name="<?php echo $settings_entry_id ?>" class="form-control">
                        <?php
                        $selected_found = False;
                        foreach ($enum_content as $c) {
                            if( $c['id'] == '_not_found' )
                                continue;
                            $current_is_selected = boolval( strcmp($settings_entry_value, $c['value']) == 0 );
                            echo sprintf(
                                '<option value="%s" %s>%s</option>',
                                $c['value'],
                                $current_is_selected ? 'selected' : '',
                                $c['label']
                            );
                            $selected_found = $selected_found || $current_is_selected;
                        }
                        if( !$selected_found ){
                            $not_found_entry = [];
                            foreach ($enum_content as $c) {
                                if( $c['id'] == '_not_found' )
                                    $not_found_entry = $c;
                            }
                            ?>
                            <option value="<?php echo $not_found_entry['value'] ?>" selected>
                                <?php echo $not_found_entry['label'] ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                    <?php
                    break;
                default:
                    echo sprintf("ERROR, unknown type (%s)", $settings_entry['type']);
                    break;
            }
            ?>
        </p>
        <p class="col-md-1" style="margin-top:6px">
          <span
            class="glyphicon glyphicon-info-sign"
            style="color: #2e6da4; font-size: 12pt"
            aria-hidden="true"
            data-toggle="popover"
            data-trigger="focus"
            data-container="body"
            data-placement="bottom"
            tabindex="0"
            title="Info"
            data-content="<?php echo $settings_entry['details'] ?>"
            ></span>
        </p>
    </div>
    <?php
}


function settings_custom_package_tab( $package_settings, $settings_tab_id ){
    $package_name = $package_settings[0];
    $package_setts_res = $package_settings[1];
    ?>
    <h5 style="font-weight:bold">
        <?php
        if( !$package_setts_res['success'] ){
            ?>
            <div class="alert alert-danger" role="alert">
                <span style="color:red">ERROR!</span>&nbsp;
                <?php echo $package_setts_res['data'] ?>
            </div>
            <?php
        }
        //
        if( $package_setts_res['success'] && !$package_setts_res['data']->is_writable() ){
            ?>
            <div class="alert alert-warning" role="alert">
                <span class="glyphicon glyphicon-file" aria-hidden="true" style="color:#ff9818"></span>
                <span style="color:#ff9818">WARNING!</span>&nbsp;
                The server does not have the rights to edit the configuration file. Any change will be lost.
            </div>
            <?php
        }
        ?>
    </h5>


    <form id="<?php echo $package_name ?>-settings-form">

        <input type="text" name="package" style="display:none" value="<?php echo $package_name ?>">

        <table style="width:100%; margin-top:20px">
            <tr>
                <td>
                    <div style="width:700px; margin:auto">

                        <?php
                        if( $package_setts_res['success'] ){
                            $settings_values = $package_setts_res['data']->asArray();
                            $metadata = $package_setts_res['data']->getMetadata();
                            foreach ($metadata['configuration_content'] as $settings_entry_id => $settings_entry) {
                                $settings_entry_value =
                                    array_key_exists($settings_entry_id, $settings_values)?
                                    $settings_values[$settings_entry_id] :
                                    ( is_null($settings_entry['default'])? '' : $settings_entry['default'] );
                                create_row( $settings_entry_id, $settings_entry, $settings_entry_value );
                            }
                        }
                        ?>

                    </div>

                </td>
            </tr>

            <tr>
                <td>
                    <button type="button" class="btn btn-success" id="<?php echo $package_name ?>-settings-save-button" style="float:right">
                        <span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span>
                        &nbsp;
                        Save and Apply
                    </button>
                </td>
            </tr>
        </table>

    </form>


    <script type="text/javascript">
    	$('#<?php echo $package_name ?>-settings-save-button').on('click', function(){
    		qs = serializeForm( '#<?php echo $package_name ?>-settings-form' );
    		//
    		url = "<?php echo \system\classes\Configuration::$BASE ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/configuration/set/json?"+qs+"&token=<?php echo $_SESSION["TOKEN"] ?>";
            unsaved_mark_id = "#<?php echo $settings_tab_id ?>_unsaved_changes_mark";
            succ_fcn = function(r){
                $(unsaved_mark_id).css('display', 'none');
            }
            //
    		callAPI(url, true, true, succ_fcn);
    	});

        $('#<?php echo $package_name ?>-settings-form :input').change(function(){
            unsaved_mark_id = "#<?php echo $settings_tab_id ?>_unsaved_changes_mark";
            $(unsaved_mark_id).css('display', '');
    	});
    </script>

<?php
}
?>

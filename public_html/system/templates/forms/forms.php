<?php

function generateView($labelName, $fieldValue, $labelCol, $valueCol) {
    echo '<table style="width:100%"><tr><td>';
    for ($i = 0; $i < sizeof($labelName); $i++) {
    	?>
		<div style="margin-bottom:4px">
			<label class="col-<?php echo (is_array($labelCol)) ? $labelCol[$i] : $labelCol ?> text-right">
				<?php echo $labelName[$i] ?>
			</label>
			<p class="col-<?php echo (is_array($valueCol)) ? $valueCol[$i] : $valueCol ?>">
				<?php echo (strlen($fieldValue[$i]) > 0) ? $fieldValue[$i] : '&nbsp;' ?>
			</p>
		</div>
		<?php
    }
    echo '</td></tr></table>';
}//generateView


/**
 * @param $layout
 *  a dictionary of the form
 *      {
 *          'key' => {
 *              'name': str,
 *              'editable': bool,
 *              'hidden': bool,
 *              'type': one-of['password', 'email', 'enum', 'select', 'boolean', 'numeric'],
 *              'placeholder':
 *                  // if type == one-of['password', 'email', 'numeric']
 *                  a string containing the placeholder text in the textbox
 *
 *                  // if type == one-of['enum', 'select']
 *                  a list of strings with the labels for a dropdown selector
 *              ,
 *              'placeholder_id': a list of IDs for the placeholder labels in 'placeholder',
 *              'value': value of the key, used to make checkboxes selected,
 *              'html': a string containing HTML DOM attributes to add to the HTML input object
 *          },
 *          ...
 *      }
 *
 * @param null $formID
 *  the ID of the HTML form
 *
 * @param null $formClass
 *  custom HTML class for the form object
 *
 * @param null $method
 *  HTTP verb to be used in this form
 *
 * @param null $action
 *  destination of the form
 *
 * @param array $values
 *  a dictionary of the form
 *      {
 *          'key' => [
 *              'value1',
 *              'value2',
 *              ...
 *          ],
 *          ...
 *      }
 *
 *  containing the list of possible values for each field in 'layout'.
 *
 */
function generateFormByLayout(&$layout, $formID = null, $formClass = null, $method = null, $action = null, &$values = array()) {
    $formTag = ($formID != null || $formClass != null || ($method != null && $action != null));
    //
    if ($formTag) {
        printf(
            '<form class="form-horizontal %s" role="form" method="%s" %s %s >',
            ($formClass != null) ? $formClass : '',
            $method,
            ($formID != null) ? 'id="' . $formID . '"' : '',
            ($action != null) ? 'action="' . $action . '"' : ''
        );
    }
    // =>
    foreach ($layout as $key => $field) {
        ?>
        <div class="form-group"
             style="margin: 10px 0; <?php echo $field['hidden'] ? 'display:none' : '' ?>">
            <label class="col-md-5 control-label"><?php echo $field['name'] ?></label>
            <div class="col-md-7" style="padding-top:7px">
                <?php
                if ($field['editable']) {
                    $type = null;
                    $object = null;
                    //
                    switch ($field['type']) {
                        case 'password':
                            $object = 'input';
                            $type = 'password';
                            break;
                        case 'email':
                            $object = 'input';
                            $type = 'email';
                            break;
                        case 'enum':
                        case 'select':
                            $object = 'select';
                            break;
                        case 'boolean':
                            $object = 'checkbox';
                            break;
                        case 'numeric':
                            $object = 'input';
                            $type = 'number';
                            break;
                        default:
                            $object = 'input';
                            $type = 'text';
                            break;
                    }
                    //
                    $disabled = ((booleanval($values['_lock_' . $key])) ? ' disabled' : '');
                    //
                    switch ($object) {
                        case 'input':
                            printf(
                                '<input type="%s" class="form-control" id="" name="%s" placeholder="%s" value="%s" %s %s>',
                                $type, $key, $field['placeholder'], $values[$key], $field['html'], $disabled
                            );
                            //
                            break;
                        case 'select':
                            printf(
                                '<select type="select" class="form-control" id="%s" name="%s" %s %s>',
                                $key, $key, $field['html'], $disabled
                            );
                            $options_ids = (isset($field['placeholder_id']) && count($field['placeholder']) == count($field['placeholder_id'])) ? $field['placeholder_id'] : $field['placeholder'];
                            for ($i = 0; $i < count($field['placeholder']); $i++) {
                                $value = $field['placeholder'][$i];
                                $id = $options_ids[$i];
                                $selected = ((isset($field['value']) && $values[$key] == $id) ? 'selected' : '');
                                //
                                printf('<option value="%s" %s>%s</option>', $id, $selected, $value);
                            }
                            echo '</select>';
                            //
                            break;
                        case 'checkbox':
                            printf('<input type="checkbox"
                                data-toggle="toggle"
                                data-onstyle="primary"
                                data-class="fast"
                                data-size="mini"
                                style="margin-top:7px"
                                id="%s"
                                name="%s"
                                %s
                                %s
                                %s >',
                                $key,
                                $key,
                                (boolval($values[$key]) ? 'checked' : ''),
                                $field['html'],
                                $disabled
                            );
                            //
                            break;
                        default:
                            break;
                    }
                } else {
                    printf(
                        '<p class="form-control-static" id="%s_p" style="padding-top:0">%s</p>',
                        $key, $values[$key]
                    );
                    printf('<input type="hidden" id="%s" name="%s">', $key, $key);
                }
                ?>
            </div>
        </div>
        <?php
    }
    printf('<input type="hidden" name="token" value="%s">', $_SESSION['TOKEN']);
    // <=
    echo ($formTag) ? '</form>' : '';
}//generateFormByLayout

?>

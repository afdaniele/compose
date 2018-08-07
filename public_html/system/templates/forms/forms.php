<?php

function generateView( $labelName, $fieldValue, $labelCol, $valueCol ){
	echo '<table style="width:100%"><tr><td>';
	for( $i = 0; $i < sizeof( $labelName ); $i++ ){

		echo '<div style="margin-bottom:4px">
				<label class="col-'. ( (is_array($labelCol))? $labelCol[$i] : $labelCol ) .' text-right">'.$labelName[$i].'</label>
				<p class="col-'. ( (is_array($valueCol))? $valueCol[$i] : $valueCol ) .'">'.( (strlen($fieldValue[$i]) > 0)? $fieldValue[$i] : '&nbsp;' ).'</p>
			</div>';

	}
	echo '</td></tr></table>';
}//generateView


function generateForm( $method='post', $action=null, $formID=null, $labelName, $inputName, $inputPlaceholder, $inputValue, $inputType, $inputEditable, $labelCol, $inputCol, $inputError=null, $inputExtraParameters=null, $inputAddOn=null ){

	echo ($formID !== null) ? '<form class="form-horizontal" ' . ( ($formID != null)? 'id="'.$formID.'"' : '' ) . ' role="form" method="'.$method.'" ' . ( ($action != null)? 'action="'.$action.'"' : '' ) . '>' : '';

	//

	for( $i = 0; $i < sizeof( $labelName ); $i++ ){

		echo '<div class="form-group" style="margin-bottom:4px">
				<label class="col-'. ( (is_array($labelCol))? $labelCol[$i] : $labelCol ) .' control-label">'.$labelName[$i].'</label>
				<div class="col-'. ( (is_array($inputCol))? $inputCol[$i] : $inputCol ) .'">';

		if( $inputEditable === true || ( is_array($inputEditable) && $inputEditable[$i]===true ) ){
			// Modifiable input
			$has_error = ( ($inputError[$i] !== null) ? $inputError[$i] : '' );
			$extra = ( ($inputExtraParameters == null)? '' : ( ($inputExtraParameters[$i] == null)? '' : $inputExtraParameters[$i] ) );
			$type = 'text';
			if( $inputType[$i] == 'password' ){
				$type = 'password';
				$inputType[$i] = 'text';
			}
			//
			echo '<div class="input-group" style="width:100%">';
			switch( $inputType[$i] ){
				case 'text':
				case 'number':
					$value = ( ($inputValue[$i] !== null ) ? 'value="'.$inputValue[$i].'"' : '' );
					echo '<input type="'.$type.'" class="form-control '.$has_error.'" id="'.$inputName[$i].'" name="'.$inputName[$i].'" placeholder="'.$inputPlaceholder[$i].'" '.$value.' '.$extra.'>';
					break;
				case 'select':
					echo '<select class="form-control '.$has_error.'" id="'.$formID.'-'.$inputName[$i].'" name="'.$inputName[$i].'" '.$extra.'>';
					foreach( $inputPlaceholder[$i] as $val ){
						$select = ( ($val == $inputValue[$i])? 'selected' : '' );
						echo '<option '.$select.'>'.$val.'</option>';
					}
					echo '</select>';
				default:break;
			}
			$addOn = ( ($inputAddOn == null)? false : ( ($inputAddOn[$i] == null)? false : $inputAddOn[$i] ) );
			if( $addOn !== false ){
				echo '<span class="input-group-addon" id="basic-addon2" style="padding-right:20px">'.$addOn.'</span>';
			}
			echo '</div>';
		}else{
			// Static input
			echo '<p class="form-control-static" id="'.$inputName[$i].'_p">'.$inputValue[$i].'</p>';
			echo '<input type="hidden" id="'.$inputName[$i].'_i" name="'.$inputName[$i].'">';
		}

		echo '</div>
			</div>';

	}
	//
	echo ( ($formID !== null) ? '</form>' : '' );
}


// $layout is a dictionary { k => val, ... }, with val.keys() = [name, editable, type, placeholder, value, html, hidden]
function generateFormByLayout( &$layout, $formID=null, $formClass=null, $method=null, $action=null, &$values=array() ){
	$formTag = ( $formID != null || $formClass != null || ( $method != null && $action != null ) );
	//
	echo ($formTag)? '<form class="form-horizontal '. ( ($formClass != null)? $formClass : '' ) .'" ' . ( ($formID != null)? 'id="'.$formID.'"' : '' ) . ' role="form" method="'.$method.'" ' . ( ($action != null)? 'action="'.$action.'"' : '' ) . '>' : '';
	// =>
	foreach( $layout as $key => $field ){
		echo '<div class="form-group" style="margin: 10px 0; '.($field['hidden']? 'display:none' : '').'">';
		echo '<label class="col-md-5 control-label">'.$field['name'].'</label>';
		echo '<div class="col-md-7" style="padding-top:7px">';
		//
		if( $field['editable'] ){
			$type = null;
			$object = null;
			//
			switch( $field['type'] ){
				case 'password':
					$object = 'input';
					$type = 'password';
					break;
				case 'email':
					$object = 'input';
					$type = 'email';
					break;
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
			$disabled = ( ( booleanval($values['_lock_'.$key]) )? ' disabled' : '' );
			//
			switch( $object ){
				case 'input':
					echo '<input type="'.$type.'" class="form-control" id="'.$key.'" name="'.$key.'" placeholder="'.$field['placeholder'].'" value="'.$values[$key].'" '.$field['html'].$disabled.'>';
					//
					break;
				case 'select':
					echo '<select type="select" class="form-control" id="'.$key.'" name="'.$key.'" '.$field['html'].$disabled.'>';
					foreach( $field['placeholder'] as $value ){
						$selected = ( (isset($field['value']) && $values[$key] == $value)? 'selected' : '' );
						echo '<option '.$selected.'>'.$value.'</option>';
					}
					echo '</select>';
					//
					break;
				case 'checkbox':
					echo sprintf('<input type="checkbox"
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
						( boolval($values[$key])? 'checked' : '' ),
						$field['html'],
						$disabled
				 	);
					//
					break;
				default:break;
			}
		}else{
			echo '<p class="form-control-static" id="'.$key.'_p" style="padding-top:0">'.$values[$key].'</p>';
			echo '<input type="hidden" id="'.$key.'" name="'.$key.'">';
		}
		//
		echo '</div>';
		echo '</div>';
	}
	echo '<input type="hidden" name="token" value="'.$_SESSION['TOKEN'].'">';
	// <=
	echo ($formTag)? '</form>' : '';
}//generateFormByLayout

?>

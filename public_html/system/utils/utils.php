<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 15th 2018




function validate( $values, $types, $mandatory=null, $keys=null ){
	$result = array();
	//
	if( $mandatory !== null ){
		foreach( $mandatory as $key ){
			if( !isset( $values[$key] ) || $values[$key] == '' ){
				$result[$key] = "Mandatory field";
			}
		}
	}
	$keys = ($keys == null)? $mandatory : $keys;
	if( $keys == null || !is_assoc($values) ){
		$m = min( sizeof($values), sizeof($types) );
		for( $i = 0; $i < $m; $i++ ){
			if( !\system\classes\enum\StringType::isValid($values[$i], \system\classes\enum\StringType::byName($types[$i])) ){
				$key = ( $keys != null )? $keys[$i] : $i;
				$result[$key] = \system\classes\enum\StringType::getDescription( $types[$i] );
			}
		}
	}else{
		// associative array
		$i = 0;
		foreach( $keys as $key ){
			$type = ( (is_assoc($types))? $types[$key] : $types[$i] );
			if( !\system\classes\enum\StringType::isValid($values[$key], \system\classes\enum\StringType::byName($type)) ){
				$result[$key] = \system\classes\enum\StringType::getDescription( $type );
			}
			$i++;
		}
	}
	//
	if( sizeof($result) > 0 ){
		return $result;
	}else{
		return true;
	}
}//validate


function toQueryString( $array, $get, $questionMarkAppend=false, $ampAppend=false ){
	$queryString = '';
	foreach( $array as $param ){
		$queryString = $queryString . ( (isset($get[$param]) && strlen($get[$param])>0 )? ( (strlen($queryString)>0)? '&' : '' ).$param.'='.$get[$param] : '' );
	}
	//
	if( $questionMarkAppend ){
		$queryString = ( (strlen($queryString) > 0)? '?'.$queryString : $queryString );
	}
	if( $ampAppend ){
		$queryString = ( (strlen($queryString) > 0)? $queryString.'&' : ( ($questionMarkAppend)? '?' : '' ) );
	}
	//
	return $queryString;
}//toQueryString


function generateRandomString( $length, $set='alphanumeric' ) {
	switch( $set ){
		case 'alphanumeric':
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			break;
		case 'alphabetic':
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case 'numeric':
			$chars = '0123456789';
			break;
		default: // alphanumeric
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			break;
	}
	$count = mb_strlen($chars);
	//
	for ($i = 0, $result = ''; $i < $length; $i++) {
		$index = rand(0, $count - 1);
		$result .= mb_substr($chars, $index, 1);
	}
	return $result;
}//generateRandomString


function toMoneyString( $value, $currencyAppend = false ){
	if( is_string($value) ) $value = floatval( $value );
	//
	return number_format( $value, 2, '.', '') . ( ($currencyAppend)? ' €' : '' );
}//toMoneyString


function is_assoc($array) {
	return (bool)count(array_filter(array_keys($array), 'is_string'));
}//is_assoc


function convertBytesToHumanReadableString($size){
	$unit=array('Bytes','KB','MB','GB','TB','PB');
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}//convertBytesToHumanReadableString


function echoArray( $array ){
	echo '<pre>'.print_r($array,true).'</pre>';
}//echoArray


function format( $val, $type ){
	switch( $type ){
		case 'alpha':
		case 'alphabetic':
		case 'alphaspace':
		case 'alphanumeric':
		case 'alphanumericspace':
		case 'password':
		case 'text':
		case 'email':
		case 'version':
		case 'numeric':
			return $val.'';
		case 'key':
			return 'ID:&nbsp;'.$val;
		case 'float':
			return number_format( $val, 2, '.', '');
		case 'money':
			return toMoneyString( $val, true );
		case 'boolean':
			return ( (booleanval($val))? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true" style="color:green" data-toggle="tooltip" data-placement="right" title="On"></span>' : '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color:red" data-toggle="tooltip" data-placement="right" title="Off"></span>' );
		case 'date':
			return date_format(date_create($val), 'd-m-Y');
		case 'datetime':
			return date_format(date_create($val), 'd-m-Y H:i');
		case 'distance':
			return ( ($val >= 1000)? number_format($val/1000, 1, ',', '') : number_format($val, 0, '', '') ) . ( ($val >= 1000)? ' Km' : ' m' );
		case 'color':
		case 'colour':
			return ( ($val == null)? '<span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-bookmark" aria-hidden="true" style="color:'.$val.'"></span>' );
		case 'percentage':
			return $val.' %';
		case 'message-status':
			return ( (booleanval($val))? '<span class="glyphicon glyphicon-eye-open" aria-hidden="true" style="color:#626262; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Read"></span>' : '<span class="glyphicon glyphicon-fire" aria-hidden="true" style="color:#ff9818; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Unread"></span>' );
			break;
		case 'placeholder':
			return '<div id="_format_placeholder_'.$val.'"><img src="'.\system\classes\Configuration::$BASE_URL.'images/loading_blue.gif" style="width:22px; height:22px;"></div>';
			break;
		case 'avatar_image_small':
			return sprintf('<img src="%s" class="formatted-avatar formatted-avatar-small">', $val);
			break;
		case 'avatar_image':
			return sprintf('<img src="%s" class="formatted-avatar">', $val);
			break;
		default:
			return $val.'';
	}
}//format

function array_assoc_filter( &$array, $keys, $copy=false ){
	if( !is_array($array) || !is_assoc($array) || !is_array($keys) ) return false;
	if( is_assoc($keys) ) $keys = array_keys($keys);
	//
	$result = ( ( $copy )? $array : null );
	//
	foreach( $array as $key => $val ){
		if( !in_array( $key, $keys ) ){
			if( $copy ){
				unset( $result[$key] );
			}else{
				unset( $array[$key] );
			}
		}
	}
	//
	return ( ($copy)? $result : true );
}//array_assoc_filter

function secsToMMSS( $seconds ){
	$mins = floor($seconds / 60);
	$secs = ($seconds - ($mins*60));
	//
	return date( 'i:s', mktime(0, $mins, $secs, 0, 0) );
}

function secsToHHMM( $seconds ){
	$hours = floor($seconds / 3600);
	$mins = floor(($seconds - ($hours*3600)) / 60);
	//
	return date( 'H:i', mktime($hours, $mins, 0, 0, 0) );
}

function secsToHHMMss( $seconds ){
	$hours = floor($seconds / 3600);
	$mins = floor(($seconds - ($hours*3600)) / 60);
	$secs = floor($seconds % 60);
	//
	return date( 'H:i:s', mktime($hours, $mins, $secs, 0, 0) );
}

function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

?>

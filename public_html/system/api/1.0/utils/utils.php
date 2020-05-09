<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use system\classes\enum\StringType;


function _bad_request($msg) {
	return ['code' => 400, 'status' => 'Bad Request', 'message' => $msg];
}//_bad_request


function _illegal_arg(&$name, &$type, &$value, $ns='') {
	$given_type = (is_assoc($value))? "object" : gettype($value);
	$msg = sprintf(
		"The value of the parameter '%s%s' is not valid. Expected type '%s', got '%s' instead",
		$ns, $name, $type, $given_type
	);
	return _bad_request($msg);
}//_illegal_arg


function prepareArguments(&$arguments, &$details) {
	foreach ($arguments as $key => &$value) {
		if (array_key_exists($key, $details)) {
			$type = $details[$key]['type'];
			// base case: primitive value argument
			prepareArgument($type, $value);
			// handle objects recursively
			if ($type == 'object'){
				prepareArguments($value, $details[$key]['_data']);
			}
			// handle arrays recursively
			if ($type == 'array') {
				$sample = $details[$key]['_data'][0];
				foreach ($value as &$v) {
					prepareArgument($sample['type'], $v);
				}
			}
		}
	}
}//prepareArguments


function prepareArgument(&$type, &$value) {
	// fix array type with zero or one element
	if ($type == 'array' && !is_array($value)) {
		$value = (strlen($value) > 0) ? [$value] : [];
	}
	// fix associative array with zero elements
	if ($type == 'object' && !is_array($value)) {
		$value = [];
	}
	// convert null strings to null value
	if ($value == 'null'){
		$value = null;
	}
	// convert boolean values
	if ($type == 'boolean' && is_string($value)){
		$value = booleanval($value);
	}
}//prepareArgument


function checkArgument(&$name, &$array, &$details, &$res, $mandatory=true, $ns=''){
	if (!array_key_exists($name, $array)){
		if($mandatory){
			$res = _bad_request(sprintf("The parameter '%s%s' is mandatory", $ns, $name));
			return false;
		}else{
			return true;
		}
	}
	if (!$mandatory && $array[$name] == ''){
		//exclude it
		unset($array[$name]);
		return true;
	}
	// get argument type and value
	$type = $details['type'];
	$value = &$array[$name];
	// check nullable parameters
	if (isset($details['nullable']) && $details['nullable'] && is_null($value)) {
		return true;
	}
	// check boolean values
	if ($type == 'boolean' && is_bool($value)){
		return true;
	}
	// check argument length
	$length = ((isset($details['length']) && $details['length'] !== null)? $details['length'] : false);
	if(!($length === false) && strlen($value) !== $length){
		$res = _bad_request(sprintf(
			"The value of parameter '%s%s' must be exactly %s, got %s instead.",
			$ns, $name, $length, strlen($value)
		));
		return false;
	}
	//
	if($type == 'enum'){
		$enum = $details['values'];
		if(!in_array($value, $enum)){
			$res = _bad_request(sprintf(
				"Illegal value for parameter '%s%s'. Allowed values are %s",
				$ns, $name, sprintf("['%s']", implode("', '", $enum))
			));
			return false;
		}
	}elseif($type == 'object'){
		// check type of array, we are expecting an associative array (aka object)
		if (!is_assoc($value) && count($value) > 0) {
			$res = _illegal_arg($name, $type, $value, $ns);
			return false;
		}
		// check object content recursively
		$sample = $details['_data'];
		foreach ($sample as $cont_key => &$cont_val){
			$nns = "{$ns}{$name}.";
			if (checkArgument($cont_key, $value, $cont_val, $res, true, $nns) === false){
				return false;
			}
		}
	}elseif($type == 'array' && isset($details['values'])){
		$allowed_values = $details['values'];
		foreach ($value as $v){
			if(!in_array($v, $allowed_values)){
				$res = _bad_request(sprintf(
					"Illegal value for parameter '%s%s'. Allowed values are %s",
					$ns, $name, sprintf("['%s']", implode("', '", $allowed_values))
				));
				return false;
			}
		}
	}elseif($type == 'array'){
		// check list content recursively
		$sample = $details['_data'][0];
		$obj_array = array_values($value);
		foreach (array_keys($obj_array) as $k){
			$nns = "{$ns}{$name}.";
			if (checkArgument($k, $obj_array, $sample, $res, true, $nns) === false){
				return false;
			}
		}
	}else{
		if(!StringType::isValid($value, StringType::getRegexByTypeName($type))){
			$res = _bad_request(sprintf("Illegal value for parameter '%s%s'.", $ns, $name));
			return false;
		}
	}
	//
	if(isset($details['domain']) && is_array($details['domain']) && sizeof($details['domain']) == 2){
		$domain = $details['domain'];
		if($value < floatval($domain[0]) || $value > floatval($domain[1])){
			$res = _bad_request(sprintf(
				"Illegal value for parameter '%s%s'. Allowed values are [%s,%s]",
				$ns, $name, $domain[0], $domain[1]
			));
			return false;
		}
	}
	//
	return true;
}//checkArgument

function pruneResult(&$records, &$details){
	if(!is_array($details)) return;
	//
	if(is_assoc($records)){
		// one record
		foreach($records as $key => $value){
			if(!array_key_exists($key, (isset($details['_data'])? $details['_data'] : $details))) unset($records[$key]);
		}
	}else{
		// array of records
		for($i = 0; $i < sizeof($records); $i++){
			foreach($records[$i] as $key => $value){
				if(!array_key_exists($key, (isset($details['_data'])? $details['_data'] : $details))) unset($records[$i][$key]);
			}
		}
	}
}//pruneResult

function formatResult(&$results, &$details){
	if($results == null || $details == null) return;
	if(!is_array($details)) return;
	//
	if(is_assoc($results)){
		// associative array
		foreach($details as $key => $value){
			if(!isset($results[$key])) continue;
			$type = (isset($details[$key]['type']))? $details[$key]['type'] : 'text';
			if($type == 'text') continue;
			//
			switch($type){
				case 'numeric':
					$results[$key] = intval($results[$key]);
					break;
				case 'float':
					$results[$key] = floatval($results[$key]);
					break;
				case 'boolean':
					$results[$key] = booleanval($results[$key]);
					break;
				case 'array':
					formatResult($results[$key], $details[$key]['_data'][0]);
					break;
				case 'object':
					formatResult($results[$key], $details[$key]['_data']);
					break;
				default:
					break;
			}
		}
	}else{
		// positional array
		$type = (isset($details['type']))? $details['type'] : 'text';
		//
		for($i = 0; $i < sizeof($results); $i++){
			switch($type){
				case 'numeric':
					$results[$i] = intval($results[$i]);
					break;
				case 'float':
					$results[$i] = floatval($results[$i]);
					break;
				case 'boolean':
					$results[$i] = booleanval($results[$i]);
					break;
				case 'array':
					formatResult($results[$i], $details['_data'][0]);
					break;
				case 'object':
					formatResult($results[$i], $details['_data']);
					break;
				default:
					break;
			}
		}
	}
}//formatResult

function prepareResult(&$res, &$action, $prune=true){
	if(!$res['success']) return array('code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data']);
	//
	if(isset($res['size']) && $res['size']==0) return array('code' => 204, 'status' => 'No Content', 'message' => 'No results found');
	//
	if($prune){
		pruneResult($res['data'], $action);
	}
	//
	return true;
}//prepareResult

function getArgument(&$arguments, $name){
	return ((isset($arguments[$name]))? $arguments[$name] : null);
}//getArgument




function _createResponseArray($code, $status, $message, $data){
	return array(
		'code' => $code,
		'status' => $status,
		'message' => $message,
		'data' => $data
	);
}//_createResponseArray

function response200OK($data=null){
	return _createResponseArray(200, 'OK', null, $data);
}//response200OK

function response401Unauthorized(){
	return _createResponseArray(401, 'Unauthorized', 'Unauthorized', null);
}//response401Unauthorized

function response401UnauthorizedMsg($message){
	return _createResponseArray(401, 'Unauthorized', $message, null);
}//response401UnauthorizedMsg

function response400BadRequest($message){
	return _createResponseArray(400, 'Bad Request', $message, null);
}//response400BadRequest

function response412PreconditionFailed($message){
	return _createResponseArray(412, 'Precondition Failed', $message, null);
}//response412PreconditionFailed

function response404NotFound($message){
	return _createResponseArray(404, 'Not Found', $message, null);
}//response404NotFound

function response500InternalServerError($message){
	return _createResponseArray(500, 'Internal Server Error', $message, null);
}//response500InternalServerError

?>

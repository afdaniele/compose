<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Tuesday, January 9th 2018



require_once __DIR__.'/../../../classes/enum/StringType.php';


function checkArgument( &$name, &$array, &$details, &$res, $mandatory=true ){
	if( !isset($array[$name]) ){
		if( $mandatory ){
			$param_desc = ( (isset(\system\classes\Configuration::$TRAN[$name]))? '\''.$name.'\'' . ' (' .\system\classes\Configuration::$TRAN[$name]. ')' : '\''.$name.'\'' );
			$res = array( 'code' => 400, 'status' => 'Bad Request', 'message' => "The parameter ".$param_desc." is mandatory" );
			return false;
		}else{
			return true;
		}
	}
	if( !$mandatory && $array[$name] == '' ){
		//exclude it
		unset( $array[$name] );
		return true;
	}
	//
	$type = $details['type'];
	$length = ((isset($details['length']) && $details['length'] !== null)? $details['length'] : false);
	//
	if( !($length === false) && strlen($array[$name]) !== $length ){
		$param_desc = ( (isset(\system\classes\Configuration::$TRAN[$name]))? '\''.$name.'\'' . ' (' .\system\classes\Configuration::$TRAN[$name]. ')' : '\''.$name.'\'' );
		$res = array( 'code' => 400, 'status' => 'Bad Request', 'message' => "The value of the ".$param_desc." parameter is not valid" );
		return false;
	}
	//
	if( $type == 'enum' ){
		$enum = $details['values'];
		if( !in_array( $array[$name], $enum ) ){
			$param_desc = ( (isset(\system\classes\Configuration::$TRAN[$name]))? '\''.$name.'\'' . ' (' .\system\classes\Configuration::$TRAN[$name]. ')' : '\''.$name.'\'' );
			$res = array( 'code' => 400, 'status' => 'Bad Request', 'message' => "Illegal value for the ".$param_desc." parameter. Allowed values are ['".implode('\', \'', $enum)."']" );
			return false;
		}
	}else{
		if( !\system\classes\enum\StringType::isValid($array[$name], \system\classes\enum\StringType::byName($type) ) ){
			$param_desc = ( (isset(\system\classes\Configuration::$TRAN[$name]))? '\''.$name.'\'' . ' (' .\system\classes\Configuration::$TRAN[$name]. ')' : '\''.$name.'\'' );
			$res = array( 'code' => 400, 'status' => 'Bad Request', 'message' => "The value of the ".$param_desc." parameter is not valid" );
			return false;
		}
	}
	//
	if( isset($details['domain']) && is_array($details['domain']) && sizeof($details['domain']) == 2 ){
		$domain = $details['domain'];
		if( $array[$name] < floatval($domain[0]) || $array[$name] > floatval($domain[1]) ){
			$param_desc = ( (isset(\system\classes\Configuration::$TRAN[$name]))? '\''.$name.'\'' . ' (' .\system\classes\Configuration::$TRAN[$name]. ')' : '\''.$name.'\'' );
			$res = array( 'code' => 400, 'status' => 'Bad Request', 'message' => "Illegal value for the ".$param_desc." parameter. Allowed values are [{$domain[0]},{$domain[1]}]" );
			return false;
		}
	}
	//
	return true;
}//checkArgument


function pruneResult( &$records, &$details ){
	if( !is_array($details) ) return;
	//
	if( is_assoc($records) ){
		// one record
		foreach( $records as $key => $value ){
			if( !array_key_exists($key, (isset($details['_data'])? $details['_data'] : $details) ) ) unset( $records[$key] );
		}
	}else{
		// array of records
		for( $i = 0; $i < sizeof($records); $i++ ){
			foreach( $records[$i] as $key => $value ){
				if( !array_key_exists($key, (isset($details['_data'])? $details['_data'] : $details) ) ) unset( $records[$i][$key] );
			}
		}
	}
}//pruneResult

function formatResult( &$results, &$details ){
	if( $results == null || $details == null ) return;
	if( !is_array($details) ) return;
	//
	if( is_assoc($results) ){
		// associative array
		foreach( $details as $key => $value ){
			if( !isset($results[$key]) ) continue;
			$type = (isset($details[$key]['type']))? $details[$key]['type'] : 'text';
			if( $type == 'text' ) continue;
			//
			switch( $type ){
				case 'numeric':
					$results[$key] = intval( $results[$key] );
					break;
				case 'float':
					$results[$key] = floatval( $results[$key] );
					break;
				case 'boolean':
					$results[$key] = booleanval( $results[$key] );
					break;
				case 'array':
					formatResult( $results[$key], $details[$key]['_data'][0] );
					break;
				case 'object':
					formatResult( $results[$key], $details[$key]['_data'] );
					break;
				default:
					break;
			}
		}
	}else{
		// positional array
		$type = (isset($details['type']))? $details['type'] : 'text';
		//
		for( $i = 0; $i < sizeof($results); $i++ ){
			switch( $type ){
				case 'numeric':
					$results[$i] = intval( $results[$i] );
					break;
				case 'float':
					$results[$i] = floatval( $results[$i] );
					break;
				case 'boolean':
					$results[$i] = booleanval( $results[$i] );
					break;
				case 'array':
					formatResult( $results[$i], $details['_data'][0] );
					break;
				case 'object':
					formatResult( $results[$i], $details['_data'] );
					break;
				default:
					break;
			}
		}
	}
}//formatResult

function prepareResult( &$res, &$action, $prune=true ){
	if( !$res['success'] ) return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
	//
	if( isset($res['size']) && $res['size']==0 ) return array( 'code' => 204, 'status' => 'No Content', 'message' => 'No results found' );
	//
	if( $prune ){
		pruneResult( $res['data'], $action );
	}
	//
	return true;
}//prepareResult

function getArgument( &$arguments, $name ){
	return ( (isset($arguments[$name]))? $arguments[$name] : null );
}//getArgument

function filelog( &$string ){
	file_put_contents( __DIR__.'/../../log/logger.log', $string /*, FILE_APPEND | LOCK_EX */ );
}//log

?>

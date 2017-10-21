<?php

function booleanval( $something ){
	if( is_bool($something) ) return $something;
	//
	if( is_object($something) || is_null($something) ){
		return false;
	}
	//
	return floatval($something) > 0;
}//booleanval

?>
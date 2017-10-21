<?php

function formatData( $data ){
	if( is_string($data) ) return $data;
	//
	if( is_array($data) ){
		$string = '';
		foreach( $data as $key => $value ){
			$string .= $key . ': '.$value . "\n";
		}
		//
		return $string;
	}
	//
	return 'null';
}//formatData

?>
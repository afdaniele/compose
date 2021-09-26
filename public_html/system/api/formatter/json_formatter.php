<?php

function formatData( $data ){
	if( is_string($data) ) return $data;
	//
	if( is_array($data) ){
		return prettyPrint( json_encode( $data ) );
	}
	//
	return 'null';
}//formatData

?>
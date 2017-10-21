<?php

require_once __DIR__ . '/utils/Array2XML.php';
use utils\Array2XML as Array2XML;


function formatData( $data ){
	if( is_string($data) ) return $data;
	//
	if( is_array($data) ){
		$xml = Array2XML::createXML('result', $data);
		return $xml->saveXML();
	}
	//
	return 'null';
}//formatData

?>
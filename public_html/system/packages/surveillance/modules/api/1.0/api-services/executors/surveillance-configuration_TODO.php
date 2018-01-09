<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 8th 2018



require_once __DIR__.'/../../../../../../../classes/Core.php';
use system\classes\Core as Core;

require_once __DIR__.'/../../../../../../../api/1.0/utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'get':
			//TODO
			// $key = $arguments['key'];
			// //
			// $res = \system\classes\Configuration::get( $key );
			// //
			// if( !$res['success'] ) return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
			//
			return array( 'code' => 200, 'status' => 'OK', 'data' => array( 'value' => $res['data'] ) );
			break;
		//
		case 'set':
			//TODO
			// $keys = $service['actions']['get']['parameters']['mandatory']['key']['values'];
			// $k = 0;
			// $error = null;
			// $success = true;
			// foreach( $keys as $key ){
			// 	if( isset($arguments[$key]) ){
			// 		$val = $arguments[$key];
			// 		//
			// 		$res = \system\classes\Configuration::set( $key, $val );
			// 		//
			// 		$k++;
			// 		//
			// 		if( !$res['success'] ){
			// 			$success = false;
			// 			$error = $res['data'];
			// 			break;
			// 		}
			// 	}
			// }
			// //
			// if( $k == 0 ) return array( 'code' => 400, 'status' => 'Bad Request', 'message' => 'Nothing to change' );
			// //
			// if( !$success ) return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $error );
			// //
			// $res = \system\classes\Configuration::commit();
			// if( !$res['success'] ) return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
			//
			return array( 'code' => 200, 'status' => 'OK' );
			break;
		//
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The command '".$actionName."' was not found" );
			break;
	}
}//execute

?>

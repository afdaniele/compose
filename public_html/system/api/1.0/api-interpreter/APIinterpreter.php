<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 14th 2018



namespace system\api\apiinterpreter;

//init configuration
require_once __DIR__.'/../../../classes/Configuration.php';
use system\classes\Configuration as Configuration;
Configuration::init();


require_once __DIR__.'/../../../classes/Core.php';
use system\classes\Core as Core;


require_once __DIR__.'/../utils/utils.php';



class APIinterpreter {

	private static $VERSION = '1.0';

	public static function interpret( &$service, &$actionName, &$arguments, &$format ){
		$serviceName = $service['id'];
		$executorPath = $service['executor'];

		// 1. init
		$cache = null;
		// if( Configuration::$CACHE_ENABLED ){
		// 	// load fast cache system
		// 	require_once __DIR__.'/../../../classes/phpfastcache/phpfastcache.php';
		// 	try{
		// 		$cache = phpFastCache(Configuration::$CACHE_SYSTEM);
		// 	}catch(Exception $e){
		// 		$cache = null;
		// 		Configuration::$CACHE_ENABLED = false;
		// 	}
		// }
		// //
		// Configuration::$CACHE_ENABLED = ( $cache !== null && $cache instanceof phpFastCache );
		Configuration::$CACHE_ENABLED = false;

		// 2. load api-service specifications
		// if( Configuration::$CACHE_ENABLED ){
		// 	$serviceLabel = strtoupper($serviceName)."-SERVICE-SPECIFICATION-".self::$VERSION;
		// 	$service =  $cache->get( $serviceLabel );
		// 	if( $service == null ){
		// 		// read from file
		// 		$spec_file_content = file_get_contents( __DIR__.'/../api-services/specifications/'.$serviceName.'.json' );
		// 		if( $spec_file_content === false ){
		// 			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The service '".$serviceName."' was not found" );
		// 		}
		// 		$service = json_decode($spec_file_content, true);
		// 		// save the api-service specifications into the cache for:  60 seconds * 60 minutes * 24 hours = 86400 seconds = 1 day
		// 		$cache->set( $serviceLabel , serialize($service) , 86400 );
		// 	}else{
		// 		$service = unserialize( $service );
		// 	}
		// }else{
		// 	// read from file
		// 	$spec_file_content = file_get_contents( __DIR__.'/../api-services/specifications/'.$serviceName.'.json' );
		// 	if( $spec_file_content === false ){
		// 		return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The service '".$serviceName."' was not found" );
		// 	}
		// 	$service = json_decode($spec_file_content, true);
		// }

		// 3. verify data completeness and correctness
		$action = $service['actions'][$actionName];

		// check for mandatory arguments
		$data = array();
		$error = null;
		if( is_array($action['parameters']['mandatory']) ){
			foreach( $action['parameters']['mandatory'] as $name => $details ){
				if( !( checkArgument( $name, $arguments, $details, $error ) === true ) ){
					$data[$name] = $error['message'];
				}
			}
		}
		if( $error !== null ){
			$error['message'] = 'An error occurred while processing the data in your request. Please check and try again!';
			$error['data']['errors'] = $data;
			return $error;
		}
		// check for optional arguments
		$error = null;
		if( is_array($action['parameters']['optional']) ){
			foreach( $action['parameters']['optional'] as $name => $details ){
				if( !( checkArgument( $name, $arguments, $details, $error, false ) === true ) ){
					$data[$name] = $error['message'];
				}
			}
		}
		if( $error !== null ){
			$error['message'] = 'An error occurred while processing the data in your request. Please check and try again!';
			$error['data']['errors'] = $data;
			return $error;
		}


		// 4. initialize the Core module
		//TODO: already done
		// Core::initCore();


		// 5. load the executor
		// $executorPath = __DIR__.'/../api-services/executors/'.$serviceName.'.php';
		if( !file_exists($executorPath) ){
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The service '".$serviceName."' was not found" );
		}
		require_once $executorPath;

		// 6. clean up the arguments (very important)
		unset( $arguments['__apiversion__'] );
		unset( $arguments['__service__'] );
		unset( $arguments['__action__'] );
		unset( $arguments['__format__'] );
		unset( $arguments['token'] );

		// 7. execute the action
		$result = execute( $service, $actionName, $arguments, $format );


		// 8. format the result content
		if( isset($result['data']) ){
			formatResult( $result['data'], $action['return']['values'] );
		}


		// 9. format the response
		require_once __DIR__.'/../../formatter/'.$format.'_formatter.php';
		$data = formatData( $result );
		$result['data'] = $data;
		$result['formatted'] = true;


		// ==================================================================================================================
		// ==================================================================================================================
		// ==================================================================================================================


		// 10. return the action execution result
		return $result;

	}//interpret

}//APIinterpreter

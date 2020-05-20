<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele



namespace system\api\apiinterpreter;

require_once __DIR__.'/../utils/utils.php';


class APIinterpreter {

	private static $VERSION = '1.0';

	public static function interpret( &$service, &$actionName, &$arguments, &$format ){
		$serviceName = $service['id'];
		$executorPath = $service['executor'];
		
		// 1. verify data completeness and correctness
		$action = $service['actions'][$actionName];
		// check for mandatory arguments
		$data = array();
		$error = null;
		if( is_array($action['parameters']['mandatory']) ){
    		// prepare arguments
            prepareArguments($arguments, $action['parameters']['mandatory']);
            // check arguments
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
    		// prepare arguments
            prepareArguments($arguments, $action['parameters']['optional']);
            // check arguments
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


		// 2. load the executor
		if( !file_exists($executorPath) ){
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The service '".$serviceName."' was not found" );
		}
		require_once $executorPath;


		// 3. clean up the arguments (very important)
		unset( $arguments['__apiversion__'] );
		unset( $arguments['__service__'] );
		unset( $arguments['__action__'] );
		unset( $arguments['__format__'] );
		unset( $arguments['token'] );


		// 4. execute the action
		$result = execute( $service, $actionName, $arguments, $format );


		// 5. format the result content
		if( isset($result['data']) ){
			formatResult( $result['data'], $action['return']['values'] );
		}

		
        // 6. compile result
		$result['data'] = $data;
		if (!isset($result['message'])) {
			$result['message'] = '';
		}


		// ==================================================================================================================


		// 7. return the action execution result
		return $result;

	}//interpret

}//APIinterpreter

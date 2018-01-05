<?php

namespace system\modules\surveillance;

require_once __DIR__.'/../../classes/Core.php';
require_once __DIR__.'/../../classes/Configuration.php';

use \system\classes\Core;
use \system\classes\Configuration;

/**
*   Module for controlling cameras, managing recorded videos, and streaming in real-time.
*/
class Surveillance{

	private static $initialized = false;

	// disable the constructor
	private function __construct() {}

    /**
    *   Initializes the Surveillance module.
    */
	public static function init(){
		if( !self::$initialized ){
			// do stuff
			return array( 'success' => true, 'data' => null );
		}else{
			return array( 'success' => true, 'data' => "Module already initialized!" );
		}
	}//init

    /**
    *   Safely terminates the module.
    */
	public static function close(){
        // do stuff
		return array( 'success' => true, 'data' => null );
	}//close

}//Surveillance

?>

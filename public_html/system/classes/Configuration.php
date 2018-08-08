<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 11/18/14
 * Time: 5:05 PM
 */

namespace system\classes;

require_once __DIR__.'/libs/booleanval.php';


class Configuration {

	private static $initialized = false;

	// Fields
	public static $TIMEZONE;
	public static $GMT;
	public static $DEBUG = true;

	public static $BASE_URL;
	public static $BASE;
	public static $PAGE;
	public static $ACTION;
	public static $ARG1;
	public static $ARG2;

	public static $IS_MOBILE = false;

	public static $CACHE_SYSTEM;
	public static $WEBAPI_VERSION;


	//Init
	public static function init(){
		$configFile = __DIR__.'/../config/configuration.php';
		//
		if( !self::$initialized ){
			//
			$error = false;
			$error_msg = null;
			//
			if ( !file_exists( $configFile ) ){
				// try to load the default configuration file
				$configFile = __DIR__.'/../config/configuration.default.php';
				if ( !file_exists( $configFile ) ){
					$error = true;
					$error_msg = "File not found: ".$configFile;
				}else{
					require( $configFile );
				}
			}else{
				require( $configFile );
			}
			//
			if( $error ){
				return array( 'success' => false, 'data'=> $error_msg );
			}
			//
			self::$TIMEZONE = $TIMEZONE;
			//

			// TODO: (Experimental) $BASE and $BASE_URL are now obtained from $_SERVER['HTTP_HOST']
			// self::$BASE_URL = $BASE_URL;
			// self::$BASE = $BASE_URL; //TODO: for language or other link tags
			
			//
			self::$CACHE_SYSTEM = $CACHE_SYSTEM;
			self::$WEBAPI_VERSION = $WEBAPI_VERSION;
			//
			self::$initialized = true;
			//
			return array( 'success' => true );
		}else{
			return array( 'success' => true );
		}
	}//init

}//Configuration

?>

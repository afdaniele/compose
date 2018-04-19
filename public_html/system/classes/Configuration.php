<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 11/18/14
 * Time: 5:05 PM
 */

namespace system\classes;

require_once __DIR__.'/jsonDB/JsonDB.php';
use system\classes\jsonDB\JsonDB;

require_once __DIR__.'/libs/booleanval.php';


class Configuration {

	private static $jsondb = null;

	private static $initialized = false;

	// Fields
	public static $TIMEZONE;
	public static $GMT;

	public static $BASE_URL;
	public static $BASE;
	public static $PAGE;
	public static $ACTION;
	public static $ARG1;
	public static $ARG2;
	public static $SHORT_SITE_NAME;
	public static $SHORT_SITE_LINK;

	public static $GOOGLE_CLIENT_ID;

	public static $NOREPLY_MAIL_ADDRESS;
	public static $NOREPLY_MAIL_HOST;
	public static $NOREPLY_MAIL_AUTH;
	public static $NOREPLY_MAIL_USERNAME;
	public static $NOREPLY_MAIL_PASSWORD;
	public static $NOREPLY_MAIL_SECURE_PROTOCOL;
	public static $NOREPLY_MAIL_SERVER_PORT;

	public static $MAINTEINANCE_MODE;
	public static $MAIN_PAGE_TITLE;
	public static $ADMIN_CONTACT_MAIL_ADDRESS;
	public static $CACHE_ENABLED;

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
			self::$BASE_URL = $BASE_URL;
			self::$BASE = $BASE_URL; //TODO: for language or other link tags
			self::$SHORT_SITE_NAME = $SHORT_SITE_NAME;
			self::$SHORT_SITE_LINK = $SHORT_SITE_LINK;
			//
			self::$GOOGLE_CLIENT_ID = $GOOGLE_CLIENT_ID;
			//
			self::$NOREPLY_MAIL_ADDRESS = $NOREPLY_MAIL_ADDRESS;
			self::$NOREPLY_MAIL_HOST = $NOREPLY_MAIL_HOST;
			self::$NOREPLY_MAIL_AUTH = $NOREPLY_MAIL_AUTH;
			self::$NOREPLY_MAIL_USERNAME = $NOREPLY_MAIL_USERNAME;
			self::$NOREPLY_MAIL_PASSWORD = $NOREPLY_MAIL_PASSWORD;
			self::$NOREPLY_MAIL_SECURE_PROTOCOL = $NOREPLY_MAIL_SECURE_PROTOCOL;
			self::$NOREPLY_MAIL_SERVER_PORT = $NOREPLY_MAIL_SERVER_PORT;
			//
			self::$CACHE_SYSTEM = $CACHE_SYSTEM;
			self::$WEBAPI_VERSION = $WEBAPI_VERSION;
			//
			// load the custom setting
			self::$jsondb = new JsonDB( __DIR__.'/../config/configuration.json' );

			// Dashboard configuration
			self::$MAINTEINANCE_MODE = booleanval( self::$jsondb->get('maintenance_mode', $DEFAULT_VALUES_DASHBOARD_CONFIG['maintenance_mode']) );
			self::$MAIN_PAGE_TITLE = self::$jsondb->get('main_page_title', $DEFAULT_VALUES_DASHBOARD_CONFIG['main_page_title']);
			self::$ADMIN_CONTACT_MAIL_ADDRESS = self::$jsondb->get('admin_contact_email_address', $DEFAULT_VALUES_DASHBOARD_CONFIG['admin_contact_email_address']);
			self::$CACHE_ENABLED = booleanval( self::$jsondb->get('cache_enabled', $DEFAULT_VALUES_DASHBOARD_CONFIG['cache_enabled']) );
			//
			self::$initialized = true;
			//
			return array( 'success' => true );
		}else{
			return array( 'success' => true );
		}
	}//init

	public static function get( $key, $default=null ){
		if( self::$jsondb != null ){
			if( self::$jsondb->contains($key) ){
				$val = self::$jsondb->get($key, $default);
				return array('success' => true, 'data' => $val);
			}else{
				return array('success' => false, 'data' => 'Parameter unknown');
			}
		}
		return array('success' => false, 'data' => 'An error occurred while reading the configurations. Please, retry!');
	}//get

	public static function set( $key, $val ){
		if( self::$jsondb != null ){
			self::$jsondb->set($key, $val);
			return array('success' => true );
		}
		return array('success' => false, 'data' => 'An error occurred while writing the configurations. Please, retry!');
	}//set

	public static function commit(){
		if( self::$jsondb != null ){
			$res = self::$jsondb->commit();
			return $res;
		}
		return array('success' => false, 'data' => 'An error occurred while writing the configurations. Please, retry!');
	}//commit

}//Configuration

?>

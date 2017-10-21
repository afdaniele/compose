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
	public static $PLATFORM;
	public static $PLATFORM_BASE;
	public static $SHORT_SITE_NAME;
	public static $SHORT_SITE_LINK;

	public static $MYSQL_HOST;
	public static $MYSQL_DBNAME;
	public static $MYSQL_USERNAME;
	public static $MYSQL_PASSWORD;

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
	public static $SURVEILLANCE_1_ENABLED;
	public static $SURVEILLANCE_1_DISK_DEVICE;
	public static $SURVEILLANCE_1_RAW_DATA_PATH;
	public static $SURVEILLANCE_1_LOG_DATA_PATH;
	public static $SURVEILLANCE_1_ACTIVITY_DATA_PATH;
	public static $SURVEILLANCE_2_ENABLED;
	public static $SURVEILLANCE_2_DISK_DEVICE;
	public static $SURVEILLANCE_2_RAW_DATA_PATH;
	public static $SURVEILLANCE_2_LOG_DATA_PATH;
	public static $SURVEILLANCE_2_ACTIVITY_DATA_PATH;
	public static $DUCKIEFLEET_PATH;
	public static $DUCKIEFLEET_BRANCH;

	public static $IS_MOBILE = false;
	public static $SURVEILLANCE;

	// Note: some pages are designed for 30 minutes chunks of videos.
	// Changing this value does not change them. Modify cautiously.
	public static $SURVEILLANCE_CHUNKS_DURATION_MINUTES = 30;

	public static $CACHE_SYSTEM;
	public static $WEBAPI_VERSION;

	public static $FOOTBAR_MESSAGE;

	public static $TRAN = array(
		'phone' => 'Phone',
		'password' => 'Password',
		'name' => 'First name',
		'surname' => 'Last name',
		'email' => 'E-mail',
		'status' => 'Status',
		'registrationTime' => 'Member since',
		'registrationTimeF' => 'Member since',
		'lastSeen' => 'Last access',
		'userID' => 'User ID',
		'id' => 'ID',
		'contactName' => 'Contact name',
		'contact_name' => 'Contact name',
		'city' => 'City',
		'cap' => 'ZIP',
		'street' => 'Address 1',
		'streetNo' => 'Address 2',
		'homeNo' => 'Apt',
		'latitude' => 'Latitude',
		'longitude' => 'Longitude',
		'passwordconfirm' => 'Password (confirm)',
		'active' => 'Active',
		'banned' => 'Banned',
		'deleted' => 'Deleted',
		'setup' => 'Setup pending',
		'denied' => 'Denied',
		'accepted' => 'Accepted',
		'completed' => 'Completed',
		'problem' => 'Issues',
		'question' => 'Generic question',
		'info' => 'Information',
		'online' => 'Online',
		'offline' => 'Offline'
	);


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
			self::$FOOTBAR_MESSAGE = $FOOTBAR_MESSAGE;
			//
			self::$MYSQL_HOST = $MYSQL_HOST;
			self::$MYSQL_DBNAME = $MYSQL_DBNAME;
			self::$MYSQL_USERNAME = $MYSQL_USERNAME;
			self::$MYSQL_PASSWORD = $MYSQL_PASSWORD;
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
			//
			self::$MAINTEINANCE_MODE = self::$jsondb->get('maintenance_mode', $MAINTEINANCE_MODE);
			self::$MAIN_PAGE_TITLE = self::$jsondb->get('main_page_title', $MAIN_PAGE_TITLE);
			self::$ADMIN_CONTACT_MAIL_ADDRESS = self::$jsondb->get('admin_contact_email_address', $ADMIN_CONTACT_MAIL_ADDRESS);
			self::$CACHE_ENABLED = booleanval( self::$jsondb->get('cache_enabled', $CACHE_ENABLED) );
			self::$SURVEILLANCE_1_ENABLED = self::$jsondb->get('surveillance_1_enabled', $SURVEILLANCE_1_ENABLED);
			self::$SURVEILLANCE_1_DISK_DEVICE = self::$jsondb->get('surveillance_1_disk_dev', $SURVEILLANCE_1_DISK_DEVICE);
			self::$SURVEILLANCE_1_RAW_DATA_PATH = self::$jsondb->get('surveillance_1_raw_data_path', $SURVEILLANCE_1_RAW_DATA_PATH);
			self::$SURVEILLANCE_1_LOG_DATA_PATH = self::$jsondb->get('surveillance_1_log_data_path', $SURVEILLANCE_1_LOG_DATA_PATH);
			self::$SURVEILLANCE_1_ACTIVITY_DATA_PATH = self::$jsondb->get('surveillance_1_activity_data_path', $SURVEILLANCE_1_ACTIVITY_DATA_PATH);
			self::$SURVEILLANCE_2_ENABLED = self::$jsondb->get('surveillance_2_enabled', $SURVEILLANCE_2_ENABLED);
			self::$SURVEILLANCE_2_DISK_DEVICE = self::$jsondb->get('surveillance_2_disk_dev', $SURVEILLANCE_2_DISK_DEVICE);
			self::$SURVEILLANCE_2_RAW_DATA_PATH = self::$jsondb->get('surveillance_2_raw_data_path', $SURVEILLANCE_2_RAW_DATA_PATH);
			self::$SURVEILLANCE_2_LOG_DATA_PATH = self::$jsondb->get('surveillance_2_log_data_path', $SURVEILLANCE_2_LOG_DATA_PATH);
			self::$SURVEILLANCE_2_ACTIVITY_DATA_PATH = self::$jsondb->get('surveillance_2_activity_data_path', $SURVEILLANCE_2_ACTIVITY_DATA_PATH);
			self::$DUCKIEFLEET_PATH = self::$jsondb->get('duckiefleet_path', $DUCKIEFLEET_PATH);
			self::$DUCKIEFLEET_BRANCH = self::$jsondb->get('duckiefleet_branch', $DUCKIEFLEET_BRANCH);
			//
			self::$SURVEILLANCE = array(
				1 => array(
					'enabled' => self::$SURVEILLANCE_1_ENABLED,
					'disk_dev' => self::$SURVEILLANCE_1_DISK_DEVICE,
					'raw_data_path' => self::$SURVEILLANCE_1_RAW_DATA_PATH,
					'log_data_path' => self::$SURVEILLANCE_1_LOG_DATA_PATH,
					'activity_data_path' => self::$SURVEILLANCE_1_ACTIVITY_DATA_PATH
				),
				2 => array(
					'enabled' => self::$SURVEILLANCE_2_ENABLED,
					'disk_dev' => self::$SURVEILLANCE_2_DISK_DEVICE,
					'raw_data_path' => self::$SURVEILLANCE_2_RAW_DATA_PATH,
					'log_data_path' => self::$SURVEILLANCE_2_LOG_DATA_PATH,
					'activity_data_path' => self::$SURVEILLANCE_2_ACTIVITY_DATA_PATH
				)
			);
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
				return array('success' => false, 'data' => 'Il parametro che si sta cercando non risulta fra quelli disponibili');
			}
		}
		return array('success' => false, 'data' => 'Impossibile leggere dalle impostazioni. Riprova!');
	}//get

	public static function set( $key, $val ){
		if( self::$jsondb != null ){
			self::$jsondb->set($key, $val);
			return array('success' => true );
		}
		return array('success' => false, 'data' => 'Impossibile scrivere le impostazioni. Riprova!');
	}//set

	public static function commit(){
		if( self::$jsondb != null ){
			$res = self::$jsondb->commit();
			return $res;
		}
		return array('success' => false, 'data' => 'Impossibile scrivere le impostazioni. Riprova!');
	}//commit

}//Configuration

?>

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

	public static $DEFAULT_VALUES_DASHBOARD_CONFIG;
	public static $DEFAULT_VALUES_DUCKIETOWN_CONFIG;

	public static $MAINTEINANCE_MODE;
	public static $MAIN_PAGE_TITLE;
	public static $ADMIN_CONTACT_MAIL_ADDRESS;
	public static $CACHE_ENABLED;
	public static $WHAT_THE_DUCK_TESTS_DATA_PATH;
	public static $SURVEILLANCE_1_ENABLED;
	public static $SURVEILLANCE_1_DISK_DEVICE;
	public static $SURVEILLANCE_1_RAW_DATA_PATH;
	public static $SURVEILLANCE_1_LOG_DATA_PATH;
	public static $SURVEILLANCE_1_WEBM_DATA_PATH;
	public static $SURVEILLANCE_1_ACTIVITY_DATA_PATH;
	public static $SURVEILLANCE_2_ENABLED;
	public static $SURVEILLANCE_2_DISK_DEVICE;
	public static $SURVEILLANCE_2_RAW_DATA_PATH;
	public static $SURVEILLANCE_2_LOG_DATA_PATH;
	public static $SURVEILLANCE_2_WEBM_DATA_PATH;
	public static $SURVEILLANCE_2_ACTIVITY_DATA_PATH;
	public static $DUCKIEFLEET_PATH;
	public static $DUCKIEFLEET_BRANCH;
	public static $DUCKIEBOT_DEFAULT_USERNAME;
	public static $DUCKIEBOT_DEFAULT_PASSWORD;
	public static $DUCKIEBOT_W_CONFIG_DEVICE_VID_PID_LIST;
	public static $DUCKIEBOT_D_CONFIG_DEVICE_VID_PID_LIST;
	public static $DUCKIEBOT_ROS_PATH;

	public static $SURVEILLANCE;
	public static $IS_MOBILE = false;

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
			self::$DEFAULT_VALUES_DASHBOARD_CONFIG = $DEFAULT_VALUES_DASHBOARD_CONFIG;
			self::$DEFAULT_VALUES_DUCKIETOWN_CONFIG = $DEFAULT_VALUES_DUCKIETOWN_CONFIG;
			// load the custom setting
			self::$jsondb = new JsonDB( __DIR__.'/../config/configuration.json' );
			$duckietown_settings_file = __DIR__.'/../../../config/configuration.json';
			$duckietown_settings = self::$DEFAULT_VALUES_DUCKIETOWN_CONFIG;
			if( file_exists($duckietown_settings_file) ){
				$duckietown_settings = json_decode( file_get_contents($duckietown_settings_file), true );
			}
			$custom_settings_what_the_duck = $duckietown_settings['what_the_duck'];
			$custom_settings_surveillance = $duckietown_settings['surveillance'];
			$custom_settings_duckiefleet = $duckietown_settings['duckiefleet'];
			$custom_settings_duckiebot = $duckietown_settings['duckiebot'];
			// Dashboard configuration
			self::$MAINTEINANCE_MODE = booleanval( self::$jsondb->get('maintenance_mode', $DEFAULT_VALUES_DASHBOARD_CONFIG['maintenance_mode']) );
			self::$MAIN_PAGE_TITLE = self::$jsondb->get('main_page_title', $DEFAULT_VALUES_DASHBOARD_CONFIG['main_page_title']);
			self::$ADMIN_CONTACT_MAIL_ADDRESS = self::$jsondb->get('admin_contact_email_address', $DEFAULT_VALUES_DASHBOARD_CONFIG['admin_contact_email_address']);
			self::$CACHE_ENABLED = booleanval( self::$jsondb->get('cache_enabled', $DEFAULT_VALUES_DASHBOARD_CONFIG['cache_enabled']) );
			self::$SURVEILLANCE_1_ENABLED = booleanval( self::$jsondb->get('camera_1_enabled', $DEFAULT_VALUES_DASHBOARD_CONFIG['camera_1_enabled']) );
			self::$SURVEILLANCE_2_ENABLED = booleanval( self::$jsondb->get('camera_2_enabled', $DEFAULT_VALUES_DASHBOARD_CONFIG['camera_2_enabled']) );
			// Duckietown configuration
			self::$WHAT_THE_DUCK_TESTS_DATA_PATH = $custom_settings_what_the_duck['tests_data_path'];
			self::$SURVEILLANCE_1_DISK_DEVICE = $custom_settings_surveillance['camera_1_disk_dev'];
			self::$SURVEILLANCE_1_RAW_DATA_PATH = $custom_settings_surveillance['camera_1_raw_data_path'];
			self::$SURVEILLANCE_1_LOG_DATA_PATH = $custom_settings_surveillance['camera_1_log_data_path'];
			self::$SURVEILLANCE_1_WEBM_DATA_PATH = $custom_settings_surveillance['camera_1_webm_data_path'];
			self::$SURVEILLANCE_1_ACTIVITY_DATA_PATH = $custom_settings_surveillance['camera_1_activity_data_path'];
			self::$SURVEILLANCE_2_DISK_DEVICE = $custom_settings_surveillance['camera_2_disk_dev'];
			self::$SURVEILLANCE_2_RAW_DATA_PATH = $custom_settings_surveillance['camera_2_raw_data_path'];
			self::$SURVEILLANCE_2_LOG_DATA_PATH = $custom_settings_surveillance['camera_2_log_data_path'];
			self::$SURVEILLANCE_2_WEBM_DATA_PATH = $custom_settings_surveillance['camera_2_webm_data_path'];
			self::$SURVEILLANCE_2_ACTIVITY_DATA_PATH = $custom_settings_surveillance['camera_2_activity_data_path'];
			self::$DUCKIEFLEET_PATH = $custom_settings_duckiefleet['path'];
			self::$DUCKIEFLEET_BRANCH = $custom_settings_duckiefleet['branch'];
			self::$DUCKIEBOT_DEFAULT_USERNAME = $custom_settings_duckiebot['ssh_username'];
			self::$DUCKIEBOT_DEFAULT_PASSWORD = $custom_settings_duckiebot['ssh_password'];
			self::$DUCKIEBOT_ROS_PATH = $custom_settings_duckiebot['ros_path'];
			self::$DUCKIEBOT_W_CONFIG_DEVICE_VID_PID_LIST = $custom_settings_duckiebot['w_config_vid_pid_list'];
			self::$DUCKIEBOT_D_CONFIG_DEVICE_VID_PID_LIST = $custom_settings_duckiebot['d_config_vid_pid_list'];
			//
			self::$SURVEILLANCE = array(
				1 => array(
					'disk_dev' => self::$SURVEILLANCE_1_DISK_DEVICE,
					'raw_data_path' => self::$SURVEILLANCE_1_RAW_DATA_PATH,
					'log_data_path' => self::$SURVEILLANCE_1_LOG_DATA_PATH,
					'webm_data_path' => self::$SURVEILLANCE_1_WEBM_DATA_PATH,
					'activity_data_path' => self::$SURVEILLANCE_1_ACTIVITY_DATA_PATH
				),
				2 => array(
					'disk_dev' => self::$SURVEILLANCE_2_DISK_DEVICE,
					'raw_data_path' => self::$SURVEILLANCE_2_RAW_DATA_PATH,
					'log_data_path' => self::$SURVEILLANCE_2_LOG_DATA_PATH,
					'webm_data_path' => self::$SURVEILLANCE_2_WEBM_DATA_PATH,
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

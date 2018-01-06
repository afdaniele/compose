<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 11/18/14
 * Time: 4:15 PM
 */

namespace system\classes;

// booleanval function
require_once __DIR__.'/libs/booleanval.php';
// structure
require_once __DIR__.'/Configuration.php';
require_once __DIR__.'/enum/StringType.php';
require_once __DIR__.'/enum/EmailTemplates.php';
// fast cache system
require_once __DIR__.'/phpfastcache/phpfastcache.php';
// php-mailer classes
require_once __DIR__.'/PHPMailer/PHPMailerAutoload.php';

require_once __DIR__.'/yaml/Spyc.php';

require_once __DIR__.'/jsonDB/JsonDB.php';

// load Google API client
require_once __DIR__.'/google_api_php_client/vendor/autoload.php';


use \phpfastcache;
use system\classes\enum\EmailTemplates;
use system\classes\enum\StringType;
use system\classes\jsonDB\JsonDB;


class Core{
	/**
	 * Construct won't be called inside this class and is uncallable from
	 * the outside. This prevents instantiating this class.
	 * This is by purpose, because we want a static class.
	 */

	private static $initialized = false;

	// Fields
	private static $cache = null;

	private static $pages = null;

	private static $regexes = array(
		"alphabetic" => "/^[a-zA-Z]+$/",
		"alphanumeric" => "/^[a-zA-Z0-9]+$/",
		"alphanumeric_s" => "/^[a-zA-Z0-9\\s]+$/",
		"numeric" => "/^[0-9]+$/",
		"password" => "/^[a-zA-Z0-9_.-]+$/",
		"text" => "/^[\\w\\D\\s_.,-]*$/",
		"email" => "/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/"
	);


	//Disable the constructor
	private function __construct() {}



	// =================================================================================================================
	// =================================================================================================================
	//
	//
	// Public Functions



	// =================================================================================================================
	// 1. Init functions

	public static function initCore(){
		if( !self::$initialized ){
			mb_internal_encoding("UTF-8");
			//init configuration
			$res = Configuration::init();
			if( !$res['success'] ){
				return $res;
			}
			// load email templates
			EmailTemplates::init();
			// enable cache
			try{
				if( Configuration::$CACHE_ENABLED ){
					try{
						self::$cache = phpFastCache(Configuration::$CACHE_SYSTEM);
					}catch(Exception $e){
						self::$cache = null;
						Configuration::$CACHE_ENABLED = false;
					}
				}
				//
				Configuration::$CACHE_ENABLED = ( self::$cache !== null && self::$cache instanceof phpFastCache );
				$_SESSION['CACHE_GROUPS'] = array();
				//
			}catch(\Exception $e){}
			// load list of available pages
			self::$pages = self::_load_available_pages();
			//
			self::$initialized = true;
			return array( 'success' => true, 'data' => null );
		}else{
			return array( 'success' => true, 'data' => "Core already initialized!" );
		}
	}//initCore


	public static function close(){
		return array( 'success' => true, 'data' => null );
	}//close


	public static function startSession(){
		session_start();
		if( !isset($_SESSION['TOKEN']) ){
			// generate a session token
			$token = self::generateRandomString(16);
			$_SESSION['TOKEN'] = $token;
		}
		//
		return true;
	}//startSession


	public static function logInUserWithGoogle( $id_token ){
		if( $_SESSION['USER_LOGGED'] ){
			return array( 'success' => false, 'data' => 'You are already logged in!' );
		}
		// get the Google Client ID for the Google Application 'Duckieboard'
		$CLIENT_ID = Configuration::$GOOGLE_CLIENT_ID;
		// verify id_token
		$client = new \Google_Client(['client_id' => $CLIENT_ID]);
		$payload = $client->verifyIdToken($id_token);
		if ($payload) {
			$userid = $payload['sub'];
			// create user descriptor
			$user_info = [
				"username" => $userid,
			    "name" => $payload['name'],
			    "email" => $payload['email'],
				"picture" => $payload['picture'],
			    "role" => "user",
			    "branch" => Configuration::$DUCKIEFLEET_BRANCH,
				"active" => true
			];
			// look for a pre-existing user profile
			$res = self::userExists($userid);
			if( $res['success'] ){
				// there exists a user profile, load info
				$res = self::openUserInfo($userid);
				if( !$res['success'] ){
					return $res;
				}
				$user_info = $res['data']->asArray();
			}
			//
			$_SESSION['USER_LOGGED'] = true;
			$_SESSION['USER_RECORD'] = $user_info;
			//
			self::regenerateSessionID();
			return array( 'success' => true, 'data' => $user_info );
		} else {
			// Invalid ID token
			return array( 'success' => false, 'data' => "Invalid ID Token" );
		}
	}//logInUserWithGoogle


	public static function isUserLoggedIn(){
		return ( isset($_SESSION['USER_LOGGED'])? $_SESSION['USER_LOGGED'] : false );
	}//isUserLoggedIn


	public static function logOutUser(){
		if( !$_SESSION['USER_LOGGED'] ){
			return array( 'success' => false, 'data' => 'User not logged in yet!' );
		}
		//
		$_SESSION['USER_LOGGED'] = false;
		unset( $_SESSION['USER_RECORD'] );
		unset( $_SESSION['USER_DUCKIEBOT'] );
		self::regenerateSessionID();
		//
		return true;
	}//logOutUser



	// =================================================================================================================
	// 2. Getter functions

	public static function getPagesList( $order=null ){
		if( is_null($order) || !isset(self::$pages[$order]) ){
			return self::$pages;
		}else{
			return self::$pages[$order];
		}
	}//getPagesList

	public static function getFilteredPagesList( $order='list', $enabledOnly=false, $accessibleBy=null ){
		if( !in_array($order, ['list', 'by-id', 'by-menuorder', 'by-responsive-priority']) ){
			// invalid order
			return [];
		}
		$pages = [];
		foreach( self::getPagesList($order) as $page ){
			if( $enabledOnly && !$page['enabled'] ) continue;
			if( !is_null($accessibleBy) && !in_array($accessibleBy, $page['access']) ) continue;
			//
			array_push( $pages, $page );
		}
		return $pages;
	}//getFilteredPagesList


	public static function getPageDetails( $page_id, $attribute=null ){
		$pages = self::getPagesList('by-id');
		$page_details = $pages[$page_id];
		if( is_null($attribute) ){
			return $page_details;
		}else{
			if( is_array($page_details) ){
				return $page_details[$attribute];
			}
			return null;
		}
	}//getPageDetails


	public static function userExists( $username ){
		$user_file = sprintf( __DIR__.'/../users/accounts/%s.json', $username );
		if( file_exists($user_file) ){
			return array( 'success' => true, 'data' => null );
		}else{
			return array( 'success' => false, 'data' => 'User "'.$username.'" not found!' );
		}
	}//userExists


	public static function openUserInfo( $username ){
		$user_file = sprintf( __DIR__.'/../users/accounts/%s.json', $username );
		$user_info = null;
		if( file_exists($user_file) ){
			// load user information
			$user_info = new JsonDB( $user_file );
		}else{
			return array( 'success' => false, 'data' => 'User "'.$username.'" not found!' );
		}
		//
		$static_user_info = $user_info->asArray();
		foreach (["username","name","email","picture","role","branch","active"] as $field) {
			if( !isset($static_user_info[$field]) ){
				return array( 'success' => false, 'data' => 'The descriptor file for the user "'.$username.'" is corrupted! Contact the administrator' );
			}
		}
		if( strcmp($static_user_info['username'], $username) != 0 ){
			return array( 'success' => false, 'data' => 'The descriptor file for the user "'.$username.'" is corrupted! Contact the administrator' );
		}
		//
		return array( 'success' => true, 'data' => $user_info );
	}//openUserInfo


	public static function getUserLogged( $field=null ){
		return (isset($_SESSION['USER_RECORD'])) ? ( ($field==null) ? $_SESSION['USER_RECORD'] : $_SESSION['USER_RECORD'][$field] ) : null;
	}//getUserLogged


	public static function getUserRole(){
		$user_role = ( self::isUserLoggedIn() )? self::getUserLogged('role') : 'guest';
		if( $user_role == 'user' ){
			$bot_name = self::getUserDuckiebot();
			if( is_null($bot_name) ){
				return 'candidate';
			}
		}
		return $user_role;
	}//getUserRole


	public static function getUserDuckiebot(){
		if( isset($_SESSION['USER_DUCKIEBOT']) ){
			return $_SESSION['USER_DUCKIEBOT'];
		}
		$username = self::getUserLogged('username');
		$res = self::getDuckiebotLinkedToUser($username);
		if( !$res['success'] ){
			self::throwError($res['data']);
		}
		$_SESSION['USER_DUCKIEBOT'] = $res['data'];
		return $_SESSION['USER_DUCKIEBOT'];
	}//getUserDuckiebot


	public static function getStatistics(){
		$statistics = array();
		//
		Configuration::$CACHE_ENABLED = ( self::$cache !== null && self::$cache instanceof phpFastCache );
		// cache stats
		$statistics['STATS_TOTAL_SELECT_REQS'] = ( (Configuration::$CACHE_ENABLED && self::$cache->isExisting('STATS_TOTAL_SELECT_REQS'))? self::$cache->get( 'STATS_TOTAL_SELECT_REQS' ) : 1 );
		$statistics['STATS_CACHED_SELECT_REQS'] = ( (Configuration::$CACHE_ENABLED && self::$cache->isExisting('STATS_CACHED_SELECT_REQS'))? self::$cache->get( 'STATS_CACHED_SELECT_REQS' ) : 1 );
		//
		return $statistics;
	}//getStatistics


	public static function getSiteName(){
		return Configuration::$SHORT_SITE_NAME;
	}//getSiteName


	public static function getCodebaseHash(){
		exec( 'git log -1 --format="%H"', $hash, $exit_code );
		if( $exit_code != 0 ){
			$hash = 'ND';
		}else{
			$hash = $hash[0];
		}
		//
		return $hash;
	}//getCodebaseHash


	public static function getServerStatus(){
		// OS release
		exec( 'grep "DISTRIB_DESCRIPTION" /etc/lsb-release', $lsb_release, $exit_code );
		if( $exit_code != 0 ){
			$lsb_release = '<error><code>/etc/lsb-release</code> was not found</error>';
		}else{
			$lsb_release = self::_regex_extract_group($lsb_release[0], "/DISTRIB_DESCRIPTION=\"(.+)\".*/", 1);
		}
		// CPU model
		exec( 'grep "model name" /proc/cpuinfo | sort -u', $cpu_model, $exit_code );
		if( $exit_code != 0 ){
			$cpu_model = '<error><code>/proc/cpuinfo</code> was not found</error>';
		}else{
			$cpu_model = self::_regex_extract_group($cpu_model[0], "/model\sname\s*:\s*(.*)/", 1);
		}
		// RAM total
		exec( "free -m | grep Mem | awk '{print $2}'", $ram_total, $exit_code );
		if( $exit_code != 0 ){
			$ram_total = "<error>The command <code>free</code> is not installed</error>";
			$ram_total_float = 1.0;
		}else{
			$ram_total_float = (float)$ram_total[0];
			$ram_total = sprintf("%d GB", floor(pow(2.0, ceil(log($ram_total_float, 2.0)))/1000.0) );
		}
		// RAM usage
		exec( "free -m | grep 'buffers/cache' | awk '{print $3}'", $ram_used, $exit_code );
		if( $exit_code != 0 ){
			$ram_usage = "<error>The command <code>free</code> is not installed</error>";
		}else{
			$ram_usage = (float)$ram_used[0] / $ram_total_float;
		}
		// CPU usage
		$cpu_load = sys_getloadavg();
		$cpu_usage = (float)$load[0];
		//
		return array(
			'os_release' => $lsb_release,
			'cpu_model' => $cpu_model,
			'ram_total' => $ram_total,
			'cpu_usage' => $cpu_usage,
			'ram_usage' => $ram_usage
		);
	}//getServerStatus


	public static function getServerDiskStatus( $cameraNum ){
		// Disk usage
		$disk_device = Configuration::$SURVEILLANCE[$cameraNum]['disk_dev'];
		exec( "df | grep '".$disk_device."' | sed 's/\s\s*/ /g' | awk '{print $5}'", $df, $exit_code );
		if( $exit_code != 0 ){
			$disk_usage = 1.0;
		}else{
			$disk_usage = (float)$df[0] / 100.0;
		}
		//
		return $disk_usage;
	}//getServerDiskStatus


	public static function getSurveillanceStatus( $cameraNum ){
		// Surveillance status
		$video_path = Configuration::$SURVEILLANCE[$cameraNum]['raw_data_path'];
		exec( "ps -aux | grep ffmpeg | grep 'rtsp://duckietown-visitor:' | grep '".$video_path."' | grep -v grep", $ffmpeg, $exit_code );
		$is_recording = booleanval( $exit_code == 0 );
		$current_chunk = null;
		if( $is_recording ){
			$current_chunk = self::_regex_extract_group($ffmpeg[0], '/.*[0-9]{4}-[0-9]{2}-[0-9]{2}_([0-9]{2}\.[0-9]{2})\.mp4/', 1);
		}
		//
		return array( 'is_recording' => $is_recording, 'chunk' => $current_chunk );
	}//getSurveillanceStatus


	public static function isSurveillanceSegmentPresent( $cameraNum, $segment_name ){
		$video_path = Configuration::$SURVEILLANCE[$cameraNum]['raw_data_path'];
		if( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}/', $segment_name) !== 1 ){
			return false;
		}
		$segment_parts = explode( '_', $segment_name );
		$date = $segment_parts[0];
		$segment_path = sprintf("%s/%s/%s.mp4", $video_path, $date, $segment_name);
		//
		return file_exists($segment_path);
	}//isSurveillanceSegmentPresent


	public static function isWebMSurveillanceSegmentPresent( $cameraNum, $segment_name ){
		$video_path = Configuration::$SURVEILLANCE[$cameraNum]['webm_data_path'];
		if( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}/', $segment_name) !== 1 ){
			return false;
		}
		$segment_parts = explode( '_', $segment_name );
		$date = $segment_parts[0];
		$segment_path = sprintf("%s/%s/web_%s.mp4", $video_path, $date, $segment_name);
		//
		return file_exists($segment_path);
	}//isWebMSurveillanceSegmentPresent


	public static function sizeOfSurveillanceSegment( $cameraNum, $segment_name ){
		if( self::isSurveillanceSegmentPresent( $cameraNum, $segment_name ) ){
			$video_path = Configuration::$SURVEILLANCE[$cameraNum]['raw_data_path'];
			$segment_parts = explode( '_', $segment_name );
			$date = $segment_parts[0];
			$segment_path = sprintf("%s/%s/%s.mp4", $video_path, $date, $segment_name);
			return human_filesize( filesize($segment_path) );
		}
		//
		return null;
	}//sizeOfSurveillanceSegment


	public static function sizeOfWebMSurveillanceSegment( $cameraNum, $segment_name ){
		if( self::isSurveillanceSegmentPresent( $cameraNum, $segment_name ) ){
			$video_path = Configuration::$SURVEILLANCE[$cameraNum]['webm_data_path'];
			$segment_parts = explode( '_', $segment_name );
			$date = $segment_parts[0];
			$segment_path = sprintf("%s/%s/web_%s.mp4", $video_path, $date, $segment_name);
			return human_filesize( filesize($segment_path) );
		}
		//
		return null;
	}//sizeOfWebMSurveillanceSegment


	public static function getSurveillanceRecordingHistory( $cameraNum, $history_len=null, $month=null, $reverse_order=false ){
		$result = array( 'total_minutes' => 0, 'days' => array() );
		// Get the last $history_len dates
		$video_path = Configuration::$SURVEILLANCE[$cameraNum]['raw_data_path'];
		$command = "ls -l '".$video_path."' ";
		$command = $command."| grep -E '[0-9]{4}-".( ( $month != null )? sprintf('%02d', $month) : '[0-9]{2}' )."-[0-9]{2}' ";
		$command = $command."| awk '{print $9}' ";
		$command = $command."| sort ".( ( $reverse_order )? '-r' : '' );
		$command = $command. ( ( $history_len != null )? "| head -".$history_len : '' );
		exec( $command, $history, $exit_code );
		// Go through the dates and compute the number of hours recorded
		$chunk_len = Configuration::$SURVEILLANCE_CHUNKS_DURATION_MINUTES;
		foreach( $history as $day ){
			$day_path = $video_path.'/'.$day.'/';
			$day_recordings = array();
			exec( "ls -l '".$day_path."' | grep -E '[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}\.mp4' | awk '{print $9}' | sort", $day_recordings, $exit_code );
			$total_recording_this_day = $chunk_len * sizeof( $day_recordings );
			$result['days'][ $day ] = array(
				'chunks' => array(),
				'total_minutes' => $total_recording_this_day
			);
			$result['total_minutes'] += $total_recording_this_day;
			foreach( $day_recordings as $chunk ){
				$chunk = self::_regex_extract_group($chunk, "/[0-9]{4}-[0-9]{2}-[0-9]{2}_([0-9]{2}\.[0-9]{2})\.mp4/", 1);
				array_push( $result['days'][ $day ][ 'chunks' ], $chunk );
			}
		}
		return $result;
	}//getSurveillanceRecordingHistory


	public static function getSurveillancePostProcessingHistory( $cameraNum, $history_len=null, $month=null, $reverse_order=false ){
		$result = array( 'total_minutes' => 0, 'days' => array() );
		// Get the last $history_len dates
		$activity_path = Configuration::$SURVEILLANCE[$cameraNum]['activity_data_path'];
		$command = "ls -l '".$activity_path."' ";
		$command = $command."| grep -E '[0-9]{4}-".( ( $month != null )? sprintf('%02d', $month) : '[0-9]{2}' )."-[0-9]{2}' ";
		$command = $command."| awk '{print $9}' ";
		$command = $command."| sort ".( ( $reverse_order )? '-r' : '' );
		$command = $command. ( ( $history_len != null )? "| head -".$history_len : '' );
		exec( $command, $history, $exit_code );
		// Go through the dates and compute the number of hours recorded
		$chunk_len = Configuration::$SURVEILLANCE_CHUNKS_DURATION_MINUTES;
		foreach( $history as $day ){
			$day_path = $activity_path.'/'.$day.'/';
			$day_activities = array();
			exec( "ls -l '".$day_path."' | grep -E '[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}\.json' | awk '{print $9}' | sort", $day_activities, $exit_code );
			$total_recording_this_day = $chunk_len * sizeof( $day_activities );
			$result['days'][ $day ] = array(
				'chunks' => array(),
				'total_minutes' => $total_recording_this_day
			);
			$result['total_minutes'] += $total_recording_this_day;
			foreach( $day_activities as $chunk ){
				$chunk = self::_regex_extract_group($chunk, "/[0-9]{4}-[0-9]{2}-[0-9]{2}_([0-9]{2}\.[0-9]{2})\.json/", 1);
				array_push( $result['days'][ $day ][ 'chunks' ], $chunk );
			}
		}
		return $result;
	}//getSurveillancePostProcessingHistory


	public static function getSurveillanceSegmentActivity( $cameraNum, $segment_name ){
		if( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}/', $segment_name) !== 1 ){
			return array('success' => false, 'data' => 'segment_name does not conform to the format required "YYYY-mm-dd_HH.mm"');
		}
		$date = self::_regex_extract_group($segment_name, "/([0-9]{4}-[0-9]{2}-[0-9]{2})_[0-9]{2}\.[0-9]{2}/", 1);
		$activity_file = sprintf( "%s/%s/%s.json",
			Configuration::$SURVEILLANCE[$cameraNum]['activity_data_path'],
			$date,
			$segment_name
		);
		if( file_exists($activity_file) ){
			$activity_log = json_decode( file_get_contents($activity_file), true );
			return array('success' => true, 'data' => $activity_log);
		}else{
			return array('success' => false, 'data' => 'The activity log for this segment does not exist');
		}
	}//getSurveillanceSegmentActivity


	public static function getSurveillanceActivityThumbnail( $cameraNum, $date ){
		if( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $date) !== 1 ){
			return array('success' => false, 'data' => 'date does not conform to the format required "YYYY-mm-dd"');
		}
		$activity_file = sprintf( "%s/%s/thumbnail.json",
			Configuration::$SURVEILLANCE[$cameraNum]['activity_data_path'],
			$date
		);
		if( file_exists($activity_file) ){
			$activity_log = json_decode( file_get_contents($activity_file), true );
			return array('success' => true, 'data' => $activity_log);
		}else{
			return array('success' => false, 'data' => 'The activity thumbnail for this date does not exist');
		}
	}//getSurveillanceActivityThumbnail


	public static function getDuckiebotsCurrentBranch(){
		exec( "ls -l '".Configuration::$DUCKIEFLEET_PATH.'/robots/'.Configuration::$DUCKIEFLEET_BRANCH."' | awk '{print $9}' | grep -E '[a-zA-Z0-9]*.robot.yaml' | sed -e 's/\.robot.yaml$//'", $duckiebots, $exit_code );
		//
		return $duckiebots;
	}//getDuckiebotsCurrentBranch


	public static function getDuckiebotOwner( $bot_name ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		$yaml_file = Configuration::$DUCKIEFLEET_PATH.'/robots/'.Configuration::$DUCKIEFLEET_BRANCH.'/'.$bot_name.'.robot.yaml';
		$yaml_file = str_replace('//', '/', $yaml_file);
		if( !file_exists($yaml_file) ){
			return null;
		}
		$yaml_content = spyc_load_file( $yaml_file );
		if( !isset($yaml_content['owner']) ){
			return null;
		}
		//
		return $yaml_content['owner'];
	}//getDuckiebotOwner


	private static function execCommandOnDuckiebot( $bot_name, $command, $ssh=null ){
		$res = array(
			'success' => false,
			'data' => null,
			'exit_code' => null,
			'connection' => null
		);
		// Set PHP timeout to 5 seconds
		set_time_limit(5);
		// open SSH connection (if needed)
		if( is_null($ssh) ){
			$host = sprintf('%s.local', $bot_name);
			$ssh = ssh2_connect($host);
			if ( $ssh === false ) {
			    $res['data'] = 'Host unreachable';
				return $res;
			}
			// authenticate SSH session
			$auth = @ssh2_auth_password($ssh, Configuration::$DUCKIEBOT_DEFAULT_USERNAME, Configuration::$DUCKIEBOT_DEFAULT_PASSWORD);
			if ( $auth === false ) {
				$res['data'] = 'Authentication failed';
				return $res;
			}
		}
		$res['connection'] = $ssh;
		// exec command
		$command .= '; echo -e "\n__EXIT_CODE_$?"';
		$return_stream = @ssh2_exec( $ssh, $command );
		stream_set_blocking( $return_stream, true );
		if( strcmp(get_resource_type($return_stream), "stream") !== 0 ){
			$res['data'] = 'Command failed';
			return $res;
		}
		// get stream content
		$stream_content = stream_get_contents( $return_stream );
		// get exit code
		$exit_code = self::_regex_extract_group($stream_content, "/.*__EXIT_CODE_([0-9]+).*/", 1);
		$stream_content = trim( preg_replace( "/.*__EXIT_CODE_([0-9]+).*/", "", $stream_content, 1 ) );
		// create response object
		$res['success'] = true;
		$res['exit_code'] = $exit_code;
		$res['data'] = $stream_content;
		return $res;
	}


	private static function getROScommand( $command ){
		return sprintf('source %s/setup.bash; %s', Configuration::$DUCKIEBOT_ROS_PATH, $command);
	}//getROScommand


	public static function authenticateOnDuckiebot( $bot_name, $username, $password, $protectDefaultUser=true ){
		// prepare result object
		$res = array(
			'success' => false,
			'data' => null,
			'connection' => null
		);
		// check whether the Duckiebot exists
		if( !self::duckiebotExists($bot_name) ){
			$res['data'] = 'Duckiebot not found';
			return $res;
		}
		// check whether the username provided matches the default backdoor username used by the platform
		if( $protectDefaultUser ){
			if( strcasecmp( trim($username), trim(Configuration::$DUCKIEBOT_DEFAULT_USERNAME) ) == 0 ){
				$res['data'] = sprintf('The user `%s` is protected. Create your own user to continue.', Configuration::$DUCKIEBOT_DEFAULT_USERNAME);
				return $res;
			}
		}
		// Set PHP timeout to 5 seconds and open SSH connection
		set_time_limit(5);
		$host = sprintf('%s.local', $bot_name);
		$ssh = ssh2_connect($host);
		if( $ssh === false ){
			$res['data'] = 'SSH connection timed out';
			return $res;
		}
		// authenticate SSH session
		$auth = @ssh2_auth_password($ssh, $username, $password);
		if( $auth === false ) {
			$res['data'] = 'Authentication failed';
			return $res;
		}
		$res['success'] = true;
		$res['data'] = 'OK';
		$res['connection'] = $ssh;
		//
		return $res;
	}//authenticateOnDuckiebot


	public static function getDuckiebotNetworkConfig( $bot_name ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		$command = "ifconfig -a | sed -e 's/^$/=====/'";
		$res = self::execCommandOnDuckiebot( $bot_name, $command );
		if( $res['success'] ){
			$data = $res['data'];
			$interfaces_strs = split("=====", $data);
			$interfaces = array();
			// iterate over the interfaces
			foreach ($interfaces_strs as $interface_str) {
				if( strlen($interface_str) <= 10 ) continue;
				$interface_str = trim( $interface_str );
				// get interface name
				$interface_name = trim( self::_regex_extract_group($interface_str, "/(.+) Link encap:.*/", 1) );
				// get interface MAC address
				$interface_mac = self::_regex_extract_group($interface_str, "/.*HWaddr ([a-z0-9:]{17}).*/", 1);
				if( $interface_mac == null ){
					$interface_mac = 'ND';
				}
				// get status and IP address
				$interface_connected = true;
				$interface_IP = self::_regex_extract_group($interface_str, "/.*inet addr:([0-9\.]+).*/", 1);
				$interface_mask = self::_regex_extract_group($interface_str, "/.*Mask:([0-9\.]+).*/", 1);
				if( $interface_IP == null ){
					$interface_IP = 'ND';
					$interface_mask = 'ND';
					$interface_connected = false;
				}
				array_push( $interfaces, array(
					'name' => $interface_name,
					'connected' => $interface_connected,
					'mac' => $interface_mac,
					'ip' => $interface_IP,
					'mask' => $interface_mask
				) );
			}
			return $interfaces;
		}
		return $res;
	}//getDuckiebotNetworkConfig


	public static function getDuckiebotDiskStatus( $bot_name ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		// get list of mounted devices and their mount points
		$command = "mount | grep '^/dev' | awk '{print $3\",\"$1}'";
		$res = self::execCommandOnDuckiebot( $bot_name, $command );
		$devices = array();
		if( $res['success'] ){
			foreach( explode("\n", $res['data']) as $dev ){
				$dev = explode(",", $dev);
				$dev_mountpoint = $dev[0];
				$dev_name = $dev[1];
				$devices[ $dev_mountpoint ] = array(
					'mountpoint' => $dev_mountpoint,
					'device' => $dev_name,
					'used' => 1.0,
					'free' => 0.0
				);
			}
		}else{
			return $res;
		}
		// get list of mountpoints and their status
		$command = "df | sed -n '1!p' | sed 's/%//g' | awk '{print $6\",\"$5/100}'";
		$res = self::execCommandOnDuckiebot( $bot_name, $command, $res['connection'] );
		if( $res['success'] ){
			foreach( explode("\n", $res['data']) as $dev ){
				$dev = explode(",", $dev);
				$dev_mountpoint = $dev[0];
				$dev_usage = round( $dev[1], 2 );
				if( isset($devices[ $dev_mountpoint ]) ){
					$devices[ $dev_mountpoint ]['used'] = $dev_usage;
					$devices[ $dev_mountpoint ]['free'] = 1.0-$dev_usage;
				}
			}
		}else{
			return $res;
		}
		// convert the dictionary into a list of devices
		$devices_list = array_values($devices);
		//
		return $devices_list;
	}//getDuckiebotDiskStatus


	public static function getDuckiebotConfiguration( $bot_name ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		$configuration = array(
			'w' => false,
			'j' => false,
			'd' => false
		);
		// get the list of USB devices
		$command = "lsusb";
		$res = self::execCommandOnDuckiebot( $bot_name, $command );
		if( $res['success'] ){
			// search for the Edimax (w configuration)
			$device_ids = implode( "|", Configuration::$DUCKIEBOT_W_CONFIG_DEVICE_VID_PID_LIST );
			$regex = sprintf("/.* ID (%s) .*/", $device_ids);
			$wireless_device_id = self::_regex_extract_group($res['data'], $regex, 1);
			if( !is_null($wireless_device_id) ){
				$configuration['w'] = true;
			}
			// search for the USB Drive (d configuration)
			$device_ids = implode( "|", Configuration::$DUCKIEBOT_D_CONFIG_DEVICE_VID_PID_LIST );
			$regex = sprintf("/.* ID (%s) .*/", $device_ids);
			$storage_device_id = self::_regex_extract_group($res['data'], $regex, 1);
			if( !is_null($storage_device_id) ){
				$configuration['d'] = true;
			}
		}else{
			return $res;
		}
		// search Joystick (j configuration)
		$command = "test -e /dev/input/js0";
		$res = self::execCommandOnDuckiebot( $bot_name, $command, $res['connection'] );
		if( $res['success'] ){
			if( $res['exit_code'] == 0 ){
				$configuration['j'] = true;
			}
		}else{
			return $res;
		}
		//
		return $configuration;
	}//getDuckiebotConfiguration


	public static function getDuckiebotROScoreStatus( $bot_name, $ssh=null ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		// look for a running roscore process in the Duckiebot
		$command = "pgrep rosmaster";
		$res = self::execCommandOnDuckiebot( $bot_name, $command, $ssh );
		if( $res['success'] ){
			if( $res['exit_code'] == 0 ){
				$res['data'] = array('is_running' => true, 'pid' => trim($res['data']) );
			}else{
				$res['data'] = array('is_running' => false, 'pid' => null );
			}
		}
		return $res;
	}//getDuckiebotROScoreStatus


	public static function getDuckiebotROSnodes( $bot_name, $ssh=null ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		// get the list of ROS nodes running on the Duckiebot
		$command = self::getROScommand( "rosnode list" );
		$res = self::execCommandOnDuckiebot( $bot_name, $command, $ssh );
		if( $res['success'] ){
			if( $res['exit_code'] == 0 ){
				$res['data'] = explode("\n", $res['data']);
			}else{
				$res['success'] = false;
			}
		}
		return $res;
	}//getDuckiebotROSnodes


	public static function getDuckiebotROStopics( $bot_name, $ssh=null ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		// get the list of ROS topics being published on the Duckiebot
		$command = self::getROScommand( "rostopic list" );
		$res = self::execCommandOnDuckiebot( $bot_name, $command, $ssh );
		if( $res['success'] ){
			if( $res['exit_code'] == 0 ){
				$res['data'] = explode("\n", $res['data']);
			}else{
				$res['success'] = false;
			}
		}
		return $res;
	}//getDuckiebotROStopics


	public static function getDuckiebotROS( $bot_name ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		$out = array(
			'core' => array(
				'is_running' => false,
				'pid' => null
			),
			'nodes' => array(),
			'topics' => array()
		);
		$res = self::getDuckiebotROScoreStatus( $bot_name );
		if( $res['success'] ){
			$out['core'] = $res['data'];
			if( !$res['data']['is_running'] ){ return $out; }
			// get nodes
			$res = self::getDuckiebotROSnodes( $bot_name, $res['connection'] );
			if( !$res['success'] ){ return $res; }
			$out['nodes'] = $res['data'];
			// get topics
			$res = self::getDuckiebotROStopics( $bot_name, $res['connection'] );
			if( !$res['success'] ){ return $res; }
			$out['topics'] = $res['data'];
		}else{
			return $res;
		}
		return $out;
	}//getDuckiebotROS


	public static function getDuckiebotLatestWhatTheDuck( $bot_name ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		$what_the_duck = array(
			'duckiebot' => null,
			'laptops' => array() //TODO: support for multiple laptops
		);
		// check if we have the WTD for the Duckiebot results locally
		$duckiebot_wtd_test_filepath = sprintf( '%s/%s.wtd.json', Configuration::$WHAT_THE_DUCK_TESTS_DATA_PATH, $bot_name );
		if( file_exists($duckiebot_wtd_test_filepath) ){
			$wtd_str = file_get_contents($duckiebot_wtd_test_filepath);
			$wtd = json_decode($wtd_str, true);
			$what_the_duck['duckiebot'] = $wtd['what-the-duck'];
		}
		//
		return $what_the_duck;
	}//getDuckiebotLatestWhatTheDuck


	public static function getDuckiebotLinkedToUser( $username ){
		$associations_dir = __DIR__."/../users/associations/";
		// create command
		$cmd = sprintf("ls -l %s | awk '{print $9}' | grep -E '^%s.[a-z]+$' | cat", $associations_dir, $username);
		// execute the command and parse the output
		$output = [];
		$retval = -100;
		exec( $cmd, $output, $retval );
		if( $retval != 0 ){
			return array('success' => false, 'data' => 'An error occurred while processing your request.');
		}
		// get the list of associations (hopefully no more than one element is present)
		if( count($output) == 0 ){
			return array('success' => true, 'data' => null);
		}else{
			//TODO: check for multiple associations indicating an inconsistent DB
			$association = $output[0];
			$parts = explode('.', $association);
			$bot_name = $parts[1];
			return array('success' => true, 'data' => $bot_name);
		}
	}//getDuckiebotLinkedTo

	public static function getUserLinkedToDuckiebot( $bot_name ){
		$associations_dir = __DIR__."/../users/associations/";
		// create command
		$cmd = sprintf("ls -l %s | awk '{print $9}' | grep -E '^[0-9]+.%s$' | cat", $associations_dir, $bot_name);
		// execute the command and parse the output
		$output = [];
		$retval = -100;
		exec( $cmd, $output, $retval );
		if( $retval != 0 ){
			return array('success' => false, 'data' => 'An error occurred while processing your request.');
		}
		// get the list of associations (hopefully no more than one element is present)
		if( count($output) == 0 ){
			return array('success' => true, 'data' => null);
		}else{
			//TODO: check for multiple associations indicating an inconsistent DB
			$association = $output[0];
			$parts = explode('.', $association);
			$username = $parts[0];
			return array('success' => true, 'data' => $username);
		}
	}//getUserLinkedToDuckiebot


	public static function isDuckiebotOnline( $bot_name ){
		if( !self::duckiebotExists($bot_name) ){
			return array('success' => false, 'data' => 'Duckiebot not found');
		}
		//
		exec( "ping -c 1 ".$bot_name.".local", $_, $exit_code );
		$is_online = booleanval( $exit_code == 0 );
		//
		return $is_online;
	}//isDuckiebotOnline


	public static function duckiebotExists( $bot_name ){
		$duckiebots = self::getDuckiebotsCurrentBranch();
		//
		return in_array($bot_name, $duckiebots);
	}//isDuckiebotOnline




	// =================================================================================================================
	// 3. Setter functions

	public static function linkDuckiebotToUserAccount( $bot_name ){
		// prepare result object
		$res = array(
			'success' => false,
			'data' => null
		);
		// check whether there is a user logged in
		if( !self::isUserLoggedIn() ){
			$res['data'] = 'You must be logged in to link a Duckiebot to your account';
			return $res;
		}
		// get the username of the current user
		$username = self::getUserLogged('username');
		// check whether the Duckiebot exists
		if( !self::duckiebotExists($bot_name) ){
			$res['data'] = sprintf('Duckiebot `%s` not found', $bot_name);
			return $res;
		}
		// check whether the user is already linked to a Duckiebot
		$res2 = self::getDuckiebotLinkedToUser($username);
		if( !$res2['success'] ){
			return $res2;
		}
		if( !is_null($res2['data']) ){
			$res['data'] = sprintf('The user account `%s` is already linked to a Duckiebot. Release it first.', $username);
			return $res;
		}
		// check whether the Duckiebot is already linked to another account
		$res2 = self::getUserLinkedToDuckiebot($bot_name);
		if( !$res2['success'] ){
			return $res2;
		}
		if( !is_null($res2['data']) ){
			$res['data'] = sprintf('The Duckiebot `%s` is already linked to a user account.', $bot_name);
			return $res;
		}
		// check whether the user exists, if it does not, create a new one
		$user_exists = self::userExists($username);
		if( !$user_exists ){
			$user_file = sprintf( __DIR__.'/../users/accounts/%s.json', $username );
			$user_info = new JsonDB( $user_file );
			// copy info to JSON
			foreach( self::getUserLogged() as $key => $val ){
				$user_info.set( $key, $val );
			}
			$user_info.set( 'role', 'user' );
			$res2 = $user_info.commit();
			if( !$res2['success'] ){
				return $res2;
			}
		}
		// link Duckiebot to user account
		$associations_dir = __DIR__."/../users/associations";
		// create command
		$cmd = sprintf("touch %s/%s.%s", $associations_dir, $username, $bot_name);
		// execute the command and parse the output
		$output = [];
		$retval = -100;
		exec( $cmd, $output, $retval );
		if( $retval != 0 ){
			$res['data'] = array_pop($output);
			return $res;
		}
		// update the info about the user within the system
		$_SESSION['USER_DUCKIEBOT'] = $bot_name;
		//
		$res['success'] = true;
		return $res;
	}//linkDuckiebotToUserAccount


	public static function unlinkDuckiebotFromUserAccount( $bot_name ){
		// get the user account this duckiebot is linked to
		$res = self::getUserLinkedToDuckiebot($bot_name);
		if( !$res['success'] ){
			return $res;
		}
		$username = $res['data'];
		// remove the association flag
		$associations_dir = __DIR__."/../users/associations";
		// create command
		$cmd = sprintf("rm -f %s/%s.%s", $associations_dir, $username, $bot_name);
		// execute the command and parse the output
		$output = [];
		$retval = -100;
		exec( $cmd, $output, $retval );
		if( $retval != 0 ){
			return array('success' => false, 'data' => array_pop($output));
		}
		// update the info about the user within the system
		unset( $_SESSION['USER_DUCKIEBOT'] );
		//
		return array('success' => true, 'data' => null);
	}//unlinkDuckiebotFromUserAccount




	// =================================================================================================================
	// 4. Utility functions

	public static function redirectTo( $resource ){
		echo '<script type="text/javascript">window.open("'.( ( substr($resource,0,4) == 'http' )? '' : Configuration::$BASE ).$resource.'","_top");</script>';
		die();
		exit;
	}//redirectTo

	public static function throwError( $errorMsg ){
		$_SESSION['_ERROR_PAGE_MESSAGE'] = $errorMsg;
		//
		self::redirectTo( 'error' );
	}//throwError

	public static function sendEMail($to, $subject, $template, $replace, $replyTo=null){
		// prepare the message body
		$res = EmailTemplates::fill( $template, $replace );
		if( !$res['success'] ){
			return $res;
		}
		$body = $res['data'];
		// create the mail object
		$mail = new \PHPMailer();
		//
		$mail->isSMTP();                                      				// Set mailer to use SMTP
		$mail->Host = Configuration::$NOREPLY_MAIL_HOST;	  				// Specify main and backup SMTP servers
		$mail->SMTPAuth = Configuration::$NOREPLY_MAIL_AUTH;  				// Enable SMTP authentication
		$mail->Username = Configuration::$NOREPLY_MAIL_USERNAME;           	// SMTP username
		$mail->Password = Configuration::$NOREPLY_MAIL_PASSWORD;      		// SMTP password
		if( !in_array( Configuration::$NOREPLY_MAIL_SECURE_PROTOCOL, array('', 'none') ) ){
			$mail->SMTPSecure = Configuration::$NOREPLY_MAIL_SECURE_PROTOCOL;  	// Enable TLS encryption, `ssl` also accepted
		}
		$mail->Port = Configuration::$NOREPLY_MAIL_SERVER_PORT;
		//
		$mail->From = Configuration::$NOREPLY_MAIL_ADDRESS;
		$mail->FromName = Configuration::$SHORT_SITE_NAME;
		$mail->addAddress( $to );     										// Add a recipient
		//
		if( $replyTo !== null ){
			$mail->addReplyTo( $replyTo['email'], $replyTo['name'] );
		}
		//
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    			// Add an Attachment
		$mail->isHTML(true);                                  				// Set email format to HTML
		//
		$mail->Subject = $subject;
		$mail->Body = $body;
		//
		if(!$mail->send()) {
			return array( 'success' => false, 'data' => $mail->ErrorInfo );
		} else {
			return array( 'success' => true, 'data' => null );
		}
	}//sendEMail

	public static function isAlphabetic( $string, $length=null ){
		return ( preg_match(self::$regexes['alphabetic'], $string) == 1 ) && ( ($length == null)? true : ($length==strlen($string)) );
	}//isAlphabetic

	public static function isNumeric( $string, $length=null ){
		return ( preg_match(self::$regexes['numeric'], $string) == 1 ) && ( ($length == null)? true : ($length==strlen($string)) );
	}//isNumeric

	public static function isAlphaNumeric( $string, $length=null ){
		return ( preg_match(self::$regexes['alphanumeric'], $string) == 1 ) && ( ($length == null)? true : ($length==strlen($string)) );
	}//isAlphaNumeric

	public static function isAvalidEmailAddress( $string, $length=null ){
		return ( preg_match(self::$regexes['email'], $string) == 1 ) && ( ($length == null)? true : ($length==strlen($string)) );
	}//isAvalidEmailAddress

	public static function hash_password( $plain_password ){
		// create a seed by removing the characters in odd positions from the password
		$seed = "";
		foreach(range($plain_password, strlen($plain_password)-1, 2) as $i){
			$seed .= $plain_password[$i];
		}
		// hash the seed using MD5 and take the first 22 characters, this will be the salt for bcrypt
		$salt = substr( md5($seed), 0, 22 );
		// hash the password using bcrypt, a cost of 10, 10000 iterations, and the given salt
		$hash = password_hash($plain_password, PASSWORD_BCRYPT, ['salt'=>$salt]);
		// return hashed password
		return $hash;
	}//hash_password

	public static function collectErrorInformation( $errorData ){
		//TODO: implement a logging system here
	}//collectErrorInformation


	// =================================================================================================================
	// =================================================================================================================
	//
	//
	// Private functions


	private static function generateRandomString( $length ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = mb_strlen($chars);
		//
		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}
		return $result;
	}//generateRandomString

	private static function clearCacheGroups( $keywords ){
		if( is_string($keywords) ) $keywords = array($keywords);
		//
		foreach( $keywords as $keyword ){
			$groupID = md5($keyword);
			//
			foreach( $_SESSION['CACHE_GROUPS'][$groupID] as $qID ){
				self::$cache->delete( $qID );
			}
			//
			unset( $_SESSION['CACHE_GROUPS'][$groupID] );
		}
	}//clearCacheGroups

	private static function regenerateSessionID( $delete_old_session = false ){
		session_regenerate_id( $delete_old_session );
	}//regenerateSessionID

	private static function toAssociativeArray( $data, $key, $target=null ){
		$res = array();
		//
		foreach( $data as $elem ){
			$res[$elem[$key]] = ( ($target==null)? $elem : $elem[$target] );
			if( $target == null ){
				unset( $res[$elem[$key]][$key] );
			}
		}
		//
		return $res;
	}//toAssociativeArray

	public static function _getGMTOffset(){
		$now = new \DateTime();
		$mins = $now->getOffset() / 60;
		$sgn = ($mins < 0 ? -1 : 1);
		$mins = abs($mins);
		$hrs = floor($mins / 60);
		$mins -= $hrs * 60;
		$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
		//
		return $offset;
	}//_getGMTOffset

	private static function _regex_extract_group($string, $pattern, $groupNum){
	    preg_match_all($pattern, $string, $matches);
	    return $matches[$groupNum][0];
	}//_regex_extract_group

	private static function _load_available_pages(){
		$pages_descriptors = __DIR__."/../pages/*/setup.json";
		$jsons = glob( $pages_descriptors );
		//
		$pages = [
			'list' => [],
			'by-id' => [],
			'by-package' => [],
			'by-usertype' => [
				'administrator' => [],
				'supervisor' => [],
				'user' => [],
				'candidate' => [],
				'guest' => []
			],
			'by-menuorder' => [],
			'by-responsive-priority' => []
		];
		foreach ($jsons as $json) {
			$page_id = self::_regex_extract_group($json, "/.*pages\/(.+)\/setup.json/", 1);
			$page = json_decode( file_get_contents($json), true );
			$page['id'] = $page_id;
			// list
			array_push( $pages['list'], $page );
			// by-id
			$pages['by-id'][$page_id] = $page;
			// by-package
			$package = $page['package'];
			if( !array_key_exists($package, $pages['by-package']) ) $pages['by-package'][$package] = [];
			array_push( $pages['by-package'][$package], $page );
			// by-usertype
			foreach ($page['access'] as $access) {
				array_push( $pages['by-usertype'][$access], $page );
			}
		}
		// by-menuorder
		$menuorder = array_filter($pages['list'], function($e){ return !is_null($e['menu_entry']); } );
		usort($menuorder, function($a, $b){
			return ($a['menu_entry']['order'] < $b['menu_entry']['order'])? -1 : 1;
		});
		$pages['by-menuorder'] = $menuorder;
		// by-responsive-priority
		$responsive_priority = array_filter($pages['list'], function($e){ return !is_null($e['menu_entry']); } );
		usort($responsive_priority, function($a, $b){
			return ($a['menu_entry']['responsive']['priority'] < $b['menu_entry']['responsive']['priority'])? -1 : 1;
		});
		$pages['by-responsive-priority'] = $responsive_priority;
		//
		return $pages;
	}//_load_available_pages

}

?>

<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 11/18/14
 * Time: 4:15 PM
 */

namespace system\classes;

use \phpfastcache;
use system\classes\enum\EmailTemplates;
use system\classes\enum\StringType;


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

class Core{
	/**
	 * Construct won't be called inside this class and is uncallable from
	 * the outside. This prevents instantiating this class.
	 * This is by purpose, because we want a static class.
	 */

	private static $initialized = false;

	// Fields
	public static $mysql;
	private static $cache = null;

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
			//
			$error = false;
			$error_msg = null;
			//init configuration
			$res = Configuration::init();
			if( !$res['success'] ){
				return $res;
			}
			//
			EmailTemplates::init();
			//
			try{
				self::$mysql = new \mysqli(Configuration::$MYSQL_HOST, Configuration::$MYSQL_USERNAME, Configuration::$MYSQL_PASSWORD, Configuration::$MYSQL_DBNAME);
				self::$mysql->query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

				self::$mysql->query('SET time_zone = \''.self::_getGMTOffset().'\'');
				//
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
			if( self::$mysql->connect_errno ){
				$error = true;
				$error_msg = "Failed to connect to MySQL: (" . self::$mysql->connect_errno . ") " . self::$mysql->connect_error;
			}
			if( !$error ){
				self::$initialized = true;
				return array( 'success' => true, 'data' => null );
			}else{
				return array( 'success' => false, 'data' => $error_msg );
			}
		}else{
			return array( 'success' => true, 'data' => "Kernel già inizializzato!" );
		}
	}//initCore


	public static function close(){
		try{
			self::$mysql->close();
		}catch(\Exception $e){
			return array( 'success' => false, 'data' => null );
		}
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


	public static function logInAdministrator( $username, $password, $recoveryMode = false ){
		if( $_SESSION['ADMIN_LOGGED'] ){
			return array( 'success' => false, 'data' => 'Amministratore già loggato!' );
		}
		//
		if( !$recoveryMode ){
			$query = "SELECT * FROM Administrator WHERE username='".self::$mysql->real_escape_string($username)."' AND password='".self::$mysql->real_escape_string($password)."'";
		}else{
			$query = "SELECT * FROM Administrator WHERE username='".self::$mysql->real_escape_string($username)."' AND tempPassword='".self::$mysql->real_escape_string($password)."'";
		}
		//
		$res = self::execSELECT( $query );
		if( $res['success'] ){
			if( sizeof($res['data']) > 0 ){
				//login correct
				// remove the password
				unset( $res['data'][0]['password'] );
				// store the result
				$_SESSION['ADMIN_LOGGED'] = true;
				$_SESSION['ADMIN_RECORD'] = $res['data'][0];
				self::regenerateSessionID();
				return $res;
			}else{
				return array( 'success' => false, 'data' => 'Amministratore "'.$username.'" non trovato!' );
			}
		}else{
			return $res;
		}
	}//logInAdministrator


	public static function isAdministratorLoggedIn(){
		return ( isset($_SESSION['ADMIN_LOGGED'])? $_SESSION['ADMIN_LOGGED'] : false );
	}//isAdministratorLoggedIn


	public static function logOutAdministrator(){
		if( !$_SESSION['ADMIN_LOGGED'] ){
			return array( 'success' => false, 'data' => 'Amministratore non ancora loggato!' );
		}
		//
		$_SESSION['ADMIN_LOGGED'] = false;
		unset( $_SESSION['ADMIN_RECORD'] );
		self::regenerateSessionID();
		//
		return true;
	}//logOutAdministrator



	// =================================================================================================================
	// 2. Getter functions

	public static function getAdministratorLastSeen( $username ){
		$query = "SELECT lastSeen FROM Administrator WHERE username='" . self::escape_string($username) . "'";
		//
		$res = self::execSELECT( $query );
		//
		return $res;
	}//getAdministratorLastSeen


	public static function getAdministratorInfoAuth( $username, $password ){
		$query = "SELECT * FROM Administrator WHERE username='".self::$mysql->real_escape_string($username)."' AND password='".self::$mysql->real_escape_string($password)."'";
		//
		$res = self::execSELECT( $query );
		//remove the password
		if( $res['size'] > 0 ){
			unset( $res['data'][0]['password'] );
		}
		//
		return $res;
	}//getAdministratorInfoAuth


	public static function getAdministratorInfoNoAuth( $username, $keepPassword=false ){
		$query = "SELECT * FROM Administrator WHERE username='".self::$mysql->real_escape_string($username)."'";
		//
		$res = self::execSELECT( $query );
		//remove the password
		if( !$keepPassword && $res['size'] > 0 ){
			unset( $res['data'][0]['password'] );
		}
		//
		return $res;
	}//getAdministratorInfoNoAuth


	public static function getAdministratorLogged( $field=null ){
		return (isset($_SESSION['ADMIN_RECORD'])) ? ( ($field==null) ? $_SESSION['ADMIN_RECORD'] : $_SESSION['ADMIN_RECORD'][$field] ) : null;
	}//getAdministratorLogged


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

	public static function getNewsList($orderBy = 'newsID', $orderWay = 'ASC', $offset = 0, $limit = PHP_INT_MAX){
		$query = "SELECT SQL_CALC_FOUND_ROWS newsID, date, title, writer FROM News WHERE 1 ORDER BY ".self::$mysql->real_escape_string($orderBy)." ".self::$mysql->real_escape_string($orderWay)." LIMIT ".self::$mysql->real_escape_string($offset).",".self::$mysql->real_escape_string($limit);
		//
		$res = self::intelliSELECT( $query, 'News' );
		//
		if( $res['success'] ){
			$res['total'] = $res['size'];
			$res2 = self::intelliSELECT( 'SELECT FOUND_ROWS() as total; /* '.$query.' */', 'News' );
			if( $res2['success'] ){
				$res['total'] = $res2['data'][0]['total'];
			}
		}
		//
		return $res;
	}//getNewsList

	public static function getNewsDetails($newsID){
		$query = "SELECT * FROM News WHERE newsID='".self::$mysql->real_escape_string($newsID)."'";
		//
		$res = self::intelliSELECT( $query, 'News' );
		//
		return $res;
	}//getNewsDetails


	public static function getAdministratorMessageList($subject=null, $read=null, $orderBy = 'creationTime', $orderWay = 'DESC', $offset = 0, $limit = PHP_INT_MAX){
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM AdministratorMessage WHERE 1 ". ( ($subject !== null)? ' AND subject=\''.self::escape_string($subject).'\'' : '' ) . ( ($read !== null)? ' AND `read`='.intval($read) : '' ) ." ORDER BY `".self::escape_string($orderBy)."` ".self::escape_string($orderWay).", creationTime DESC LIMIT ".self::escape_string($offset).",".self::escape_string($limit);
		//
		$res = self::intelliSELECT( $query, 'AdministratorMessage' );
		//
		if( $res['success'] ){
			$res['total'] = $res['size'];
			$res2 = self::intelliSELECT( 'SELECT FOUND_ROWS() as total; /* '.$query.' */', 'AdministratorMessage' );
			if( $res2['success'] ){
				$res['total'] = $res2['data'][0]['total'];
			}
		}
		//
		return $res;
	}//getAdministratorMessageList

	public static function getAdministratorMessage($messageID){
		$query = "SELECT * FROM AdministratorMessage WHERE messageID=".self::escape_string($messageID);
		//
		$res = self::intelliSELECT( $query, 'AdministratorMessage' );
		//
		return $res;
	}//getAdministratorMessage


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
			$ram_total = sprintf("%d GB", (int)($ram_total_float / 1000.0));
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
			exec( "ls -l '".$day_path."' | grep -E '[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}\.dat' | awk '{print $9}' | sort", $day_activities, $exit_code );
			$total_recording_this_day = $chunk_len * sizeof( $day_activities );
			$result['days'][ $day ] = array(
				'chunks' => array(),
				'total_minutes' => $total_recording_this_day
			);
			$result['total_minutes'] += $total_recording_this_day;
			foreach( $day_activities as $chunk ){
				$chunk = self::_regex_extract_group($chunk, "/[0-9]{4}-[0-9]{2}-[0-9]{2}_([0-9]{2}\.[0-9]{2})\.dat/", 1);
				array_push( $result['days'][ $day ][ 'chunks' ], $chunk );
			}
		}
		return $result;
	}//getSurveillancePostProcessingHistory


	public static function getDuckiebotsCurrentBranch(){
		exec( "ls -l '".Configuration::$DUCKIEFLEET_PATH.'/robots/'.Configuration::$DUCKIEFLEET_BRANCH."' | awk '{print $9}' | grep -E '[a-zA-Z0-9]*.robot.yaml' | sed -e 's/\.robot.yaml$//'", $duckiebots, $exit_code );
		//
		return $duckiebots;
	}//getDuckiebotsCurrentBranch


	public static function getDuckiebotOwner( $bot_name ){
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

	public static function getDuckiebotIPAddress( $bot_name ){
		exec( "ping -c 1 ".$bot_name.".local", $_, $exit_code );
		$is_online = booleanval( $exit_code == 0 );
		//
		return $is_online;
	}//isDuckiebotOnline


	public static function isDuckiebotOnline( $bot_name ){
		exec( "ping -c 1 ".$bot_name.".local", $_, $exit_code );
		$is_online = booleanval( $exit_code == 0 );
		//
		return $is_online;
	}//isDuckiebotOnline













	// =================================================================================================================
	// 3. Setter functions


	public static function editPersonalAdministratorInformation($administrator, $administratorInfo){
		array_assoc_filter( $administratorInfo , array('name', 'surname', 'email') );
		//
		if( sizeof($administratorInfo) <= 0 ){
			return array( 'success' => true, 'data' => 'Niente da modificare' );
		}
		//
		$update = self::arrayToUpdateQueryString( $administratorInfo );
		//
		$query = 'UPDATE Administrator SET '.$update.' WHERE username=\'' . self::escape_string($administrator) .'\'';
		//
		$res = self::execUPDATE( $query );
		//
		if( $res['success'] ){
			// update local information
			$res2 = Core::getAdministratorInfoNoAuth( $administrator );
			$_SESSION['ADMIN_RECORD'] = $res2['data'][0];
			// clear the cache
			self::clearCacheGroups( 'Administrator' );
		}
		//
		return $res;
	}//editPersonalAdministratorInformation

	public static function editSecurityAdministratorInformation($administrator, $adminInfo){
		$query = "UPDATE Administrator SET
					password = '". md5($adminInfo['password']) ."'
				 WHERE
				 	username = '".self::escape_string($administrator)."'";
		//
		$res = self::execUPDATE( $query );
		//
		if( $res['success'] ){
			// clear the cache
			self::clearCacheGroups( 'Administrator' );
		}
		//
		return $res;
	}//editSecurityAdministratorInformation


	public static function addNews($newsData){
		$query = 'INSERT INTO News(
					newsID,
					date,
					title,
					writer,
					content
				) VALUES (
					DEFAULT,
					NOW(),
					\''. self::escape_string($newsData['title']) .'\',
					\''. self::escape_string($newsData['writer']) .'\',
					\''. self::escape_string($newsData['content']) .'\'
				)';
		//
		$res = self::execINSERT( $query );
		//
		if( $res['success'] ){
			// clear the cache
			self::clearCacheGroups( 'News' );
		}
		//
		return $res;
	}//addNews

	public static function editNews($newsID, $newsData){
		array_assoc_filter( $newsData , array('date', 'title', 'writer', 'content') );
		//
		if( sizeof($newsData) <= 0 ){
			return array( 'success' => true, 'data' => 'Niente da modificare' );
		}
		//
		$update = self::arrayToUpdateQueryString( $newsData );
		//
		$query = 'UPDATE News SET '.$update.' WHERE newsID=\''. self::escape_string($newsID).'\'';
		//
		$res = self::execUPDATE( $query );
		//
		if( $res['success'] ){
			// clear the cache
			self::clearCacheGroups( 'News' );
		}
		//
		return $res;
	}//editNews

	public static function removeNews($newsID){
		$query = 'DELETE FROM News WHERE newsID=\''.self::escape_string($newsID).'\'';
		//
		$res = self::execDELETE( $query );
		//
		if( $res['success'] ){
			// clear the cache
			self::clearCacheGroups( 'News' );
		}
		//
		return $res;
	}//removeNews

	public static function setAdministratorLastSeen( $username, $timestamp=null ){
		$timestamp = ( ($timestamp == null)? 'UNIX_TIMESTAMP()' : $timestamp );
		$query = "UPDATE Administrator SET lastSeen=" . $timestamp . " WHERE username='" . self::escape_string($username) . "'";
		//
		$res = self::execUPDATE( $query );
		//
		if( $res['success'] ){
			// clear the cache
			self::clearCacheGroups( 'Administrator' );
		}
		//
		return $res;
	}//setAdministratorLastSeen

	public static function generateAdministratorTemporaryPassword($administrator){
		$tempPwd = self::generateRandomString(8);
		$update = 'tempPassword=\''. md5( $tempPwd ) . '\'';
		//
		$query = 'UPDATE Administrator SET '.$update.' WHERE username=\'' . self::escape_string($administrator) . '\'';
		//
		$res = self::execUPDATE( $query );
		if( $res['success'] ){
			$res['data'] = $tempPwd;
			// clear the cache
			self::clearCacheGroups( 'Administrator' );
		}
		//
		return $res;
	}//generateAdministratorTemporaryPassword


	public static function collectContactRequest( $contactData ){
		$query = 'INSERT INTO AdministratorMessage(
					messageID,
					sender,
					subject,
					message,
					creationTime,
					phone,
					email,
					`read`
				) VALUES (
					DEFAULT,
					\''. self::escape_string($contactData['sender']) .'\',
					\''. self::escape_string($contactData['subject']) .'\',
					\''. self::escape_string($contactData['message']) .'\',
					NOW(),
					\''. self::escape_string($contactData['phone']) .'\',
					\''. self::escape_string($contactData['email']) .'\',
					FALSE
				)';
		//
		$res = self::execINSERT( $query );
		//
		if( $res['success'] ){
			// clear the cache
			self::clearCacheGroups( 'AdministratorMessage' );
		}
		//
		return $res;
	}//collectContactRequest

	public static function markContactRequestAsRead( $messageID ){
		$query = 'UPDATE AdministratorMessage SET `read`=TRUE WHERE messageID=\''. self::escape_string($messageID) .'\'';
		//
		$res = self::execUPDATE( $query );
		//
		if( $res['success'] ){
			// clear the cache
			self::clearCacheGroups( 'AdministratorMessage' );
		}
		//
		return $res;
	}//markContactRequestAsRead


	// =================================================================================================================
	// 4.Metodi di Utilità

	public static function redirectTo( $resource ){
		echo '<script type="text/javascript">window.open("'.( ( substr($resource,0,4) == 'http' )? '' : Configuration::$PLATFORM_BASE ).$resource.'","_top");</script>';
		die();
		exit;
	}//redirectTo

	public static function throwError( $errorMsg ){
		$_SESSION[Configuration::$PLATFORM.'_ERROR_PAGE_MESSAGE'] = $errorMsg;
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

	public static function escape_string( $string ){
		return self::$mysql->real_escape_string( $string );
	}//escape_string

	public static function collectErrorInformation( $errorData ){
		$query = 'INSERT INTO ComplaintBox(
					`id`,
					`date`,
					`platform`,
					`error`
				) VALUES (
					DEFAULT,
					UNIX_TIMESTAMP(NOW()),
					\''. Configuration::$PLATFORM .'\',
					\''. self::escape_string(serialize( $errorData )) .'\'
				)';
		//
		self::execINSERT( $query );
	}//collectErrorInformation


	// =================================================================================================================
	// =================================================================================================================
	//
	//
	// FUNZIONI PRIVATE


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

	private static function intelliSELECT( $query, $keywords=null ) {
		$until_midnight = strtotime("tomorrow 00:00:00")-time();
		$res = self::_intelli_select( $query, $keywords );
		//
		if( Configuration::$CACHE_ENABLED ){
			// update statistics
			if( self::$cache->isExisting('STATS_TOTAL_SELECT_REQS') ){
				self::$cache->set('STATS_TOTAL_SELECT_REQS', self::$cache->get('STATS_TOTAL_SELECT_REQS')+1, $until_midnight);
			}else{
				self::$cache->set('STATS_TOTAL_SELECT_REQS', 1, $until_midnight );
			}
			//
			if( $res['cached']===true ){
				if( self::$cache->isExisting('STATS_CACHED_SELECT_REQS') ){
					self::$cache->set('STATS_CACHED_SELECT_REQS', self::$cache->get('STATS_CACHED_SELECT_REQS')+1, $until_midnight);
				}else{
					self::$cache->set( 'STATS_TOTAL_SELECT_REQS' , 1, $until_midnight );
					self::$cache->set( 'STATS_CACHED_SELECT_REQS' , 1, $until_midnight );
				}
			}
		}
		//
		return $res;
	}//intelliSELECT

	private static function _intelli_select( $query, $keywords=null ) {
		Configuration::$CACHE_ENABLED = ( self::$cache !== null && self::$cache instanceof phpFastCache );
		//
		if( Configuration::$CACHE_ENABLED ){
			$queryID = md5($query);
			// cache enabled
			$uptodate = true;
			if( is_string($keywords) ) $keywords = array($keywords);
			//
			if( is_array($keywords) ){
				// advanced group-based caching mode
				foreach( $keywords as $keyword ){
					$groupID = md5($keyword);
					if( !is_array($_SESSION['CACHE_GROUPS'][$groupID]) || !in_array( $queryID, $_SESSION['CACHE_GROUPS'][$groupID] ) ){
						$uptodate = false;
					}
				}
			}else{ /* default (updates-blind) caching mode */ }
			//
			if( $uptodate == true ){
				// read from cache
				$res = self::$cache->get( $queryID );
				if( $res == null ){
					// no results found
					$res = self::execSELECT( $query );
					if( $res['success'] ){
						// set query result into the cache for 600 seconds = 10 minutes
						self::$cache->set( $queryID , serialize($res) , 600 );
					}else{ return $res; }
				}else{
					// a cached value will be returned
					$res = unserialize( $res );
					$res['cached'] = true;
				}
			}else{
				// exec the query
				$res = self::execSELECT( $query );
				if( $res['success'] ){
					// set query result into the cache for 600 seconds = 10 minutes
					self::$cache->set( $queryID , serialize($res) , 600 );
				}else{ return $res; }
				//
				foreach( $keywords as $keyword ){
					$groupID = md5($keyword);
					if( !isset($_SESSION['CACHE_GROUPS'][$groupID]) || !is_array($_SESSION['CACHE_GROUPS'][$groupID]) ){
						$_SESSION['CACHE_GROUPS'][$groupID] = array();
					}
					//
					if( !in_array($queryID, $_SESSION['CACHE_GROUPS'][$groupID]) ){
						array_push( $_SESSION['CACHE_GROUPS'][$groupID], $queryID );
					}
				}
			}
			//
			return $res;
		}else{
			// cache not enabled, use the database
			$res = self::execSELECT( $query );
			return $res;
		}
	}//_intelli_select


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


	private static function execSELECT( $query ){
		try{
			$res = self::$mysql->query( $query );
			if( $res instanceof \mysqli_result ){
				$array = array();
				$i = 0;
				while( $row = $res->fetch_assoc() ){
					$array[$i] = $row;
					$i++;
				}
				return array( 'success'=>true, 'size'=>$i, 'data'=>$array );
			}else{
				return array( 'success'=>false, 'data'=>self::$mysql->error );
			}
		}catch(Exception $e){
			return array( 'success'=>false, 'data'=>self::$mysql->error );
		}
	}//execSELECT


	private static function execUPDATE( $query ){
		try{
			$res = self::$mysql->query( $query );
			if( $res ){
				return array( 'success'=>true, 'data'=>null );
			}else{
				return array( 'success'=>false, 'data'=>self::$mysql->error );
			}
		}catch(Exception $e){
			return array( 'success'=>false, 'data'=>self::$mysql->error );
		}
	}//execUPDATE

	public static function execINSERT( $query ){ //TODO: private (parse_insert purpose)
		$res = self::execUPDATE($query);
		if( $res['success'] ){
			$res['insertID'] = self::$mysql->insert_id;
		}
		return $res;
	}//execINSERT

	private static function execDELETE( $query ){
		return self::execUPDATE($query);
	}//execDELETE

	private static function regenerateSessionID( $delete_old_session = false ){
		session_regenerate_id( $delete_old_session );
	}//regenerateSessionID

	private static function arrayToUpdateQueryString( $array ){
		$arr = array();
		foreach( $array as $key => $value ){
			array_push( $arr, self::_glue( $key, $value ) );
		}
		//
		return implode( ', ', $arr );
	}//arrayToUpdateQueryString

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

	private static function _glue($key, $value){
		return '`'.$key.'`=\''. self::escape_string($value) .'\'';
	}//_glue

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
	}
}

?>

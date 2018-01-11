<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Sunday, December 31st 2017
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018



namespace system\packages\duckietown;

require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Core.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Configuration.php';

use \system\classes\Core;
use \system\classes\Configuration;

/**
*   Module for managing entities in Duckietown (e.g., Duckietown, Duckiebots)
*/
class Duckietown{

	private static $initialized = false;

	// disable the constructor
	private function __construct() {}

    /** Initializes the module.
     *
     *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
     */
	public static function init(){
		if( !self::$initialized ){
			// do stuff
			return array( 'success' => true, 'data' => null );
		}else{
			return array( 'success' => true, 'data' => "Module already initialized!" );
		}
	}//init


    /** Safely terminates the module.
     *
     *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
     */
	public static function close(){
        // do stuff
		return array( 'success' => true, 'data' => null );
	}//close














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






}//Duckietown

?>

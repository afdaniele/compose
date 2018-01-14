<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Saturday, January 13th 2018

namespace system\classes;

require_once __DIR__.'/../environment.php';

// booleanval function
require_once __DIR__.'/libs/booleanval.php';
// structure
require_once __DIR__.'/Configuration.php';
require_once __DIR__.'/Utils.php';
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

	private static $packages = null;
	private static $pages = null;
	private static $api = null;

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




	// =======================================================================================================
	// Initilization and session management functions

	public static function initCore(){
		if( !self::$initialized ){
			mb_internal_encoding("UTF-8");
			// init configuration
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
			// load list of available packages
			self::$packages = self::_load_available_packages();
			// load list of available pages
			self::$pages = self::_load_available_pages();
			// load list of available API services
			self::$api = self::_load_API_setup();
			// initialize all the packages
			foreach( self::$packages as $pkg ){
				if( !is_null($pkg['core']) ){
					require_once $pkg['core'];
					$php_init_command = sprintf( "\system\packages\%s\%s::init();", $pkg['id'], ucfirst($pkg['id']) );
					eval( $php_init_command );
				}
			}
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




	// =======================================================================================================
	// Users management functions

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
		return $user_role;
	}//getUserRole


	public static function setUserRole( $user_role ){
		$_SESSION['USER_RECORD']['role'] = $user_role;
	}//setUserRole



	// =======================================================================================================
	// Packages management functions

	/** Returns the list of packages installed on the platform.
	 *
	 *	@retval array
	 * 		an associative array of the form
	 *	<pre><code class="php">[
	 *		"package_id" => [
	 *			"id" : string, 					// ID of the package
	 *			"name" : string,				// name of the package
     *			"description" : string,			// brief description of the package
     *			"dependencies" : [
 	 *				"system-packages" : [],		// list of system packages required by the package
	 *				"packages" : []				// list of \compose\ packages required by the package
     *			],
	 *			"enabled" : boolean				// whether the package is enabled
	 *		],
	 *		... 								// other packages
	 *	]</code></pre>
	 */
	public static function getPackagesList(){
		return self::$packages;
	}//getPackagesList


	/** Returns whether the package specified is installed on the platform.
	 *
	 *	@param string $package
	 *		the name of the package to check.
	 *	@retval boolean
	 * 		whether the package exists.
	 */
	public static function packageExists( $package ){
		$package_meta = sprintf('%s%s/metadata.json', $GLOBALS['__PACKAGES__DIR__'], $package);
		return file_exists($package_meta);
	}//packageExists


	/** Returns whether the specified package is enabled.
	 *
	 *	If the package in not installed, `FALSE` will be returned.
	 *
	 *	@param string $package
	 *		the name of the package to check.
	 *	@retval boolean
	 *		whether the package is enabled.
	 */
	public static function isPackageEnabled( $package ){
		$package_disabled_flag = sprintf('%s%s/disabled.flag', $GLOBALS['__PACKAGES__DIR__'], $package);
		return !file_exists($package_disabled_flag);
	}//isPackageEnabled


	/** Enables a package installed on the platform.
	 *
	 *	If the package specified is not installed, the function reports a failure state.
	 *
	 *	@param string $package
	 *		the name of the package to enable.
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
	 */
	public static function enablePackage( $package ){
		$package_meta = sprintf('%s%s/metadata.json', $GLOBALS['__PACKAGES__DIR__'], $package);
		if( !file_exists($package_meta) ){
			return ['success' => false, 'data' => sprintf('The package "%s" does not exist', $package)];
		}
		$package_disabled_flag = sprintf('%s%s/disabled.flag', $GLOBALS['__PACKAGES__DIR__'], $package);
		if( file_exists($package_disabled_flag) ){
			$success = unlink( $package_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
	}//enablePackage


	/** Disables a package installed on the platform.
	 *
	 *	If the package specified is not installed, the function reports a failure state.
	 *
	 *	@param string $package
	 *		the name of the package to disable.
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
	 */
	public static function disablePackage( $package ){
		if( $package == 'core' )
			return ['success' => false, 'data' => 'The Core package cannot be disabled'];
		$package_meta = sprintf('%s%s/metadata.json', $GLOBALS['__PACKAGES__DIR__'], $package);
		if( !file_exists($package_meta) ){
			return ['success' => false, 'data' => sprintf('The package "%s" does not exist', $package)];
		}
		$package_disabled_flag = sprintf('%s%s/disabled.flag', $GLOBALS['__PACKAGES__DIR__'], $package);
		if( !file_exists($package_disabled_flag) ){
			$success = touch( $package_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
	}//disablePackage






	// =======================================================================================================
	// Pages management functions

	public static function getPagesList( $order=null ){
		if( is_null($order) || !isset(self::$pages[$order]) ){
			return self::$pages;
		}else{
			return self::$pages[$order];
		}
	}//getPagesList

	public static function getFilteredPagesList( $order='list', $enabledOnly=false, $accessibleBy=null ){
		$pages = array();
		$pages_collection = self::getPagesList($order);
		if( is_assoc($pages_collection) ){
			if( $order == 'by-id' ){
				// collection in which pages are organized in an associative array by-id
				foreach( $pages_collection as $key => $page ){
					if( $enabledOnly && !$page['enabled'] ) continue;
					if( !is_null($accessibleBy) && !in_array($accessibleBy, $page['access']) ) continue;
					//
					$pages[$key] = $page;
				}
				return $pages;
			}else{
				// collection in which pages are organized in sub-categories
				foreach( $pages_collection as $group_id => $pages_per_group ){
					$pages_this_group = [];
					foreach( $pages_per_group as $page ){
						if( $enabledOnly && !$page['enabled'] ) continue;
						if( !is_null($accessibleBy) && !in_array($accessibleBy, $page['access']) ) continue;
						//
						array_push( $pages_this_group, $page );
					}
					$pages[$group_id] = $pages_this_group;
				}
				return $pages;
			}
		}else{
			// collection in which pages are arranged in a sequence, no keys
			foreach( $pages_collection as $page ){
				if( $enabledOnly && !$page['enabled'] ) continue;
				if( !is_null($accessibleBy) && !in_array($accessibleBy, $page['access']) ) continue;
				//
				array_push( $pages, $page );
			}
			return $pages;
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


	/** Returns whether the page specified is installed on the platform as part of the package specified.
	 *
	 *	@param string $package
	 *		the name of the package the page to check belongs to.
	 *	@param string $page
	 *		the name of the page to check.
	 *	@retval boolean
	 * 		whether the page exists.
	 */
	public static function pageExists( $package, $page ){
		$page_meta = sprintf('%s%s/pages/%s/metadata.json', $GLOBALS['__PACKAGES__DIR__'], $package, $page);
		return file_exists($page_meta);
	}//pageExists


	/** Returns whether the specified page is enabled.
	 *
	 *	If the package in not installed, `FALSE` will be returned.
	 *
	 *	@param string $package
	 *		the name of the package the page to check belongs to.
	 *	@param string $page
	 *		the name of the page to check.
	 *	@retval boolean
	 *		whether the package is enabled.
	 */
	public static function isPageEnabled( $package, $page ){
		$page_disabled_flag = sprintf('%s%s/pages/%s/disabled.flag', $GLOBALS['__PACKAGES__DIR__'], $package, $page);
		return !file_exists($page_disabled_flag);
	}//isPageEnabled


	/** Enables a page installed on the platform as part of the given package.
	 *
	 *	If the package specified is not installed, the function reports a failure state.
	 *
	 *	@param string $package
	 *		the name of the package the page to enable belongs to..
	 *	@param string $page
	 *		the name of the page to enable.
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
	 */
	public static function enablePage( $package, $page ){
		$page_meta = sprintf('%s%s/pages/%s/metadata.json', $GLOBALS['__PACKAGES__DIR__'], $package, $page);
		if( !file_exists($page_meta) ){
			return ['success' => false, 'data' => sprintf('The page "%s.%s" does not exist', $package, $page)];
		}
		$page_disabled_flag = sprintf('%s%s/pages/%s/disabled.flag', $GLOBALS['__PACKAGES__DIR__'], $package, $page);
		if( file_exists($page_disabled_flag) ){
			$success = unlink( $page_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
	}//enablePage


	/** Disables a page installed on the platform as part of the given package.
	 *
	 *	If the package specified is not installed, the function reports a failure state.
	 *
	 *	@param string $package
	 *		the name of the package the page to disable belongs to..
	 *	@param string $page
	 *		the name of the page to disable.
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
	 */
	public static function disablePage( $package, $page ){
		if( $package == 'core' )
			return ['success' => false, 'data' => 'Core pages cannot be disabled'];
		$page_meta = sprintf('%s%s/pages/%s/metadata.json', $GLOBALS['__PACKAGES__DIR__'], $package, $page);
		if( !file_exists($page_meta) ){
			return ['success' => false, 'data' => sprintf('The page "%s.%s" does not exist', $package, $page)];
		}
		$page_disabled_flag = sprintf('%s%s/pages/%s/disabled.flag', $GLOBALS['__PACKAGES__DIR__'], $package, $page);
		if( !file_exists($page_disabled_flag) ){
			$success = touch( $page_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
	}//disablePage




	// =======================================================================================================
	// API management functions

	/*	TODO @todo Returns the list of API services installed on the platform.
	*/
	public static function getAPIsetup(){
		return self::$api;
	}//getAPIsetup




	// =======================================================================================================
	// Utility functions

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
			$hash = substr( $hash[0], 0, 7 );
		}
		//
		return $hash;
	}//getCodebaseHash


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


	public static function _load_API_setup(){
		$packages = self::getPackagesList();
		$packages_ids = array_keys( $packages );
		$api = [];
		//
		foreach( $packages_ids as $pkg_id ){
			$api_services_descriptors = sprintf("%s/../packages/%s/modules/api/*/api-services/specifications/*.json", __DIR__, $pkg_id);
			$jsons = glob( $api_services_descriptors );
			//
			foreach ($jsons as $json) {
				$api_version = self::_regex_extract_group($json, "/.*api\/(.+)\/api-services\/specifications\/(.+).json/", 1);
				$api_service_id = self::_regex_extract_group($json, "/.*api\/(.+)\/api-services\/specifications\/(.+).json/", 2);
				if( !isset($api[$api_version]) ){
					$api[$api_version] = [
						'services' => []
					];
				}
				//
				$api_services_path_regex = sprintf( "/(.+)\/specifications\/%s.json/", $api_service_id );
				$api_service_executor_path = sprintf(
					"%s/executors/%s.php",
					self::_regex_extract_group($json, $api_services_path_regex, 1),
					$api_service_id
				);
				//
				$api_service = json_decode( file_get_contents($json), true );
				$api_service['package'] = $pkg_id;
				$api_service['id'] = $api_service_id;
				$api_service['executor'] = $api_service_executor_path;
				$api_service['enabled'] = $packages[$pkg_id]['enabled'] && $api_service['enabled'];
				//
				$api[$api_version]['services'][$api_service_id] = $api_service;
			}
		}
		//
		return $api;
	}//_load_API_setup


	/*	Loads and returns the list of pages available in every package installed on the platform.
	*TODO: add return description
	*/
	private static function _load_available_pages(){
		$packages = self::getPackagesList();
		$packages_ids = array_keys( $packages );
		//
		$pages = [
			'list' => [],
			'by-id' => [],
			'by-package' => [],
			'by-usertype' => [],
			'by-menuorder' => [],
			'by-responsive-priority' => []
		];
		//
		foreach( $packages_ids as $pkg_id ){
			$pages_descriptors = sprintf("%s%s/pages/*/metadata.json", $GLOBALS['__PACKAGES__DIR__'], $pkg_id);
			$jsons = glob( $pages_descriptors );
			$pages['by-package'][$pkg_id] = [];
			//
			foreach ($jsons as $json) {
				$page_id = self::_regex_extract_group($json, "/.*pages\/(.+)\/metadata.json/", 1);
				$page_path = self::_regex_extract_group($json, "/(.+)\/metadata.json/", 1);
				$page = json_decode( file_get_contents($json), true );
				$page['package'] = $pkg_id;
				$page['id'] = $page_id;
				$page['path'] = $page_path;
				$page['enabled'] = $packages[$pkg_id]['enabled'] && self::isPageEnabled($pkg_id, $page_id);
				// list
				array_push( $pages['list'], $page );
				// by-id
				$pages['by-id'][$page_id] = $page;
				// by-package
				array_push( $pages['by-package'][$pkg_id], $page );
				// by-usertype
				foreach ($page['access'] as $access) {
					if( !isset($pages['by-usertype'][$access]) ) $pages['by-usertype'][$access] = [];
					array_push( $pages['by-usertype'][$access], $page );
				}
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


	private static function _load_available_packages(){
		$pkgs_descriptors = $GLOBALS['__PACKAGES__DIR__']."*/metadata.json";
		$jsons = glob( $pkgs_descriptors );
		//
		$pkgs = [];
		foreach ($jsons as $json) {
			$pkg_id = self::_regex_extract_group($json, "/.*packages\/(.+)\/metadata.json/", 1);
			$pkg_path = self::_regex_extract_group($json, "/(.+)\/metadata.json/", 1);
			$pkg = json_decode( file_get_contents($json), true );
			$pkg['id'] = $pkg_id;
			$pkg_core_file = sprintf( "%s/%s.php", $pkg_path, ucfirst($pkg_id) );
			$pkg['core'] = ( file_exists($pkg_core_file) )? $pkg_core_file : null;
			$pkg['enabled'] = self::isPackageEnabled($pkg_id);
			// by-id
			$pkgs[$pkg_id] = $pkg;
		}
		//
		return $pkgs;
	}//_load_available_packages


}

?>

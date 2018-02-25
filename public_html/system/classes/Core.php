<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

namespace system\classes;

require_once __DIR__.'/../environment.php';

// booleanval function
require_once __DIR__.'/libs/booleanval.php';
// structure
require_once __DIR__.'/Configuration.php';
require_once __DIR__.'/EditableConfiguration.php';
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

/** Core module of the platform <b>\\compose\\</b>.
 */
class Core{

	private static $initialized = false;
	private static $cache = null;
	private static $packages = null;
	private static $pages = null;
	private static $api = null;
	private static $settings = null;
	private static $registered_user_types = [];

	private static $STRING_TYPES_REGEX = [
		"alphabetic" => "/^[a-zA-Z]+$/",
		"alphanumeric" => "/^[a-zA-Z0-9]+$/",
		"alphanumeric_s" => "/^[a-zA-Z0-9\\s]+$/",
		"numeric" => "/^[0-9]+$/",
		"password" => "/^[a-zA-Z0-9_.-]+$/",
		"text" => "/^[\\w\\D\\s_.,-]*$/",
		"email" => "/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/"
	];

	private static $USER_ACCOUNT_TEMPLATE = [
		"username" => ["string", "Google (numeric) user ID"],
		"name" => ["string", "Full name of the user"],
		"email" => ["string", "Email address"],
		"picture" => ["string", "Link to google account picture (provided by Google Sign-In)"],
		"role" => ["string", "Access level of the user"],
		"active" => ["boolean", "Whether the user is allowed to login and use the platform"]
	];

	private static $PAGE_METADATA_TEMPLATE = [
		"name" => ["string", "Name of the page"],
		"package" => ["string", "ID of the package the page belongs to"],
		"menu_entry" => [
			"__type" => "associative_array",
			"__details" => "Associative array containing info about the menu entry for the page",
			"order" => ["float", "The order of the page on the top menu bar (smallest number = leftmost entry)"],
			"icon" => [
				"__type" => "associative_array",
				"__details" => "Associative array containing info about the menu entry for the page",
				"class" => ["string", "Class of the icon (e.g., glyphicon)"],
				"name" => ["string", "ID of the icon to use (e.g., car)"]
			],
			"responsive" => [
				"__type" => "associative_array",
				"__details" => "Associative array containing info about how the responsiveness of the menu entry for small devices",
				"priority" => ["float", "Priority with which the menu entry will be contracted (highest number = contracted first)"]
			],
			"exclude_roles" => [
				"__type" => "array",
				"__details" => "List of user roles for which this page icon should be hidden"
			]
		],
		"child_pages" => [
			"__type" => "array",
			"__details" => "The menu entry of the page will be highlighted if the current page matches the `name` of this class or any ID in this list",
			"__sample_item" => ["string", "ID of a child page"]
		],
		"access_level"  => [
			"__type" => "array",
			"__details" => "List of user roles for which this page is accessible",
			"__sample_item" => ["string", "User role to grand access to"]
		]
	];

	private static $PACKAGE_SETTINGS_METADATA_TEMPLATE = [
		"configuration_content" => [
			"__type" => "associative_array",
			"__details" => "Associative array containing (parameter_key, parameter_details) pairs for the package",
			"__sample_item" => [
				"__type" => "associative_array",
				"__details" => "Associative array containing (parameter_detail, parameter_value) pairs for the setting parameter",
				"__sample_key" => ["string", "Parameter key"],
				"__sample_value" => [
					"title" => ["string", "Name of the setting parameter"],
		            "type" => ["string", "Type of the parameter (e.g., string, boolean, integer)"],
		            "default" => ["_same_as_type", "Default value of this parameter"],
		            "details" => ["string", "A longer description of the parameter"]
				]
			]
		]
	];


	//Disable the constructor
	private function __construct() {}




	// =======================================================================================================
	// Initilization and session management functions


	/** Initializes the Core module.
	 *	It is the first function to call when using the Core module.
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function initCore(){
		if( !self::$initialized ){
			mb_internal_encoding("UTF-8");
			//
			// init configuration
			$res = Configuration::init();
			if( !$res['success'] ){
				return $res;
			}
			//
			// load list of available packages
			self::$packages = self::_load_available_packages();
			// load list of available pages
			self::$pages = self::_load_available_pages();
			// load list of available API services
			self::$api = self::_load_API_setup();
			// load package-specific settings
			self::$settings = self::_load_packages_settings();
			//
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
			//
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


	/** Terminates the Core module.
	 *	It is responsible for committing unsaved changes to the disk or closing open connections (e.g., mySQL)
	 *	before leaving.
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function close(){
		return array( 'success' => true, 'data' => null );
	}//close


	/** Creates a new PHP Session and assigns a new randomly generated 16-digits authorization token to it.
	 *
	 *	@retval boolean
	 *		`TRUE` if the function succeded, `FALSE` otherwise
	 */
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

	/** Logs in a user using the Google Sign-In OAuth 2.0 authentication procedure.
	 *
	 *	@param string $id_token
	 *		id_token returned by the Google Identity Sign-In tool,
	 *		(for more info check: https://developers.google.com/identity/sign-in/web/reference#gapiauth2authresponse);
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
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
				"active" => true
			];
			// look for a pre-existing user profile
			$user_exists = self::userExists($userid);
			if( $user_exists ){
				// there exists a user profile, load info
				$res = self::openUserInfo($userid);
				if( !$res['success'] ){
					return $res;
				}
				$user_info = $res['data']->asArray();
			}else{
				$res = self::createNewUserAccount($userid, $user_info);
				if( !$res['success'] ){
					return $res;
				}
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


	/** Creates a new user account.
	 *
	 *	@param string $user_id
	 *		string containing the (numeric) user id provided by Google Sign-In;
	 *
	 *	@param array $user_info
	 *		array containing information about the new user. This array
	 *		has to contain at least all the keys defined in $USER_ACCOUNT_TEMPLATE;
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function createNewUserAccount( $user_id, $user_info ){
		$user_exists = self::userExists($user_id);
		if( $user_exists ){
			return ['success' => false, 'data' => sprintf('The user `%s` already exists', $user_id) ];
		}
		// validate user info
		$mandatory_fields = array_keys( self::$USER_ACCOUNT_TEMPLATE );
		foreach( $mandatory_fields as $field) {
			if( !isset($user_info[$field]) ){
				return ['success' => false, 'data' => sprintf('The field "%s" is required for a new user account', $field)];
			}
		}
		// create a new user account on the server
		$user_file = sprintf( $GLOBALS['__SYSTEM__DIR__'].'/users/accounts/%s.json', $user_id );
		$user_obj = new JsonDB( $user_file );
		// copy info to jsonDB
		foreach( $user_info as $key => $val ){
			$user_obj->set( $key, $val );
		}
		// commit new user to the disk
		$res = $user_obj->commit();
		if( !$res['success'] ){
			return $res;
		}
		//
		return ['success' => true, 'data' => null ];
	}//createNewUserAccount


	/** Returns whether a user is currently logged in.
	 *
	 *	@retval boolean
	 *		whether a user is currently logged in;
	 */
	public static function isUserLoggedIn(){
		return ( isset($_SESSION['USER_LOGGED'])? $_SESSION['USER_LOGGED'] : false );
	}//isUserLoggedIn


	/** Returns the list of users registered on the platform.
	 *	A user is automatically registered when s/he logs in with google.
	 *
	 *	@retval array
	 *		list of user ids. The user id of a user is the numeric user id assigned by Google;
	 */
	public static function getUsersList(){
		$users_file_descriptors = sprintf("%s/users/accounts/*.json", $GLOBALS['__SYSTEM__DIR__']);
		$jsons = glob( $users_file_descriptors );
		//
		$users = [];
		foreach ($jsons as $json) {
			$user_id = self::_regex_extract_group($json, "/.*users\/accounts\/(.+).json/", 1);
			array_push( $users, $user_id );
		}
		return $users;
	}//getUsersList


	/** Logs out the user from the platform.
	 *	If the user is not logged in yet, the function will return an error status.
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function logOutUser(){
		if( !$_SESSION['USER_LOGGED'] ){
			return ['success' => false, 'data' => 'User not logged in yet!'];
		}
		//
		$_SESSION['USER_LOGGED'] = false;
		unset( $_SESSION['USER_RECORD'] );
		unset( $_SESSION['USER_DUCKIEBOT'] );
		self::regenerateSessionID();
		//
		return ['success' => true, 'data' => null];
	}//logOutUser


	/** Checks whether a user account exists.
	 *
	 *	@param string $user_id
	 *		string containing the (numeric) user id provided by Google Sign-In;
	 *
	 *	@retval boolean
	 *		whether a user account with the specified user id exists;
	 */
	public static function userExists( $user_id ){
		$user_file = sprintf( __DIR__.'/../users/accounts/%s.json', $user_id );
		return file_exists($user_file);
	}//userExists


	/** Opens the user account record for the user specified in write-mode.
	 *	This function returns an instance of the class \\system\\classes\\jsonDB\\JsonDB
	 *	containing the information about the user specified.
	 *
	 *	@param string $user_id
	 *		string containing the (numeric) user id provided by Google Sign-In;
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or instance of \\system\\classes\\jsonDB\\JsonDB
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`,
	 *		otherwise it will contain an instance of the class \\system\\classes\\jsonDB\\JsonDB
	 *		containing the information about the user specified.
	 *		The JsonDB object will contain at least the keys specified in $USER_ACCOUNT_TEMPLATE.
	 *		See the documentation for the class JsonDB to understand how to edit and commit information.
	 */
	public static function openUserInfo( $user_id ){
		$user_file = sprintf( __DIR__.'/../users/accounts/%s.json', $user_id );
		$user_info = null;
		if( file_exists($user_file) ){
			// load user information
			$user_info = new JsonDB( $user_file );
		}else{
			return array( 'success' => false, 'data' => 'User "'.$user_id.'" not found!' );
		}
		//
		$static_user_info = $user_info->asArray();
		$mandatory_fields = array_keys( self::$USER_ACCOUNT_TEMPLATE );
		foreach( $mandatory_fields as $field) {
			if( !isset($static_user_info[$field]) ){
				return array( 'success' => false, 'data' => 'The descriptor file for the user "'.$user_id.'" is corrupted! Contact the administrator' );
			}
		}
		if( strcmp($static_user_info['username'], $user_id) != 0 ){
			return array( 'success' => false, 'data' => 'The descriptor file for the user "'.$user_id.'" is corrupted! Contact the administrator' );
		}
		//
		return array( 'success' => true, 'data' => $user_info );
	}//openUserInfo


	/** Returns the user account record for the user specified.
	 *	Unlike openUserInfo(), this function returns a read-only copy of the user account.
	 *
	 *	@param string $user_id
	 *		string containing the (numeric) user id provided by Google Sign-In;
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or associative array
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`,
	 *		otherwise it will contain an associative array containing the information about the user specified.
	 *		The associative array in `data` will contain at least the keys specified in $USER_ACCOUNT_TEMPLATE.
	 */
	public static function getUserInfo( $user_id ){
		$res = self::openUserInfo( $user_id );
		if( !$res['success'] ){
			return $res;
		}
		//
		return array( 'success' => true, 'data' => $res['data']->asArray() );
	}//getUserInfo


	/** Returns the user account record of the user currently logged in.
	 *
	 *	@param string $field
	 *		(optional) name of the field to retrieve from the user account. It can be any of the keys specified
	 *		in $USER_ACCOUNT_TEMPLATE;
	 *
	 *	@retval mixed
	 *		If no user is currently logged in, returns `NULL`;
	 *		If `$field`=`NULL`, returns associative array containing the information about the user currently
	 *		logged in (similar to getUserInfo());
	 *		If a value for `$field` is passed, only the value of the field specified is returned (e.g., name).
	 */
	public static function getUserLogged( $field=null ){
		return (isset($_SESSION['USER_RECORD'])) ? ( ($field==null) ? $_SESSION['USER_RECORD'] : $_SESSION['USER_RECORD'][$field] ) : null;
	}//getUserLogged


	/** Returns the role of the user that is currently using the platform.
	 *
	 *	@retval string
	 *		role of the user that is currently using the platform. It can be any of the default roles
	 *		defined by <b>\\compose\\</b> or any other role registered by third-party packages. A list
	 *		of all the user roles registered can be retrieved using the function getUserTypesList();
	 */
	public static function getUserRole(){
		$user_role = ( self::isUserLoggedIn() )? self::getUserLogged('role') : 'guest';
		return $user_role;
	}//getUserRole


	/** Sets the user role of the user that is currently using the platform.
	 *	NOTE: this function does not update the user account of the current user permanently. This change
	 *	will be lost once the session is closed.
	 *
	 *	@param string $user_role
	 *		role to assign to the current user;
	 *
	 *	@retval void
	 */
	public static function setUserRole( $user_role ){
		$_SESSION['USER_RECORD']['role'] = $user_role;
	}//setUserRole


	/** Returns the list of all user roles known to the platform. It includes all the user roles defined
	 *	by <b>\\compose\\</b> plus all the user roles introduced by third-party packages.
	 *
	 *	@retval array
	 *		list of unique strings. Each string represents a different user role;
	 */
	public static function getUserTypesList(){
		return self::$registered_user_types;
	}//getUserTypesList



	// =======================================================================================================
	// Packages management functions

	/** Returns the list of packages installed on the platform.
	 *
	 *	@retval array
	 * 		an associative array of the form
	 *	<pre><code class="php">[
	 *		"package_id" => [
	 *			"id" : string, 					// ID of the package (identical to package_id)
	 *			"name" : string,				// name of the package
     *			"description" : string,			// brief description of the package
     *			"dependencies" : [
 	 *				"system-packages" : [],		// list of system packages required by the package
	 *				"packages" : []				// list of \\compose\\ packages required by the package
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
	 *
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
	 *
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
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
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
	 *		The `data` field contains an error string when `success` is `FALSE`.
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


	/** Returns the settings for a given package as an instance of \system\classes\EditableConfiguration.
	 *
	 *	@param string $package_name
	 *		the ID of the package to retrieve the settings for.
	 *
	 *	@retval mixed
	 *		If the package is installed, it returns an associative array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the configuration was successfully loaded
	 *		"data" => mixed 		// instance of EditableConfiguration or a string error message
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains a string with the error when `success` is `FALSE`.
	 *		If the package is not installed, the function returns `NULL`.
	 */
	public static function getPackageSettings( $package_name ){
		if( key_exists( $package_name, self::$settings ) ){
			return self::$settings[$package_name];
		}
		return null;
	}//getPackageSettings


	/** Returns the settings for a given package as an associative array.
	 *
	 *	@param string $package_name
	 *		the ID of the package to retrieve the settings for.
	 *
	 *	@retval mixed
	 *		If the function succeeds, it returns an associative array of the form
	 *	<pre><code class="php">[
	 *		"key" => "value",
	 *		... 				// other entries
	 *	]</code></pre>
	 *		where, `key` can ba any configuration key exported by the package
	 *		and `value` its value.
	 *		If the package is not installed, the function returns `NULL`.
	 *		If an error occurred while reading the configuration of the given
	 *		package, a `string` containing the error is returned.
	 */
	public static function getPackageSettingsAsArray( $package_name ){
		if( key_exists( $package_name, self::$settings ) ){
			if( self::$settings[$package_name]['success'] ){
				return self::$settings[$package_name]['data']->asArray();
			}
			return self::$settings[$package_name]['data'];
		}
		return null;
	}//getPackageSettingsAsArray


	/** Returns the value of the given setting key for the given package.
	 *
	 *	@param string $package_name
	 *		the ID of the package the setting key belongs to;
	 *
	 *	@param string $key
	 *		the setting key to retrieve;
	 *
	 *	@param string $default_value
	 *		the default value returned if the key does not exist.
	 *		DEFAULT = null;
	 *
	 *	@retval mixed
	 *		If the function succeeds, it returns the value of the setting key specified.
	 *		If the package is not installed or an error occurred while reading the
	 *		configuration for the given package, `NULL` is returned.
	 */
	public static function getSetting( $package_name, $key, $default_value=null ){
		if( key_exists( $package_name, self::$settings ) ){
			if( self::$settings[$package_name]['success'] ){
				$res = self::$settings[$package_name]['data']->get( $key, $default_value );
				if( !$res['success'] )
					return null;
				return $res['data'];
			}
			return null;
		}
		return null;
	}//getSetting


	/** Sets the value for the given setting key of the given package.
	 *
	 *	@param string $package_name
	 *		the ID of the package the setting key belongs to;
	 *
	 *	@param string $key
	 *		the setting key to set the value for;
	 *
	 *	@param string $value
	 *		the new value to store in the package's settings;
	 *
	 *	@retval mixed
	 *		If the function succeeds, it returns `TRUE`.
	 *		If the package is not installed, the function returns `NULL`.
	 *		If an error occurred while writing the configuration of the given
	 *		package, a `string` containing the error is returned.
	 */
	public static function setSetting( $package_name, $key, $value ){
		if( key_exists( $package_name, self::$settings ) ){
			if( self::$settings[$package_name]['success'] ){
				// update the key,value pair
				$res = self::$settings[$package_name]['data']->set( $key, $value );
				if( !$res['success'] ) return $res['data'];
				// commit the new configuration
				$res = self::$settings[$package_name]['data']->commit();
				if( !$res['success'] ) return $res['data'];
				// success
				return true;
			}
			return self::$settings[$package_name]['data']; // error message
		}
		return null;
	}//setSetting



	// =======================================================================================================
	// Package-specific resources functions


	/** Returns the URL to a package-specific image.
	 *
	 *	@param string $image_file_with_extension
	 *		Filename of the image (including extension);
	 *
	 *	@param string $package_name
	 *		(optional) Name of the package the requested image belongs to. Default is 'core';
	 *
	 *	@retval string
	 *		URL of the image.
	 */
	public static function getImageURL( $image_file_with_extension, $package_name="core" ){
		if( $package_name == "core" ){
			// TODO: return placeholder if the image does not exist (only for core case, image.php does the same)
			return sprintf("%s/images/%s", Configuration::$BASE_URL, $image_file_with_extension );
		}else{
			return sprintf("%s/image.php?package=%s&image=%s", Configuration::$BASE, $package_name, $image_file_with_extension );
		}
	}//getImageURL



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
					if( !is_null($accessibleBy) && !in_array($accessibleBy, $page['access_level']) ) continue;
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
						if( !is_null($accessibleBy) && !in_array($accessibleBy, $page['access_level']) ) continue;
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
				if( !is_null($accessibleBy) && !in_array($accessibleBy, $page['access_level']) ) continue;
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
	 *		whether the page is enabled.
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
	 *		The `data` field contains an error string when `success` is `FALSE`.
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
	 *		The `data` field contains an error string when `success` is `FALSE`.
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


	/** Returns whether the given API service is installed on the platform.
	 *
	 *	@param string $api_version
	 *		the version of the API the service to check belongs to;
	 *
	 *	@param string $service_name
	 *		the name of the API service to check;
	 *
	 *	@retval boolean
	 * 		whether the API service exists;
	 */
	public static function APIserviceExists( $api_version, $service_name ){
		$api_setup = self::getAPIsetup();
		return isset($api_setup[$api_version]) && isset($api_setup[$api_version]['services'][$service_name]);
	}//APIserviceExists


	/** Returns whether the specified API service is enabled.
	 *
	 *	If the API service does not exist, the function will return `FALSE`.
	 *
	 *	@param string $api_version
	 *		the version of the API the service to check belongs to;
	 *
	 *	@param string $service_name
	 *		the name of the API service to check;
	 *
	 *	@retval boolean
	 *		whether the API service exists and is enabled;
	 */
	public static function isAPIserviceEnabled( $api_version, $service_name ){
		if( !self::APIserviceExists($api_version, $service_name) ) return false;
		$service_disabled_flag = sprintf('%sapi/%s/flags/%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name);
		return !file_exists($service_disabled_flag);
	}//isAPIserviceEnabled


	/** Enables an API service.
	 *
	 *	@param string $api_version
	 *		the version of the API the service to enable belongs to;
	 *
	 *	@param string $service_name
	 *		the name of the API service to enable;
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function enableAPIservice( $api_version, $service_name ){
		if( !self::APIserviceExists($api_version, $service_name) )
			return ['success' => false, 'data' => sprintf('The API service "%s(v%s)" does not exist', $service_name, $api_version)];
		$service_disabled_flag = sprintf('%sapi/%s/flags/%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name);
		if( file_exists($service_disabled_flag) ){
			$success = unlink( $service_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
	}//enableAPIservice


	/** Disables an API service.
	 *
	 *	@param string $api_version
	 *		the version of the API the service to disable belongs to;
	 *
	 *	@param string $service_name
	 *		the name of the API service to disable;
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function disableAPIservice( $api_version, $service_name ){
		// avoid disabling things that cannot be re-enabled
		if( $service_name == 'api' )
			return ['success' => false, 'data' => sprintf('The API service "%s" cannot be disabled', $service_name)];
		if( !self::APIserviceExists($api_version, $service_name) )
			return ['success' => false, 'data' => sprintf('The API service "%s(v%s)" does not exist', $service_name, $api_version)];
		$service_disabled_flag = sprintf('%sapi/%s/flags/%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name);
		if( !file_exists($service_disabled_flag) ){
			$success = touch( $service_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
	}//disableAPIservice


	/** Returns whether the given API action is installed on the platform.
	 *
	 *	@param string $api_version
	 *		the version of the API the action to check belongs to;
	 *
	 *	@param string $service_name
	 *		the name of the API service the action to check belongs to;
	 *
	 *	@param string $action_name
	 *		the name of the API action to check;
	 *
	 *	@retval boolean
	 * 		whether the API action exists;
	 */
	public static function APIactionExists( $api_version, $service_name, $action_name ){
		$api_setup = self::getAPIsetup();
		return isset($api_setup[$api_version])
			&& isset($api_setup[$api_version]['services'][$service_name])
			&& isset($api_setup[$api_version]['services'][$service_name]['actions'][$action_name]);
	}//APIactionExists


	/** Returns whether the specified API action is enabled.
	 *
	 *	If the API action does not exist, the function will return `FALSE`.
	 *
	 *	@param string $api_version
	 *		the version of the API the action to check belongs to;
	 *
	 *	@param string $service_name
	 *		the name of the API service the action to check belongs to;
	 *
	 *	@param string $action_name
	 *		the name of the API action to check;
	 *
	 *	@retval boolean
	 *		whether the API action exists and is enabled;
	 */
	public static function isAPIactionEnabled( $api_version, $service_name, $action_name ){
		if( !self::APIactionExists($api_version, $service_name, $action_name) ) return false;
		$action_disabled_flag = sprintf('%sapi/%s/flags/%s.%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name, $action_name);
		return !file_exists($action_disabled_flag);
	}//isAPIactionEnabled


	/** Enables an API action.
	 *
	 *	@param string $api_version
	 *		the version of the API the action to enable belongs to;
	 *
	 *	@param string $service_name
	 *		the name of the API service the action to enable belongs to;
	 *
	 *	@param string $action_name
	 *		the name of the API action to enable;
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function enableAPIaction( $api_version, $service_name, $action_name ){
		if( !self::APIactionExists($api_version, $service_name, $action_name) )
			return ['success' => false, 'data' => sprintf('The API action "%s.%s(v%s)" does not exist', $service_name, $action_name, $api_version)];
		$action_disabled_flag = sprintf('%sapi/%s/flags/%s.%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name, $action_name);
		if( file_exists($action_disabled_flag) ){
			$success = unlink( $action_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
	}//enableAPIaction


	/** Disables an API action.
	 *
	 *	@param string $api_version
	 *		the version of the API the action to disable belongs to;
	 *
	 *	@param string $service_name
	 *		the name of the API service the action to disable belongs to;
	 *
	 *	@param string $action_name
	 *		the name of the API action to disable;
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function disableAPIaction( $api_version, $service_name, $action_name ){
		// avoid disabling things that cannot be re-enabled
		if( $service_name == 'api' && in_array($action_name, ['service_enable', 'action_enable']) )
			return ['success' => false, 'data' => sprintf('The API action "%s.%s" cannot be disabled', $service_name, $action_name)];
		if( !self::APIactionExists($api_version, $service_name, $action_name) )
			return ['success' => false, 'data' => sprintf('The API action "%s.%s(v%s)" does not exist', $service_name, $action_name, $api_version)];
		$action_disabled_flag = sprintf('%sapi/%s/flags/%s.%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name, $action_name);
		if( !file_exists($action_disabled_flag) ){
			$success = touch( $action_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
	}//disableAPIaction




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


	/** Returns the hash identifying the version of the codebase.
	 * 	This corresponds to the commit ID on git.
	 *
	 *	@param boolean $long_hash
	 *		whether to return the short hash (first 7 digits) or the long (full) commit hash.
	 *		DEFAULT = false (7-digits commit hash).
	 *
	 *	@retval string
	 *		alphanumeric hash of the commit currently fetched on the server
	 */
	public static function getCodebaseHash( $long_hash=false ){
		exec( 'git log -1 --format="%H"', $hash, $exit_code );
		if( $exit_code != 0 ){
			$hash = 'ND';
		}else{
			$hash = ($long_hash)? $hash[0] : substr( $hash[0], 0, 7 );
		}
		//
		return $hash;
	}//getCodebaseHash


	/** Returns information about the current codebase (e.g., git user, git repository, remote URL, etc.)
	 *
	 *	@retval array
	 *		An array containing info about the codebase with the following details:
	 *	<pre><code class="php">[
	 *		"git_owner" => string, 			// username of the owner of the git repository
	 *		"git_repo" => string, 			// name of the repository
	 *		"git_host" => string, 			// hostname of the remote git server
	 *		"git_remote_url" => string, 	// url to the remote repository
	 *		"head_hash" => string, 			// short commit hash of the head of the local repository
	 *		"head_full_hash" => string, 	// full commit hash of the head of the local repository
	 *		"head_tag" => mixed 			// tag associated to the head. null if no tag is found
	 *		"latest_tag" => mixed 			// latest tag (going back in time) this codebase is based on. null if no tag is found.
	 *	]</code></pre>
	 *
	 */
	public static function getCodebaseInfo(){
		$codebase_info = [
			'git_owner' => 'ND',
			'git_repo' => 'ND',
			'git_host' => 'ND',
			'git_remote_url' => 'ND',
			'head_hash' => self::getCodebaseHash(),
			'head_full_hash' => self::getCodebaseHash(true),
			'head_tag' => 'ND',
			'latest_tag' => 'ND'
		];
		exec( 'git config --get remote.origin.url', $info, $exit_code );
		if( $exit_code != 0 ){
			$codebase_info['git_user'] = 'GIT_ERROR';
			$codebase_info['git_repo'] = 'GIT_ERROR';
		}else{
			if( strcasecmp( substr($info[0], 0, 4), "http") == 0 ){
				// the remote URL is in the format "http(s)://(<user>@)<host>/<owner>/<repo>.git"
				$pattern = "/http(s)?:\/\/([^@]+@)?([^:]+)\/(.*)\/(.*)\.git/";
				preg_match_all($pattern, $info[0], $matches);
				$codebase_info['git_host'] = $matches[3][0];
				$codebase_info['git_owner'] = $matches[4][0];
				$codebase_info['git_repo'] = $matches[5][0];
				$codebase_info['git_remote_url'] = sprintf( "http%s://%s/%s/%s.git",
					$matches[1][0],
					$codebase_info['git_host'],
					$codebase_info['git_owner'],
					$codebase_info['git_repo']
				);
			}else{
				// the remote URL is in the format "git@<host>:<owner>/<repo>"
				$pattern = "/[^@]+@([^:]+):(.*)\/(.*)/";
				preg_match_all($pattern, $info[0], $matches);
				$codebase_info['git_host'] = $matches[1][0];
				$codebase_info['git_owner'] = $matches[2][0];
				$codebase_info['git_repo'] = $matches[3][0];
				$codebase_info['git_remote_url'] = sprintf( "http://%s/%s/%s.git",
					$codebase_info['git_host'],
					$codebase_info['git_owner'],
					$codebase_info['git_repo']
				);
			}
		}
		// get tag associated to the head (if any)
		exec( 'git tag --contains HEAD', $tag, $exit_code );
		if( $exit_code != 0 ){
			$codebase_info['head_tag'] = 'GIT_ERROR';
		}else{
			$cb_tag = trim($tag[0]);
			$codebase_info['head_tag'] = (strlen($cb_tag) <= 0)? null : $cb_tag;
		}
		// get closest tag going back in time (if any)
		exec( 'git describe --abbrev=0 --tags', $latest_tag, $exit_code );
		if( $exit_code != 0 ){
			$codebase_info['latest_tag'] = 'GIT_ERROR';
		}else{
			$latest_cb_tag = trim($latest_tag[0]);
			$codebase_info['latest_tag'] = (strlen($latest_cb_tag) <= 0)? null : $latest_cb_tag;
		}
		//
		return $codebase_info;
	}//getCodebaseInfo


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
		return ( preg_match(self::$STRING_TYPES_REGEX['alphabetic'], $string) == 1 ) && ( ($length == null)? true : ($length==strlen($string)) );
	}//isAlphabetic


	public static function isNumeric( $string, $length=null ){
		return ( preg_match(self::$STRING_TYPES_REGEX['numeric'], $string) == 1 ) && ( ($length == null)? true : ($length==strlen($string)) );
	}//isNumeric


	public static function isAlphaNumeric( $string, $length=null ){
		return ( preg_match(self::$STRING_TYPES_REGEX['alphanumeric'], $string) == 1 ) && ( ($length == null)? true : ($length==strlen($string)) );
	}//isAlphaNumeric


	public static function isAvalidEmailAddress( $string, $length=null ){
		return ( preg_match(self::$STRING_TYPES_REGEX['email'], $string) == 1 ) && ( ($length == null)? true : ($length==strlen($string)) );
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


	public static function _load_packages_settings(){
		$packages = self::getPackagesList();
		$packages_ids = array_keys( $packages );
		$settings = [];
		//
		foreach( $packages_ids as $pkg_id ){
			$pkg_settings = new EditableConfiguration( $pkg_id );
			$res = $pkg_settings->sanityCheck();
			if( !$res['success'] ){
				$settings[$pkg_id] = $res;
			}else{
				$settings[$pkg_id] = [
					'success' => true,
					'data' => $pkg_settings
				];
			}
		}
		//
		return $settings;
	}//_load_packages_settings


	public static function _load_API_setup(){
		$packages = self::getPackagesList();
		$packages_ids = array_keys( $packages );
		// load global settings for API
		$global_api_setts_file = sprintf("%s/../api/web-api-settings.json", __DIR__);
		$global_api_setts = json_decode( file_get_contents($global_api_setts_file), true );
		// create resulting object
		$api = [];
		foreach( $global_api_setts['versions'] as $v => $v_specs ){
			$api[$v] = [
				'services' => [],
				'global' => $global_api_setts['global'],
				'enabled' => $v_specs['enabled']
			];
		}
		//
		foreach( $api as $api_version => &$api_v_specs ){
			$api_v_enabled = $api_v_specs['enabled'];
			foreach( $packages_ids as $pkg_id ){
				$api_services_descriptors = sprintf("%s/../packages/%s/modules/api/%s/api-services/specifications/*.json", __DIR__, $pkg_id, $api_version);
				$jsons = glob( $api_services_descriptors );
				//
				foreach ($jsons as $json) {
					$api_service_id = self::_regex_extract_group($json, "/.*api\/(.+)\/api-services\/specifications\/(.+).json/", 2);
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
					// check whether the service is enabled
					$api_service_disabled_flag = sprintf('%sapi/%s/flags/%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $api_service_id);
					$api_service['enabled'] = !file_exists($api_service_disabled_flag);
					$api_service['enabled'] = $api_v_enabled && $packages[$pkg_id]['enabled'] && $api_service['enabled'];
					//
					foreach ($api_service['actions'] as $api_action_id => &$api_action) {
						$api_action_disabled_flag = sprintf('%sapi/%s/flags/%s.%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $api_service_id, $api_action_id);
						$api_action['enabled'] = !file_exists($api_action_disabled_flag);
						$api_action['enabled'] = $api_service['enabled'] && $api_action['enabled'];
						// collect user types
						self::$registered_user_types = array_unique(
							array_merge(self::$registered_user_types, $api_action['access_level'])
						);
					}
					//
					$api_v_specs['services'][$api_service_id] = $api_service;
				}
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
				foreach ($page['access_level'] as $access) {
					if( !isset($pages['by-usertype'][$access]) ) $pages['by-usertype'][$access] = [];
					array_push( $pages['by-usertype'][$access], $page );
				}
				// collect user types
				self::$registered_user_types = array_unique(
					array_merge(self::$registered_user_types, $page['access_level'])
				);
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

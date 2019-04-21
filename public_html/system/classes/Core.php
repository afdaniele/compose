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
require_once __DIR__.'/Database.php';
require_once __DIR__.'/Utils.php';
require_once __DIR__.'/Formatter.php';
require_once __DIR__.'/Cache.php';
require_once __DIR__.'/enum/StringType.php';
require_once __DIR__.'/enum/EmailTemplates.php';
require_once __DIR__.'/enum/CacheTime.php';
// php-mailer classes
require_once __DIR__.'/PHPMailer/PHPMailerAutoload.php';

require_once __DIR__.'/yaml/Spyc.php';

require_once __DIR__.'/jsonDB/JsonDB.php';

// load Google API client
require_once __DIR__.'/google_api_php_client/vendor/autoload.php';


use system\classes\enum\EmailTemplates;
use system\classes\enum\StringType;
use system\classes\enum\CacheTime;
use system\classes\jsonDB\JsonDB;
use system\classes\Database;
use system\classes\Formatter;
use system\classes\Cache;
use system\classes\CacheProxy;
use system\classes\Utils;


define('INFO', 0);
define('WARNING', 1);
define('ERROR', 2);


/** Core module of the platform <b>\\compose\\</b>.
 */
class Core{

	private static $initialized = false;
	private static $cache = null;
	private static $packages = null;
	private static $pages = null;
	private static $verbose = false;
	private static $debug = false;
	private static $settings = null;
	private static $debugger_data = [];
	private static $registered_css_stylesheets = [];
	private static $default_page_per_role = [
		'administrator' => 'profile',
		'supervisor' => 'profile',
		'user' => 'profile',
		'guest' => 'login'
	];
	private static $registered_user_roles = [
		'core' => [
			'guest' => [
				'default_page' => 'login',
				'factory_default_page' => 'login'
			],
			'user' => [
				'default_page' => 'profile',
				'factory_default_page' => 'profile'
			],
			'supervisor' => [
				'default_page' => 'profile',
				'factory_default_page' => 'profile'
			],
			'administrator' => [
				'default_page' => 'profile',
				'factory_default_page' => 'profile'
			]
		]
	];


	private static $RESERVED_PAGES = [
    'setup',
		'api',
    'data',
    'debug',
    'error',
    'login',
    'maintenance',
    'packages',
    'profile',
    'settings',
    'users',
    'docs'
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
			"__nullable" => true,
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

	private static $PACKAGE_METADATA_TEMPLATE = [
		"name" => ["string", "Name of the package"],
	    "description" => ["string", "Textual description of the package"],
	    "core" => [
			"__type" => "associative_array",
			"__nullable" => true,
			"__details" => "Associative array containing info about an (optional) core module to include",
			"namespace" => ["string", "Namespace under which the core module is declared. The string '/system/classes/' will be prepended"],
			"file" => ["string", "Path to the PHP file (extension included) containing the core class to load. The path must be relative to the PACKAGE_ROOT directory"],
			"class" => ["string", "Name of the class as defined in the core file specified above"]
		],
		"dependencies" => [
			"__type" => "associative_array",
			"__nullable" => true,
			"__details" => "Associative array containing info about (optional) package dependencies",
			"system-packages" => [
				"__type" => "array",
				"__details" => "List of system packages required by the package",
				"__sample_item" => ["string", "Name of the system package to install"]
			],
			"packages" => [
			   "__type" => "array",
			   "__details" => "List of \compose\ packages required by the package",
			   "__sample_item" => ["string", "Name of the package to install"]
		   ]
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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function init( $safe_mode=false ){
		if( !self::$initialized ){
			mb_internal_encoding("UTF-8");
			//
			// init configuration
			$res = Configuration::init();
			if( !$res['success'] ){
				return $res;
			}
			//
			// create cache proxy (cache initialization happens later)
			self::$cache = new CacheProxy('core');
			//
			// load settings for the core module only (needed to initialize the cache)
			self::$packages = ['core' => null];
			self::$settings = self::_load_packages_settings( true );
			//
			// set timezone
			date_default_timezone_set( self::getSetting('timezone', 'core', 'America/Chicago') );
			//
			// load default page per role
			foreach( self::$registered_user_roles['core'] as $user_role => &$user_role_config ){
				$key = sprintf('%s_default_page', $user_role);
				$user_role_config['default_page'] = self::getSetting($key, 'core', $user_role_config['factory_default_page']);
			}
			//
			// initialize cache
			if( self::getSetting('cache_enabled', 'core', false) ){
				Cache::init();
			}
			//
			// load list of available packages
			self::$packages = self::_load_available_packages( $safe_mode );
			// load list of available pages
			self::$pages = self::_load_available_pages( $safe_mode );
			// load package-specific settings
			self::$settings = self::_load_packages_settings( $safe_mode );
			//
			// safe mode (everything after this point should be optional)
			if( $safe_mode ){
				self::$initialized = true;
				return array( 'success' => true, 'data' => null );
			}
			//
			// load email templates
			EmailTemplates::init();
			//
			// create dependencies graph for the packages
			$dep_graph = [];
			foreach( self::$packages as $pkg_id => $pkg ){
				if( $pkg_id=='core' ) continue;
				// collect dependencies
				$dep_graph[$pkg_id] = $pkg['dependencies']['packages'];
			}
			// solve the dependencies graph
			$res = self::_solve_dependencies_graph($dep_graph);
			if( !$res['success'] ) return $res;
			$package_order = $res['data'];
			//
			// initialize all the packages
			foreach( $package_order as $pkg_id ){
				$pkg = self::$packages[$pkg_id];
				if( !$pkg['enabled'] ) continue;
				// initialize package Core class
				if( !is_null($pkg['core']) ){
					// try to load the core file
					$file_loaded = include_once $pkg['core']['file'];
					if( $file_loaded ){
						// TODO: do not prepend \system\classes if it is already in $pkg['core']['namespace']
						$php_init_command = sprintf( "return \system\packages\%s\%s::init();", $pkg['core']['namespace'], $pkg['core']['class'] );
						// try to initialize the package core class
						try {
							$res = eval( $php_init_command );
							if( !is_array($res) )
								return ['success' => false, 'data' => sprintf('An error occurred while initializing the package `%s`. Command `%s`', $pkg['id'], $php_init_command)];
							if( !$res['success'] )
								return ['success' => false, 'data' => sprintf('An error occurred while initializing the package `%s`. Command `%s`. The module reports: "%s"', $pkg['id'], $php_init_command, $res['data'])];
						} catch (\Error $e) {
							return ['success' => false, 'data' => $e->getMessage()];
						}
					}
				}
			}
			self::$initialized = true;
			return ['success' => true, 'data' => null];
		}else{
			return ['success' => true, 'data' => "Core already initialized!"];
		}
	}//init


  public static function isComposeConfigured(){
    // TODO: Cache this data

    // open first_setup DB
		$first_setup_db = new Database('core', 'first_setup');
		// return whether the configuration flag exists
		return $first_setup_db->key_exists('configured');
  }//isComposeConfigured


  public static function getCurrentResource(){
    $resource_parts = [
      Configuration::$PAGE,
      Configuration::$ACTION,
      Configuration::$ARG1,
      Configuration::$ARG2
    ];
    $resource_parts = array_filter(
      $resource_parts,
      function($e){return !is_null($e) && strlen($e) > 0;}
    );
    return implode('/', $resource_parts);
  }//getCurrentResource


  public static function getCurrentResourceURL($qs_array=[], $include_qs=false){
    $qs_dict = array_merge(($include_qs)? $_GET : [], $qs_array);
    $qs = toQueryString(array_keys($qs_dict), $qs_dict, true);
    $resource = self::getCurrentResource();
    return sprintf('%s%s%s', Configuration::$BASE_URL, $resource, $qs);
  }//getCurrentResourceURL


  public static function getURL($page=null, $action=null, $arg1=null, $arg2=null, $qs=[]){
    return sprintf(
      '%s%s%s%s%s',
      Configuration::$BASE_URL,
      is_null($page)? '' : $page,
      is_null($action)? '' : $action,
      is_null($arg1)? '' : $arg1,
      is_null($arg2)? '' : $arg2,
      (count($qs) > 0)? toQueryString(array_keys($qs), $qs, true) : ''
    );
  }//getURL


	public static function loadPackagesModules( $module_family=null, $pkg_id=null ){
		foreach( self::$packages as $pkg ){
			if( !$pkg['enabled'] || ( !is_null($pkg_id) && $pkg_id != $pkg['id'] ) )
				continue;
			// load package modules
			foreach( $pkg['modules'] as $module_fam => $module_scripts ){
				if( !is_null($module_family) && $module_family != $module_fam )
					continue;
				foreach( $module_scripts as $module_script ){
					// check file
					if( !file_exists($module_script) ){
						self::collectDebugInfo( $pkg['id'], sprintf('Load module script %s of type %s', $module_script, $module_family), false, Formatter::BOOLEAN );
						continue;
					}
					// load module
					require_once $module_script;
					self::collectDebugInfo( $pkg['id'], sprintf('Load module %s of type %s', $module_script, $module_family), true, Formatter::BOOLEAN );
				}
			}
		}
	}//loadPackagesModules


	public static function getClasses( $parent_class=null ){
		$classes = [];
		foreach( get_declared_classes() as $class ){
		    if( !is_null($parent_class) && !is_subclass_of($class, $parent_class) )
				continue;
			array_push( $classes, $class );
		}
		return $classes;
	}//getClasses


	/** Terminates the Core module.
	 *	It is responsible for committing unsaved changes to the disk or closing open connections (e.g., mySQL)
	 *	before leaving.
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function close(){
		return array( 'success' => true, 'data' => null );
	}//close


	/** Creates a new PHP Session and assigns a new randomly generated 16-digits authorization token to it.
	 *
	 *	@retval boolean
	 *		`TRUE` if the call succeded, `FALSE` otherwise
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


	/** Writes and closes the current PHP Session.
	 *
	 *	@retval boolean
	 *		`TRUE` if the call succeded, `FALSE` otherwise
	 */
	public static function closeSession(){
		return session_write_close();
	}//closeSession




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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function logInUserWithGoogle( $id_token ){
		if( $_SESSION['USER_LOGGED'] ){
			return array( 'success' => false, 'data' => 'You are already logged in!' );
		}
		// verify id_token
		$client = new \Google_Client([
			'client_id' => self::getSetting('google_client_id', 'core')
		]);
		$payload = $client->verifyIdToken($id_token);
		if( $payload ){
			$userid = $payload['sub'];
			// create user descriptor
			$user_info = [
				"username" => $userid,
			    "name" => $payload['name'],
			    "email" => $payload['email'],
				"picture" => $payload['picture'],
			    "role" => "user",
				"active" => true,
				"pkg_role" => []
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
			// make sure that the user is active
			if( !boolval($user_info['active']) ){
				return [
					'success' => false,
					'data' => 'The user profile you are trying to login with is not active. Please, contact the administrator'
				];
			}
			//
			$_SESSION['USER_LOGGED'] = true;
			$_SESSION['USER_RECORD'] = $user_info;
			//
			self::regenerateSessionID();
			return ['success' => true, 'data' => $user_info];
		}else{
			// Invalid ID token
			return ['success' => false, 'data' => "Invalid ID Token"];
		}
	}//logInUserWithGoogle


	/** Authorizes a user using an API Application.
	 *
	 *	@param string $app_id
	 *		ID of the API Application to authenticate with.
	 *
	 *	@param string $app_secret
	 *		Secret key associated with the API Application identified by `$app_id`.
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field will contain the info about the API Application used to
	 *		authenticate the user or an error string when `success` is `FALSE`.
	 */
	public static function authorizeUserWithAPIapp( $app_id, $app_secret ){
		RESTfulAPI::init();
		// check if the app exists
		$res = RESTfulAPI::getApplication( $app_id );
		if( !$res['success'] ) return $res;
		// get the app
		$app = $res['data'];
		// check if the app_secret matches
		if( !boolval($app_secret == $app['secret']) )
			return ['success' => false, 'data' => 'The application secret key provided is not correct'];
		// check if the app is enabled
		if( !boolval($app['enabled']) )
			return ['success' => false, 'data' => sprintf('The application `%s` is not enabled, thus it cannot be used', $app['id'])];
		// get owner of the app
		$username = $app['user'];
		if( !self::userExists($username) )
			return ['success' => false, 'data' => sprintf('The application `%s` is not enabled, thus it cannot be used', $app['id'])];
		// load user info
		$res = self::openUserInfo($username);
		if( !$res['success'] ) return $res;
		$user_info = $res['data']->asArray();
		$user_info['pkg_role'] = [];
		// this data will be deleted if the PHP session was not initialized before this call
		$_SESSION['USER_LOGGED'] = true;
		$_SESSION['USER_RECORD'] = $user_info;
		// return app
		return [
			'success' => true,
			'data' => [
				'user' => $user_info,
				'app' => $app
			]
		];
	}//authorizeUserWithAPIapp


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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function createNewUserAccount( $user_id, &$user_info ){
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
		// open users DB
		$users_db = new Database('core', 'users');
		// create administrator if this is the first user
		if( $users_db->size() < 1 ){
			$user_info['role'] = 'administrator';
		}
		// create a new user account on the server
		$res = $users_db->write($user_id, $user_info);
		return $res;
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
		// open users DB
		$users_db = new Database('core', 'users');
		// get list of users
		return $users_db->list_keys();
	}//getUsersList


	/** Logs out the user from the platform.
	 *	If the user is not logged in yet, the call will return an error status.
	 *
	 *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function logOutUser(){
		if( !$_SESSION['USER_LOGGED'] ){
			return ['success' => false, 'data' => 'User not logged in yet!'];
		}
		// destroy session
		session_destroy();
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
	public static function userExists($user_id){
		// open users DB
		$users_db = new Database('core', 'users');
		// return whether the user exists
		return $users_db->key_exists($user_id);
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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or instance of \\system\\classes\\jsonDB\\JsonDB
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`,
	 *		otherwise it will contain an instance of the class \\system\\classes\\jsonDB\\JsonDB
	 *		containing the information about the user specified.
	 *		The JsonDB object will contain at least the keys specified in $USER_ACCOUNT_TEMPLATE.
	 *		See the documentation for the class JsonDB to understand how to edit and commit information.
	 */
	public static function openUserInfo( $user_id ){
		// open users DB
		$users_db = new Database('core', 'users');
		// make sure that the user exists
		if( !$users_db->key_exists($user_id) )
			return array( 'success' => false, 'data' => 'User "'.$user_id.'" not found!' );
		// load user info
		$res = $users_db->get_entry($user_id);
		if( !$res['success'] )
			return $res;
		$user_info = $res['data'];
		// sanity check on user entry
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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or associative array
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
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
	 *	@param string $package
	 *		(optional) package with respect to which we want to obtain the current role; Default is 'core';
	 *
	 *	@retval string
	 *		role of the user that is currently using the platform. It can be any of the default roles
	 *		defined by <b>\\compose\\</b> or any other role registered by third-party packages. A list
	 *		of all the user roles registered can be retrieved using the function getAllRegisteredUserRoles();
	 */
	public static function getUserRole( $package='core' ){
		// not logged => guest
		if( !self::isUserLoggedIn() ) return 'guest';
		// core package
		if( $package == 'core' ) return self::getUserLogged('role');
		// third-party packages
		$pkg_role = self::getUserLogged('pkg_role');
		if( in_array($package, array_keys($pkg_role)) ){
			return $pkg_role[$package];
		}
		// no role for this package
		return null;
	}//getUserRole


	/** Sets the package-specific role of the user that is currently using the platform.
	 *	NOTE: this function does not update the user account of the current user permanently. This change
	 *	will be lost once the session is closed.
	 *
	 *	@param string $user_role
	 *		role to assign to the current user;
	 *
	 *	@param string $package
	 *		(optinal) package with respect to which we assign the new role; Default is `core`.
	 *
	 *	@retval void
	 */
	public static function setUserRole( $user_role, $package='core' ){
		//TODO: make sure that the give <pkg,role> pair was previously registered
		if( $package == 'core' ){
			if( in_array($user_role, ['guest', 'user', 'supervisor', 'administrator']) )
				$_SESSION['USER_RECORD']['role'] = $user_role;
		}else{
			$_SESSION['USER_RECORD']['pkg_role'][$package] = $user_role;
		}
	}//setUserRole


	/** Returns the list of all the roles of the current user on the platform. It includes the user role
	 *	defined by <b>\\compose\\</b> plus all the user roles introduced by third-party packages and associated
	 *	to the current user. The main user role (defined by <b>\\compose\\</b>) is returned in the format
	 *	"role". Package-specific roles are returned in the form "package_id:role".
	 *
	 *	@retval array
	 *		list of unique strings. Each string represents a different role;
	 */
	public static function getUserRolesList(){
		$roles = [ self::getUserRole('core') ];
		$pkg_role = self::getUserLogged('pkg_role');
		foreach( $pkg_role as $pkg_id => $pkg_role ){
			$role = sprintf('%s:%s', $pkg_id, $pkg_role);
			array_push($roles, $role);
		}
		return $roles;
	}//getUserRolesList


	/**TODO: Returns the list of all user roles known to the platform. It includes all the user roles defined
	 *	by <b>\\compose\\</b> plus all the user roles introduced by third-party packages.
	 *
	 *	@retval array
	 *		list of unique strings. Each string represents a different user role;
	 */
	public static function getPackageRegisteredUserRoles( $package='core' ){
		if( array_key_exists($package, self::$registered_user_roles) ){
			return array_keys( self::$registered_user_roles[$package] );
		}
		return [];
	}//getPackageRegisteredUserRoles


	/**TODO: Returns the list of all user roles known to the platform. It includes all the user roles defined
	 *	by <b>\\compose\\</b> plus all the user roles introduced by third-party packages.
	 *
	 *	@retval array
	 *		list of unique strings. Each string represents a different user role;
	 */
	public static function getAllRegisteredUserRoles(){
		$roles = [];
		foreach( array_keys(self::getPackagesList()) as $pkg_id ){
			$prefix = boolval($pkg_id == 'core')? '' : sprintf('%s:', $pkg_id);
			$pkg_roles = self::getPackageRegisteredUserRoles( $pkg_id );
			array_merge( $roles, array_map(function($v){ return sprintf('%s%s',$prefix,$v); }, $pkg_roles) );
		}
		return array_unique($roles);
	}//getAllRegisteredUserRoles


	/** Adds a new user role to the list of roles known to the platform.
	 *
	 *	@retval void
	 */
	public static function registerNewUserRole( $package, $user_role, $default_page='NO_DEFAULT_PAGE' ){
		if( !array_key_exists($package, self::$registered_user_roles) ) self::$registered_user_roles[$package] = [];
		// add the user role if not present
		if( !array_key_exists($user_role, self::$registered_user_roles[$package]) ){
			self::$registered_user_roles[$package][$user_role] = [
				'default_page' => 'NO_DEFAULT_PAGE',
				'factory_default_page' => 'NO_DEFAULT_PAGE'
			];
		}
		// update default page
		if( $default_page != 'NO_DEFAULT_PAGE' ){
			self::$registered_user_roles[$package][$user_role]['default_page'] = $default_page;
		}
	}//registerNewUserRole



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
	 *			"url_rewrite" : [
	 *				"rule_id" : [
	 *					"pattern" : string,		// regex of the rule for the URI to be compared against
	 *					"replace" : string		// replacement template using group-specific variables (e.g., $1)
	 *				],
	 *				...
 	 *			]
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
	public static function packageExists($package){
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
	public static function isPackageEnabled($package){
		$package_disabled_flag = sprintf('%s%s/disabled.flag', $GLOBALS['__PACKAGES__DIR__'], $package);
		return !file_exists($package_disabled_flag);
	}//isPackageEnabled


  public static function installPackage($package){
    return self::packageManagerBatch([$package], [], []);
  }//installPackage


  public static function updatePackage($package){
    return self::packageManagerBatch([], [$package], []);
  }//updatePackage


  public static function removePackage($package){
    return self::packageManagerBatch([], [], [$package]);
  }//removePackage


  public static function packageManagerBatch($to_install, $to_update, $to_remove){
    $to_remove = array_diff($to_remove, ['core']);
    $package_manager_py = sprintf(
      '%s/lib/python/compose/package_manager.py',
      $GLOBALS['__SYSTEM__DIR__']
    );
    $install_arg = '--install '.implode(' ', $to_install);
    $update_arg = '--update '.implode(' ', $to_update);
    $uninstall_arg = '--uninstall '.implode(' ', $to_remove);
    $cmd = sprintf(
      'python3 "%s" %s %s %s 2>&1',
      $package_manager_py,
      (count($to_install) > 0)? $install_arg : '',
      (count($to_update) > 0)? $update_arg : '',
      (count($to_remove) > 0)? $uninstall_arg : ''
    );
    $output = "";
    $exit_code = 0;
    exec($cmd, $output, $exit_code);
    $success = boolval($exit_code == 0);
    // invalidate cache
    self::$cache->clear();
    // ---
    return [
      'success' => $success,
      'data' => $success? null : implode('<br/>', array_merge(['Package Manager Error:'], $output))
    ];
  }//packageManagerBatch


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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
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
	 *		where, the `success` field indicates whether the call succeded.
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
	 *	@param string $key
	 *		the setting key to retrieve;
	 *
	 *	@param string $package_name
	 *		(optional) Name of the package the requested setting belongs to. Default is 'core';
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
	public static function getSetting( $key, $package_name='core', $default_value=null ){
		if( key_exists( $package_name, self::$settings ) ){
			if( self::$settings[$package_name]['success'] ){
				$res = self::$settings[$package_name]['data']->get( $key, $default_value );
				if( !$res['success'] )
					return $default_value;
				return $res['data'];
			}
			return $default_value;
		}
		return $default_value;
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
	 *	The image file must in the directory `/images` of the package.
	 *
	 *	@param string $image_file_with_extension
	 *		Filename of the image (including extension);
	 *
	 *	@param string $package_name
	 *		(optional) Name of the package the requested image belongs to. Default is 'core';
	 *
	 *	@retval string
	 *		URL to the requested image.
	 */
	public static function getImageURL( $image_file_with_extension, $package_name="core" ){
		if( $package_name == "core" ){
			// TODO: return placeholder if the image does not exist (only for core case, image.php does the same)
			return sprintf("%s/images/%s", Configuration::$BASE_URL, $image_file_with_extension );
		}else{
			return sprintf("%s/image.php?package=%s&image=%s", Configuration::$BASE, $package_name, $image_file_with_extension );
		}
	}//getImageURL


	/** Returns the URL to a package-specific Java-Script file.
	 *	The JS file must in the directory `/js` of the package.
	 *
	 *	@param string $js_file_with_extension
	 *		Filename of the Java-Script file (including extension);
	 *
	 *	@param string $package_name
	 *		(optional) Name of the package the requested Java-Script file belongs to. Default is 'core';
	 *
	 *	@retval string
	 *		URL to the requested Java-Script file.
	 */
	public static function getJSscriptURL( $js_file_with_extension, $package_name="core" ){
		if( $package_name == "core" ){
			return sprintf("%s/js/%s", Configuration::$BASE_URL, $js_file_with_extension );
		}else{
			return sprintf("%s/js.php?package=%s&script=%s", Configuration::$BASE, $package_name, $js_file_with_extension );
		}
	}//getJSscriptURL


	/** Returns the URL to a package-specific CSS file.
	 *	The CSS file must in the directory `/css` of the package.
	 *
	 *	@param string $css_file_with_extension
	 *		Filename of the CSS file (including extension);
	 *
	 *	@param string $package_name
	 *		(optional) Name of the package the requested CSS file belongs to. Default is 'core';
	 *
	 *	@retval string
	 *		URL to the requested CSS file.
	 */
	public static function getCSSstylesheetURL( $css_file_with_extension, $package_name="core" ){
		if( $package_name == "core" ){
			return sprintf("%s/css/%s", Configuration::$BASE_URL, $css_file_with_extension );
		}else{
			return sprintf("%s/css.php?package=%s&stylesheet=%s", Configuration::$BASE, $package_name, $css_file_with_extension );
		}
	}//getCSSstylesheetURL


	public static function registerCSSstylesheet( $css_file_with_extension, $package_name ){
		array_push(
			self::$registered_css_stylesheets,
			sprintf('%s%s/css/%s', $GLOBALS['__PACKAGES__DIR__'], $package_name, $css_file_with_extension)
		);
	}//registerCSSstylesheet


	public static function getRegisteredCSSstylesheets(){
		return self::$registered_css_stylesheets;
	}//getRegisteredCSSstylesheets



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
		$accessibleBy = is_null($accessibleBy)? null : (is_array($accessibleBy)? $accessibleBy : [$accessibleBy]);
		if( is_assoc($pages_collection) ){
			if( $order == 'by-id' ){
				// collection in which pages are organized in an associative array by-id
				foreach( $pages_collection as $key => $page ){
					if( $enabledOnly && !$page['enabled'] ) continue;
					if( !is_null($accessibleBy) && count(array_intersect($accessibleBy, $page['access_level'])) == 0 ) continue;
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
						if( !is_null($accessibleBy) && count(array_intersect($accessibleBy, $page['access_level'])) == 0 ) continue;
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
				if( !is_null($accessibleBy) && count(array_intersect($accessibleBy, $page['access_level'])) == 0 ) continue;
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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
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


	public static function getFactoryDefaultPagePerRole( $user_role ){
		$no_default = 'NO_DEFAULT_PAGE';
		if( !array_key_exists($user_role, self::$registered_user_roles['core']) ) return $no_default;
		// return default page
		return self::$registered_user_roles['core'][$user_role]['factory_default_page'];
	}//getFactoryDefaultPagePerRole


	public static function getDefaultPagePerRole( $user_role, $package='core' ){
		$no_default = 'NO_DEFAULT_PAGE';
		if( !array_key_exists($package, self::$registered_user_roles) ) return $no_default;
		if( !array_key_exists($user_role, self::$registered_user_roles[$package]) ) return $no_default;
		// return default page
		return self::$registered_user_roles[$package][$user_role]['default_page'];
	}//getDefaultPagePerRole




	// =======================================================================================================
	// Utility functions

	public static function getStatistics(){
		$statistics = array();
		//

		// TODO: Configuration::$CACHE_ENABLED is no longer available
		// Configuration::$CACHE_ENABLED = ( self::$cache !== null && self::$cache instanceof phpFastCache );
		// // cache stats
		// $statistics['STATS_TOTAL_SELECT_REQS'] = ( (Configuration::$CACHE_ENABLED && self::$cache->isExisting('STATS_TOTAL_SELECT_REQS'))? self::$cache->get( 'STATS_TOTAL_SELECT_REQS' ) : 1 );
		// $statistics['STATS_CACHED_SELECT_REQS'] = ( (Configuration::$CACHE_ENABLED && self::$cache->isExisting('STATS_CACHED_SELECT_REQS'))? self::$cache->get( 'STATS_CACHED_SELECT_REQS' ) : 1 );

		//
		return $statistics;
	}//getStatistics


	public static function getSiteName(){
		return self::getSetting('website_name', 'core');
	}//getSiteName


	/** Returns the hash identifying the version of the <b>\\compose\\</b> codebase.
	 * 	This corresponds to the commit ID on git.
	 *
	 *	@param boolean $long_hash
	 *		whether to return the short hash (first 7 digits) or the long (full) commit hash.
	 *		DEFAULT = false (7-digits commit hash).
	 *
	 *	@retval string
	 *		alphanumeric hash of the commit currently in use on the server
	 */
	public static function getCodebaseHash($long_hash=false){
		return self::getPackageCodebaseHash('core', $long_hash);
	}//getCodebaseHash


	/** Returns the hash identifying the version of a package's codebase.
	 * 	This corresponds to the commit ID on git.
   *
	 *	@param string $package_name
	 *		name of the package for which to retrieve the git hash.
 	 *
	 *	@param boolean $long_hash
	 *		whether to return the short hash (first 7 digits) or the long (full) commit hash.
	 *		DEFAULT = false (7-digits commit hash).
	 *
	 *	@retval string
	 *		alphanumeric hash of the commit currently fetched on the server
	 */
	public static function getPackageCodebaseHash($package_name, $long_hash=false){
    // check if this object is cached
		$cache_key = sprintf("pkg_%s_codebase_hash_%s", $package_name, $long_hash? 'long' : 'short');
		if(self::$cache->has($cache_key))
      return self::$cache->get( $cache_key );
		// hash not present in cache, get it from git
    $package_dir = sprintf('%s%s', $GLOBALS['__PACKAGES__DIR__'], $package_name);
    $hash = self::getGitRepositoryHash($package_dir, $long_hash);
    // cache hash
		self::$cache->set($cache_key, $hash, CacheTime::HOURS_24);
		//
    return $hash;
  }//getPackageCodebaseHash



  /** Returns the hash identifying the version of a repository.
	 * 	This corresponds to the commit ID on git.
   *
   *	@param string $git_repo_path
   *		absolute path to the git repository for which to retrieve the info.
 	 *
	 *	@param boolean $long_hash
	 *		whether to return the short hash (first 7 digits) or the long (full) commit hash.
	 *		DEFAULT = false (7-digits commit hash).
	 *
	 *	@retval string
	 *		alphanumeric hash of the commit currently fetched on the server
	 */
	public static function getGitRepositoryHash($git_repo_path, $long_hash=false){
    exec(
      sprintf('git -C "%s" log -1', $git_repo_path).' --format="%H"',
      $info,
      $exit_code
    );
		if( $exit_code != 0 ){
			$hash = 'ND';
		}else{
			$hash = ($long_hash)? $info[0] : substr($info[0], 0, 7);
		}
		return $hash;
	}//getGitRepositoryHash


	/** Returns information about the current <b>\\compose\\</b> codebase.
	 *
	 *	@retval array
	 *		See Core::getGitRepositoryInfo().
	 *
	 */
	public static function getCodebaseInfo(){
		return self::getPackageCodebaseInfo('core');
	}//getCodebaseInfo


  /** Returns information about a package's codebase.
   *
   *	@param string $package_name
   *		name of the package for which to retrieve the codebase info.
	 *
	 *	@retval array
	 *		See Core::getGitRepositoryInfo().
	 *
	 */
	public static function getPackageCodebaseInfo($package_name){
		// check if this object is cached
		$cache_key = sprintf("pkg_%s_codebase_info", $package_name);
		if( self::$cache->has( $cache_key ) ) return self::$cache->get( $cache_key );
		// hash not present in cache, get it from git
    $package_dir = sprintf('%s%s', $GLOBALS['__PACKAGES__DIR__'], $package_name);
    $codebase_info = self::getGitRepositoryInfo($package_dir);
		// cache object
		self::$cache->set( $cache_key, $codebase_info, CacheTime::HOURS_24 );
		//
		return $codebase_info;
	}//getCodebaseInfo


  /** Returns information about a git repository (e.g., git user, git repository, remote URL, etc.)
   *
   *	@param string $git_repo_path
   *		absolute path to the git repository for which to retrieve the info.
	 *
	 *	@retval array
	 *		An array containing info about the repository with the following details:
	 *	<pre><code class="php">[
	 *		"git_owner" => string, 			   // username of the owner of the git repository
	 *		"git_repo" => string, 			   // name of the repository
	 *		"git_host" => string, 			   // hostname of the remote git server
	 *		"git_remote_url" => string, 	 // url to the remote repository
	 *		"head_hash" => string, 			   // short commit hash of the head of the local repository
	 *		"head_full_hash" => string, 	 // full commit hash of the head of the local repository
	 *		"head_tag" => mixed 			     // tag associated to the head. null if no tag is found
	 *		"latest_tag" => mixed 			   // latest tag (back in time) of codebase. null if no tag is found.
	 *	]</code></pre>
	 *
	 */
	public static function getGitRepositoryInfo($git_repo_path){
    $codebase_info = [
			'git_owner' => 'ND',
			'git_repo' => 'ND',
			'git_host' => 'ND',
			'git_remote_url' => 'ND',
			'head_hash' => self::getGitRepositoryHash($git_repo_path),
			'head_full_hash' => self::getGitRepositoryHash($git_repo_path, true),
			'head_tag' => 'ND',
			'latest_tag' => 'ND'
		];
		exec(
      sprintf('git -C "%s" config --get remote.origin.url', $git_repo_path),
      $info,
      $exit_code
    );
		if( $exit_code != 0 ){
			$codebase_info['git_user'] = 'ND';
			$codebase_info['git_repo'] = 'ND';
		}else{
			if( strcasecmp( substr($info[0], 0, 4), "http") == 0 ){
        // the remote URL is in the format "http(s)://(<user>@)<host>/<owner>/<repo>(.git)"
        $pattern = "/http(s)?:\/\/([^@]+@)?(.*)\/(.*)\/(.*)(\.git)?/";
        preg_match_all($pattern, $info[0], $matches);
				$codebase_info['git_host'] = $matches[3][0];
				$codebase_info['git_owner'] = $matches[4][0];
				$codebase_info['git_repo'] = preg_replace('/\.git$/', '', $matches[5][0]);
				$codebase_info['git_remote_url'] = sprintf("http%s://%s/%s/%s.git",
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
				$codebase_info['git_remote_url'] = sprintf("http://%s/%s/%s.git",
					$codebase_info['git_host'],
					$codebase_info['git_owner'],
					$codebase_info['git_repo']
				);
			}
		}
		// get tag associated to the head (if any)
		exec(
      sprintf('git -C "%s" tag -l --points-at HEAD', $git_repo_path),
      $tag,
      $exit_code
    );
		if( $exit_code != 0 ){
			$codebase_info['head_tag'] = 'ND';
		}else{
			$cb_tag = trim($tag[0]);
			$codebase_info['head_tag'] = (strlen($cb_tag) <= 0)? 'ND' : $cb_tag;
		}
		// get closest tag going back in time (if any)
		exec(
      sprintf('git -C "%s" describe --abbrev=0 --tags', $git_repo_path),
      $latest_tag,
      $exit_code
    );
		if( $exit_code != 0 ){
			$codebase_info['latest_tag'] = 'ND';
		}else{
			$latest_cb_tag = trim($latest_tag[0]);
			$codebase_info['latest_tag'] = (strlen($latest_cb_tag) <= 0)? 'ND' : $latest_cb_tag;
		}
    return $codebase_info;
  }//getGitRepositoryInfo


	/** Returns the debugger data
	 *
	 *	@retval array
	 *		An array containing debugging data. The array contains an entry `key`=>`value` for each package
	 *		that produced debug information, where `key` is the package ID and `value` is an array. Such
	 *		array contains entries `key`=>`debug_entry`, with `key` a unique identifier of the test, and
	 *		`debug_entry` a tuple of the form [`test_value`, `test_format`]. `test_value` is the outcome of
	 *		the test, and `test_format` indicates how the `test_value` should be interpreted. `test_format`
	 *		contains values from the enum class \system\classes\Formatter.
	 *
	 */
	public static function getDebugInfo(){
		return self::$debugger_data;
	}//getDebugInfo


	public static function redirectTo($resource, $append_qs=false){
    $qs = '';
    $uri = ltrim(trim($_SERVER['REQUEST_URI']), '/');
    if($append_qs && strlen($uri) > 0){
      $qs = sprintf(
        '?q=%s',
        base64_encode($uri)
      );
    }
    echo sprintf(
      '<script type="text/javascript">window.open("%s%s%s", "_top");</script>',
      (substr($resource, 0, 4) == 'http')? '' : Configuration::$BASE,
      $resource,
      $qs
    );
		die();
		exit;
	}//redirectTo

  public static function openAlert($type, $message){
    echo sprintf(
      "<script type=\"application/javascript\">
      	$(document).ready(function() {
      		openAlert('%s', \"%s\");
      	});
      </script>",
      $type,
      addslashes($message)
    );
  }//openAlert

	public static function throwError( $errorMsg ){
		$_SESSION['_ERROR_PAGE_MESSAGE'] = $errorMsg;
		//
		self::redirectTo( 'error' );
	}//throwError


	public static function throwErrorF( ...$args ){
		$_SESSION['_ERROR_PAGE_MESSAGE'] = call_user_func_array('sprintf', $args);
		//
		self::redirectTo( 'error' );
	}//throwErrorF


	public static function throwException( $exceptionMsg ){
		self::throwError( $exceptionMsg );
	}//throwException


	// public static function sendEMail($to, $subject, $template, $replace, $replyTo=null){
	// 	// prepare the message body
	// 	$res = EmailTemplates::fill( $template, $replace );
	// 	if( !$res['success'] ){
	// 		return $res;
	// 	}
	// 	$body = $res['data'];
	// 	// create the mail object
	// 	$mail = new \PHPMailer();
	// 	//
	// 	$mail->isSMTP();                                      				// Set mailer to use SMTP
	// 	$mail->Host = Configuration::$NOREPLY_MAIL_HOST;	  				// Specify main and backup SMTP servers
	// 	$mail->SMTPAuth = Configuration::$NOREPLY_MAIL_AUTH;  				// Enable SMTP authentication
	// 	$mail->Username = Configuration::$NOREPLY_MAIL_USERNAME;           	// SMTP username
	// 	$mail->Password = Configuration::$NOREPLY_MAIL_PASSWORD;      		// SMTP password
	// 	if( !in_array( Configuration::$NOREPLY_MAIL_SECURE_PROTOCOL, array('', 'none') ) ){
	// 		$mail->SMTPSecure = Configuration::$NOREPLY_MAIL_SECURE_PROTOCOL;  	// Enable TLS encryption, `ssl` also accepted
	// 	}
	// 	$mail->Port = Configuration::$NOREPLY_MAIL_SERVER_PORT;
	// 	//
	// 	$mail->From = Configuration::$NOREPLY_MAIL_ADDRESS;
	// 	$mail->FromName = self::getSiteName();
	// 	$mail->addAddress( $to );     										// Add a recipient
	// 	//
	// 	if( $replyTo !== null ){
	// 		$mail->addReplyTo( $replyTo['email'], $replyTo['name'] );
	// 	}
	// 	//
	// 	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    			// Add an Attachment
	// 	$mail->isHTML(true);                                  				// Set email format to HTML
	// 	//
	// 	$mail->Subject = $subject;
	// 	$mail->Body = $body;
	// 	//
	// 	if(!$mail->send()) {
	// 		return array( 'success' => false, 'data' => $mail->ErrorInfo );
	// 	} else {
	// 		return array( 'success' => true, 'data' => null );
	// 	}
	// }//sendEMail


	public static function getErrorRecordsList(){
		// open errors DB
		$errors_db = new Database('core', 'errors');
		// get list of keys
		return $errors_db->list_keys();
	}//getErrorRecordsList


	public static function getErrorRecord( $error_id ){
		// open errors DB
		$errors_db = new Database('core', 'errors');
		// get item
		return $errors_db->read( $error_id );
	}//getErrorRecord


	public static function collectErrorInfo( $error_msg ){
		// open errors DB
		$errors_db = new Database('core', 'errors');
		// get user info
		$user = null;
		if( self::isUserLoggedIn() )
			$user = Core::getUserLogged('username');
		// create error record
		$error_id = strtotime("now");
		$error = [
			'id' => $error_id,
			'datetime' => gmdate("Y-m-d H:i:s", $error_id),
			'message' => $error_msg,
			'user' => $user
		];
		// push error to DB
		$errors_db->write( $error_id, $error );
	}//collectErrorInfo


	public static function deleteErrorRecord( $error_id ){
		// open errors DB
		$errors_db = new Database('core', 'errors');
		// remove item
		return $errors_db->delete( $error_id );
	}//deleteErrorRecord


	public static function collectDebugInfo( $package, $test_id, $test_value, $test_type ){
		if( !Configuration::$DEBUG ) return;
		if( !key_exists($package, self::$debugger_data) ) self::$debugger_data[$package] = array();
		// add debug test tuple
		self::$debugger_data[$package][$test_id] = [ $test_value, $test_type ];
	}//collectDebugInfo

	// TODO: DO NOT USE: moving to Utils, use Utils::generateRandomString() instead
	public static function generateRandomString( $length ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = mb_strlen($chars);
		//
		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}
		return $result;
	}//generateRandomString


	public static function verbose( $verbose_flag=True ){
		self::$verbose = $verbose_flag;
	}//verbose


	public static function debug( $debug_flag=True ){
		self::$debug = $debug_flag;
	}//debug


	public static function log( $type, $message, ...$args ){
		if( self::$debug ){
			echo vsprintf( $message, $args );
			echo '<br>';
		}
	}//log


  public static function regenerateSessionID( $delete_old_session = false ){
		session_regenerate_id( $delete_old_session );
	}//regenerateSessionID



	// =================================================================================================================
	// =================================================================================================================
	//
	//
	// Private functions



	/**
	 * Recursive dependency resolution
	 *
	 * @param string $item Item to resolve dependencies for
	 * @param array $items List of all items with dependencies
	 * @param array $resolved List of resolved items
	 * @param array $unresolved List of unresolved items
	 * @return array
	 */
	function _dep_solve_dependencies_graph($item, array $items, array $resolved, array $unresolved) {
	    array_push($unresolved, $item);
	    foreach ($items[$item] as $dep) {
	        if (!in_array($dep, $resolved)) {
	            if (!in_array($dep, $unresolved)) {
	                array_push($unresolved, $dep);
	                list($resolved, $unresolved) = self::_dep_solve_dependencies_graph($dep, $items, $resolved, $unresolved);
	            } else {
	                throw new \RuntimeException("Circular dependency: $item -> $dep");
	            }
	        }
	    }
	    // add $item to $resolved if it's not already there
	    if (!in_array($item, $resolved)) {
	        array_push($resolved, $item);
	    }
	    // remove all occurrences of $item in $unresolved
	    while (($index = array_search($item, $unresolved)) !== false) {
	        unset($unresolved[$index]);
	    }
		//
	    return [$resolved, $unresolved];
	}//_dep_solve_dependencies_graph


	private static function _solve_dependencies_graph( $graph ){
		$resolved = [];
		$unresolved = [];
		// resolve dependencies for each node
		foreach( array_keys($graph) as $node ){
		    try {
		        list ($resolved, $unresolved) = self::_dep_solve_dependencies_graph($node, $graph, $resolved, $unresolved);
		    } catch (\Exception $e) {
		        return ['success' => false, 'data' => $e->getMessage()];
		    }
		}
		//
		return ['success' => true, 'data' => $resolved];
	}//_solve_dependencies_graph


	private static function _load_packages_settings( $core_only=false ){
		// check if this object is cached
		$cache_key = sprintf( "packages_settings%s", $core_only? '_core_only' : '' );
		if( self::$cache->has( $cache_key ) ) return self::$cache->get( $cache_key );
		//
		$packages = self::getPackagesList();
		$packages_ids = array_keys( $packages );
		$settings = [];
		// iterate over the packages
		foreach( $packages_ids as $pkg_id ){
			if( $core_only && $pkg_id != 'core' ) continue;
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
		// cache object
		self::$cache->set( $cache_key, $settings, CacheTime::HOURS_24 );
		//
		return $settings;
	}//_load_packages_settings


	/*	Loads and returns the list of pages available in every package installed on the platform.
	*TODO: add return description
	*/
	private static function _load_available_pages( $core_only=false ){
		// check if this object is cached
		$cache_key_pages = sprintf( "available_pages%s", $core_only? '_core_only' : '' );
		$cache_key_user_types = sprintf( "user_types%s", $core_only? '_core_only' : '' );
		if( self::$cache->has($cache_key_pages) && self::$cache->has($cache_key_user_types) ){
			self::$registered_user_roles = self::$cache->get( $cache_key_user_types );
			return self::$cache->get( $cache_key_pages );
		}
		//
		$packages = self::getPackagesList();
		$packages_ids = array_keys( $packages );
		// iterate over the packages
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
			if( $core_only && $pkg_id != 'core' ) continue;
			$pages_descriptors = sprintf("%s%s/pages/*/metadata.json", $GLOBALS['__PACKAGES__DIR__'], $pkg_id);
			$jsons = glob( $pages_descriptors );
			$pages['by-package'][$pkg_id] = [];
			//
			foreach ($jsons as $json) {
				$page_id = Utils::regex_extract_group($json, "/.*pages\/(.+)\/metadata.json/", 1);
				$page_path = Utils::regex_extract_group($json, "/(.+)\/metadata.json/", 1);
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
				foreach( $page['access_level'] as $lvl ){
					$parts = explode(':', $lvl);
					$package = ( count($parts) == 1 )? 'core' : $parts[0];
					$role = ( count($parts) == 1 )? $parts[0] : $parts[1];
					self::registerNewUserRole($package, $role);
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
		// cache objects
		self::$cache->set( $cache_key_pages, $pages, CacheTime::HOURS_24 );
		self::$cache->set( $cache_key_user_types, self::$registered_user_roles, CacheTime::HOURS_24 );
		//
		return $pages;
	}//_load_available_pages


	private static function _load_available_packages( $core_only=false ){
		// check if this object is cached
		$cache_key = sprintf( "available_packages%s", $core_only? '_core_only' : '' );
		if( self::$cache->has( $cache_key ) ) return self::$cache->get( $cache_key );
		//
		$pkgs_descriptors = $GLOBALS['__PACKAGES__DIR__']."*/metadata.json";
		$jsons = glob( $pkgs_descriptors );
		// check if this object is cached
		$cache_key = sprintf( "available_packages%s", $core_only? '_core_only' : '' );
		if( self::$cache->has( $cache_key ) ) return self::$cache->get( $cache_key );
		// iterate over the packages
		$pkgs = [];
		foreach ($jsons as $json) {
			$pkg_id = Utils::regex_extract_group($json, "/.*packages\/(.+)\/metadata.json/", 1);
			if( $core_only && $pkg_id != 'core' ) continue;
			$pkg_path = Utils::regex_extract_group($json, "/(.+)\/metadata.json/", 1);
			$pkg = json_decode( file_get_contents($json), true );
			$pkg['id'] = $pkg_id;
			if( !key_exists('core', $pkg) ){
				$pkg['core'] = null;
				$pkg_core_file = sprintf( "%s/%s.php", $pkg_path, ucfirst($pkg_id) );
				if( file_exists($pkg_core_file) ){
					$pkg['core'] = [
						'namespace' => $pkg_id,
						'file' => sprintf( "%s.php", ucfirst($pkg_id) ),
						'class' => ucfirst($pkg_id)
					];
				}
			}
			$pkg['core']['file'] = sprintf( "%s/%s", $pkg_path, $pkg['core']['file'] );
			// check whether the package is enabled
			$pkg['enabled'] = self::isPackageEnabled($pkg_id);
      // get package codebase version
      $pkg['codebase'] = self::getPackageCodebaseInfo($pkg_id);
			// load modules
			self::_load_package_modules_list($pkg_id, $pkg);
			// create public data symlink (if it does not exist)
			$sym_link = sprintf( "%s%s", $GLOBALS['__DATA__DIR__'], $pkg_id );
			$sym_link_exists = file_exists($sym_link);
			if( !$sym_link_exists ){
				$public_data_dir = sprintf( "%s%s/data/public", $GLOBALS['__PACKAGES__DIR__'], $pkg_id );
				$pubdata_exists = file_exists($public_data_dir);
				if( $pubdata_exists ){
					$symlink_success = symlink($public_data_dir, $sym_link);
				}
			}
			// by-id
			$pkgs[$pkg_id] = $pkg;
		}
		// cache object
		self::$cache->set( $cache_key, $pkgs, CacheTime::HOURS_24 );
		//
		return $pkgs;
	}//_load_available_packages


	private static function _load_package_modules_list( &$pkg_id, &$package_descriptor ){
		$package_descriptor['modules'] = [
			'renderers/blocks' => []
		];
		// load renderers
		// => block renderers
		$block_rends_path = sprintf( "%s%s/modules/renderers/blocks/*.php", $GLOBALS['__PACKAGES__DIR__'], $pkg_id );
		$block_rends = glob( $block_rends_path );
		$package_descriptor['modules']['renderers/blocks'] = $block_rends;
	}//_load_package_modules_list

}//Core

?>

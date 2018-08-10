<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

namespace system\classes;

use system\classes\Core;
use system\classes\Utils;
use system\classes\Database;
use system\classes\enum\CacheTime;


/** RESTfulAPI class: provides an interface for configuring the RESTfulAPI module.
 */
class RESTfulAPI{

	private static $initialized = false;
	private static $settings = false;
	private static $configuration = false;
	private static $cache = null;


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
	public static function init(){
		if( !self::$initialized ){
			//
			// create cache proxy
			self::$cache = new CacheProxy('api');
			//
			// load API settings
			self::$settings = self::_load_API_settings();
			//
			// load API configuration
			self::$configuration = self::_load_API_configuration();
			//
			self::$initialized = true;
			return array( 'success' => true, 'data' => null );
		}else{
			return array( 'success' => true, 'data' => "Module already initialized!" );
		}
	}//init


	// =======================================================================================================
	// API management functions

	/** Returns whether the RESTfulAPI module is initialized.
	 *
	 *	@retval boolean
	 * 		whether the RESTfulAPI module is initialized;
	 */
	public static function isInitialized(){
		return self::$initialized;
	}//isInitialized

	/*	Returns the setup of the RESTfulAPI module. For more info about the settings
	 *  check the file `/system/api/web-api-settings.json`.
	*/
	public static function getSettings(){
		return self::$settings;
	}//getConfiguration


	/*	TODO @todo Returns the list of API services installed on the platform.
	*/
	public static function getConfiguration(){
		return self::$configuration;
	}//getConfiguration


	/** Returns whether the RESTfulAPI module is enabled.
	 *
	 *	@retval boolean
	 * 		whether the RESTfulAPI module is enabled;
	 */
	public static function webAPIenabled(){
		if( self::isInitialized() )
			return self::$settings['webapi-enabled'];
		throw new \Exception("Module not initialized", 1);
	}//webAPIenabled


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
	public static function serviceExists( $api_version, $service_name ){
		if( self::isInitialized() )
			return isset(self::$configuration[$api_version]) && isset(self::$configuration[$api_version]['services'][$service_name]);
		throw new \Exception("Module not initialized", 1);
	}//serviceExists


	/** Returns whether the specified API service is enabled.
	 *
	 *	If the API service does not exist, the call will return `FALSE`.
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
	public static function isServiceEnabled( $api_version, $service_name ){
		if( !self::isInitialized() )
			throw new \Exception("Module not initialized", 1);
		//
		if( !self::serviceExists($api_version, $service_name) ) return false;
		// open API service status database
		$db_name = sprintf('api_%s_disabled_service', $api_version);
		$service_db = new Database('core', $db_name);
		// key exists == service is disabled
		return !$service_db->key_exists($service_name);
	}//isServiceEnabled


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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function enableService( $api_version, $service_name ){
		if( !self::isInitialized() )
			throw new \Exception("Module not initialized", 1);
		// make sure that the service exists
		if( !self::serviceExists($api_version, $service_name) )
			return ['success' => false, 'data' => sprintf('The API service "%s(v%s)" does not exist', $service_name, $api_version)];
		// open API service status database
		$db_name = sprintf('api_%s_disabled_service', $api_version);
		$service_db = new Database('core', $db_name);
		if( $service_db->key_exists($service_name) ){
			return $service_db->delete($service_name);
		}
		return ['success' => true, 'data' => null];
	}//enableService


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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function disableService( $api_version, $service_name ){
		if( !self::isInitialized() )
			throw new \Exception("Module not initialized", 1);
		// avoid disabling things that cannot be re-enabled
		if( $service_name == 'api' )
			return ['success' => false, 'data' => sprintf('The API service "%s" cannot be disabled', $service_name)];
		// make sure that the service exists
		if( !self::serviceExists($api_version, $service_name) )
			return ['success' => false, 'data' => sprintf('The API service "%s(v%s)" does not exist', $service_name, $api_version)];
		// open API service status database
		$db_name = sprintf('api_%s_disabled_service', $api_version);
		$service_db = new Database('core', $db_name);
		return $service_db->write($service_name, []);
	}//disableService


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
	public static function actionExists( $api_version, $service_name, $action_name ){
		if( !self::isInitialized() )
			throw new \Exception("Module not initialized", 1);
		//
		$api_setup = self::getConfiguration();
		return isset($api_setup[$api_version])
			&& isset($api_setup[$api_version]['services'][$service_name])
			&& isset($api_setup[$api_version]['services'][$service_name]['actions'][$action_name]);
	}//actionExists


	/** Returns whether the specified API action is enabled.
	 *
	 *	If the API action does not exist, the call will return `FALSE`.
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
	public static function isActionEnabled( $api_version, $service_name, $action_name ){
		if( !self::isInitialized() )
			throw new \Exception("Module not initialized", 1);
		// make sure that the service exists
		if( !self::actionExists($api_version, $service_name, $action_name) ) return false;
		// open API action status database
		$db_name = sprintf('api_%s_disabled_action', $api_version);
		$action_db = new Database('core', $db_name);
		// key exists == action is disabled
		$action_key = sprintf('%s_%s', $service_name, $action_name);
		return !$action_db->key_exists($action_key);
	}//isActionEnabled


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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function enableAction( $api_version, $service_name, $action_name ){
		if( !self::isInitialized() )
			throw new \Exception("Module not initialized", 1);
		// make sure that the service exists
		if( !self::actionExists($api_version, $service_name, $action_name) )
			return ['success' => false, 'data' => sprintf('The API action "%s.%s(v%s)" does not exist', $service_name, $action_name, $api_version)];
		// open API action status database
		$db_name = sprintf('api_%s_disabled_action', $api_version);
		$action_db = new Database('core', $db_name);
		// remove key if it exists
		$action_key = sprintf('%s_%s', $service_name, $action_name);
		if( $action_db->key_exists($action_key) ){
			return $action_db->delete($action_key);
		}
		return ['success' => true, 'data' => null];
	}//enableAction


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
	 *		"success" => boolean, 	// whether the call succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the call succeded.
	 *		The `data` field contains an error string when `success` is `FALSE`.
	 */
	public static function disableAction( $api_version, $service_name, $action_name ){
		if( !self::isInitialized() )
			throw new \Exception("Module not initialized", 1);
		// avoid disabling things that cannot be re-enabled
		if( $service_name == 'api' && in_array($action_name, ['service_enable', 'action_enable']) )
			return ['success' => false, 'data' => sprintf('The API action "%s.%s" cannot be disabled', $service_name, $action_name)];
		// make sure that the action exists
		if( !self::actionExists($api_version, $service_name, $action_name) )
			return ['success' => false, 'data' => sprintf('The API action "%s.%s(v%s)" does not exist', $service_name, $action_name, $api_version)];


		// open API action status database
		$db_name = sprintf('api_%s_disabled_action', $api_version);
		$action_db = new Database('core', $db_name);
		// create key if it does not exist
		$action_key = sprintf('%s_%s', $service_name, $action_name);
		return $action_db->write($action_key, []);
	}//disableAction



	// =======================================================================================================
	// User Applications management functions

	/** TODO: Returns a list of applications with app_key...
	 */
	public static function getUserApplications( $username ){
		// open applications DB for the current/given user
		$apps_db = new Database( 'core', 'api_applications', self::_build_app_db_regex($username) );
		// iterate through the apps
		$apps = [];
		foreach($apps_db->list_keys() as $app_id){
			$app = $apps_db->read($app_id);
			if( $app['success'] ){
				array_push($apps, $app['data']);
			}else{
				return $app;
			}
		}
		// return list of apps
		return ['success' => true, 'data'=>$apps];
	}//getUserApplications


	/** TODO: Returns a list of applications with app_key...
	 */
	public static function getApplication( $app_id ){
		// open applications DB
		$apps_db = new Database( 'core', 'api_applications' );
		// make sure that the app exists
		if( !$apps_db->key_exists($app_id) )
			return ['success'=>false, 'data'=>sprintf('No application found with ID `%s`', $app_id)];
		// retrieve the app
		$res = $apps_db->read($app_id);
		// return app
		return $res;
	}//getUserApplication

	/** TODO: Creates a new app...
	 */
	public static function createApplication( $app_name, $endpoints, $app_enabled=true, $username=null ){
		if( is_null($username) ){
			if( !Core::isUserLoggedIn() )
				return ['success'=>false, 'data'=>'Only logged users are allowed to create API Applications'];
			// get user id
			$username = Core::getUserLogged('username');
		}
		// open applications DB for the current/given user
		$apps_db = new Database( 'core', 'api_applications', self::_build_app_db_regex($username) );
		// compose the app_id as <username>.<app_id>
		$app_id = sprintf('%s_%s', $username, Utils::string_to_valid_filename($app_name));
		// make sure the app does not exist
		if( $apps_db->key_exists($app_id) ){
			return ['success'=>false, 'data'=>'Another application with the same name is already present. Choose another name and retry'];
		}
		// get user record
		$res = Core::getUserInfo($username);
		if( !$res['success'] ) return $res;
		$user = $res['data'];
		// make sure that the user does not gain powers s/he is not supposed to have
		$endpoints = array_intersect($endpoints, self::_endpoints_per_role($user['role']));
		// create app
		$app_data = [
			'id' => $app_id,
			'user' => $username,
			'name' => $app_name,
			'secret' => Utils::generateRandomString(48),
			'endpoints' => $endpoints,
			'enabled' => boolval($app_enabled)
		];
		return $apps_db->write( $app_id, $app_data );
	}//createApplication


	/** TODO: Edits an app...
	 */
	public static function updateApplication( $app_id, $endpoints_up, $endpoints_dw, $app_enabled=null, $username=null ){
		if( is_null($username) ){
			if( !Core::isUserLoggedIn() )
				return ['success'=>false, 'data'=>'Only logged users are allowed to update API Applications'];
			// get user id
			$username = Core::getUserLogged('username');
		}
		// open applications DB for the current/given user
		$apps_db = new Database( 'core', 'api_applications', self::_build_app_db_regex($username) );
		// make sure that the app exists
		if( !$apps_db->key_exists($app_id) ){
			return ['success'=>false, 'data'=>sprintf('The application with ID `%s` does not exist', $app_id)];
		}
		// retrieve the app to update
		$res = $apps_db->get_entry( $app_id );
		if( !$res['success'] ) return $res;
		$app = $res['data'];
		// get the list of active API end-points associated to this app
		// NOTE: array_keys(array_flip()) is similar to array_unique() for array w/o keys
		$endpoints_orig = $app->get('endpoints');
		$endpoints = array_keys(array_flip( array_merge( array_diff($endpoints_orig, $endpoints_dw), $endpoints_up) ));
		// get user record
		$res = Core::getUserInfo($username);
		if( !$res['success'] ) return $res;
		$user = $res['data'];
		// make sure that the user does not gain powers s/he is not supposed to have
		$endpoints = array_intersect($endpoints, self::_endpoints_per_role($user['role']));
		// update app
		$app->set( 'endpoints', $endpoints );
		// maintain status if not passed
		if( !is_null($app_enabled) ){
			$app->set( 'enabled', boolval($app_enabled) );
		}
		// write to disk and return
		return $app->commit();
	}//updateApplication


	/** TODO: Deletes an app...
	 */
	public static function deleteApplication( $app_id, $username=null ){
		if( is_null($username) ){
			if( !Core::isUserLoggedIn() )
				return ['success'=>false, 'data'=>'Only logged users are allowed to delete API Applications'];
			// get user id
			$username = Core::getUserLogged('username');
		}
		// open applications DB for the current/given user
		$apps_db = new Database( 'core', 'api_applications', self::_build_app_db_regex($username) );
		// remove entry
		return $apps_db->delete( $app_id );
	}//deleteApplication



	// =================================================================================================================
	// =================================================================================================================
	//
	//
	// Private functions

	private static function _build_app_db_regex( $username=null, $app_id=null ){
		return sprintf(
			"/^%s_%s$/",
			is_null($username)? '[0-9]{21}' : $username,
			is_null($app_id)? '[A-Za-z0-9_]+' : $app_id
		);
	}//_build_app_db_regex

	private static function _endpoints_per_role( $user_role, $app_auth_only=true ){
		$endpoints = [];
		foreach( self::$configuration as $pkg_id => &$pkg_api ){
		    foreach( $pkg_api['services'] as $service_id => &$service_config ){
		        foreach( $service_config['actions'] as $action_id => &$action_config ){
					if( $app_auth_only && !in_array('app', $action_config['authentication']) ) continue;
					if( in_array($user_role, $action_config['access_level']) ){
						$pair = sprintf('%s/%s', $service_id, $action_id);
			            array_push( $endpoints, $pair );
					}
		        }
		    }
		}
		return $endpoints;
	}//_endpoints_per_role

	private static function _load_API_settings(){
		// check if this object is cached
		$cache_key = "api_settings";
		if( self::$cache->has( $cache_key ) ) return self::$cache->get( $cache_key );
		// load global settings
		$settings_file = sprintf("%s/api/web-api-settings.json", $GLOBALS['__SYSTEM__DIR__']);
		$settings = json_decode( file_get_contents($settings_file), true );
		// cache object
		self::$cache->set( $cache_key, $settings, CacheTime::HOURS_24 );
		//
		return $settings;
	}//_load_API_settings

	private static function _load_API_configuration(){
		// check if this object is cached
		$cache_key = "api_configuration";
		if( self::$cache->has( $cache_key ) ) return self::$cache->get( $cache_key );
		// get list of packages
		$packages = Core::getPackagesList();
		$packages_ids = array_keys( $packages );
		// create resulting object
		$api = [];
		foreach( self::$settings['versions'] as $v => $v_specs ){
			$api[$v] = [
				'services' => [],
				'global' => self::$settings['global'],
				'enabled' => $v_specs['enabled']
			];
		}
		// iterate over the API versions -> packages -> services -> actions
		foreach( $api as $api_version => &$api_v_specs ){
			$api_v_enabled = $api_v_specs['enabled'];
			// open API service status database
			$db_name_srv = sprintf('api_%s_disabled_service', $api_version);
			$service_status_db = new Database('core', $db_name_srv);
			// open API action status database
			$db_name_act = sprintf('api_%s_disabled_action', $api_version);
			$action_status_db = new Database('core', $db_name_act);
			// iterate over the packages
			foreach( $packages_ids as $pkg_id ){
				$api_services_descriptors = sprintf("%s/../packages/%s/modules/api/%s/api-services/specifications/*.json", __DIR__, $pkg_id, $api_version);
				$jsons = glob( $api_services_descriptors );
				// iterate over the API services
				foreach ($jsons as $json) {
					$api_service_id = Utils::regex_extract_group($json, "/.*api\/(.+)\/api-services\/specifications\/(.+).json/", 2);
					// get service name
					$api_services_path_regex = sprintf( "/(.+)\/specifications\/%s.json/", $api_service_id );
					$api_service_executor_path = sprintf(
						"%s/executors/%s.php",
						Utils::regex_extract_group($json, $api_services_path_regex, 1),
						$api_service_id
					);
					// load service settings
					$api_service = json_decode( file_get_contents($json), true );
					$api_service['package'] = $pkg_id;
					$api_service['id'] = $api_service_id;
					$api_service['executor'] = $api_service_executor_path;
					// check whether the service is enabled (key exists == service is disabled)
					$api_service['enabled'] = !$service_status_db->key_exists($api_service_id);
					$api_service['enabled'] = $api_v_enabled && $packages[$pkg_id]['enabled'] && $api_service['enabled'];
					// iterate over the API actions
					foreach( $api_service['actions'] as $api_action_id => &$api_action ){
						$action_key = sprintf('%s_%s', $api_service_id, $api_action_id);
						$api_action['enabled'] = !$action_status_db->key_exists($action_key);
						$api_action['enabled'] = $api_service['enabled'] && $api_action['enabled'];
						// collect user types
						foreach( $api_action['access_level'] as $user_type ){
							Core::registerNewUserType( $user_type );
						}
					}//for:action
					// attach service config to API specs object
					$api_v_specs['services'][$api_service_id] = $api_service;
				}//for:service
			}//for:package
		}//for:version
		// cache object
		self::$cache->set( $cache_key, $api, CacheTime::HOURS_24 );
		// return api config object
		return $api;
	}//_load_API_configuration

}//RESTfulAPI

?>

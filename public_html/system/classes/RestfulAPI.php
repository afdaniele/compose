<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

namespace system\classes;

use system\classes\Core;
use system\classes\Utils;
use system\classes\enum\CacheTime;


/** RestfulAPI class: provides an interface for configuring the RestfulAPI module.
 */
class RestfulAPI{

	private static $initialized = false;
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
			// load API configuration
			self::$configuration = self::_load_API_setup();
			//
			self::$initialized = true;
			return array( 'success' => true, 'data' => null );
		}else{
			return array( 'success' => true, 'data' => "Module already initialized!" );
		}
	}//init


	// =======================================================================================================
	// API management functions

	/*	TODO @todo Returns the list of API services installed on the platform.
	*/
	public static function getConfiguration(){
		return self::$configuration;
	}//getConfiguration


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
		return isset(self::$configuration[$api_version]) && isset(self::$configuration[$api_version]['services'][$service_name]);
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
		if( !self::serviceExists($api_version, $service_name) ) return false;
		//TODO: storing this info in a Database and checking using `exists($api_version.'_'.$service_name)` would be more efficient
		$service_disabled_flag = sprintf('%sapi/%s/flags/%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name);
		return !file_exists($service_disabled_flag);
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
		if( !self::serviceExists($api_version, $service_name) )
			return ['success' => false, 'data' => sprintf('The API service "%s(v%s)" does not exist', $service_name, $api_version)];
		$service_disabled_flag = sprintf('%sapi/%s/flags/%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name);
		if( file_exists($service_disabled_flag) ){
			$success = unlink( $service_disabled_flag );
			return ['success' => $success, 'data' => null];
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
		// avoid disabling things that cannot be re-enabled
		if( $service_name == 'api' )
			return ['success' => false, 'data' => sprintf('The API service "%s" cannot be disabled', $service_name)];
		if( !self::serviceExists($api_version, $service_name) )
			return ['success' => false, 'data' => sprintf('The API service "%s(v%s)" does not exist', $service_name, $api_version)];
		$service_disabled_flag = sprintf('%sapi/%s/flags/%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name);
		if( !file_exists($service_disabled_flag) ){
			$success = touch( $service_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
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
		if( !self::actionExists($api_version, $service_name, $action_name) ) return false;
		$action_disabled_flag = sprintf('%sapi/%s/flags/%s.%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name, $action_name);
		return !file_exists($action_disabled_flag);
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
		if( !self::actionExists($api_version, $service_name, $action_name) )
			return ['success' => false, 'data' => sprintf('The API action "%s.%s(v%s)" does not exist', $service_name, $action_name, $api_version)];
		$action_disabled_flag = sprintf('%sapi/%s/flags/%s.%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name, $action_name);
		if( file_exists($action_disabled_flag) ){
			$success = unlink( $action_disabled_flag );
			return ['success' => $success, 'data' => null];
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
		// avoid disabling things that cannot be re-enabled
		if( $service_name == 'api' && in_array($action_name, ['service_enable', 'action_enable']) )
			return ['success' => false, 'data' => sprintf('The API action "%s.%s" cannot be disabled', $service_name, $action_name)];
		if( !self::actionExists($api_version, $service_name, $action_name) )
			return ['success' => false, 'data' => sprintf('The API action "%s.%s(v%s)" does not exist', $service_name, $action_name, $api_version)];
		$action_disabled_flag = sprintf('%sapi/%s/flags/%s.%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $service_name, $action_name);
		if( !file_exists($action_disabled_flag) ){
			$success = touch( $action_disabled_flag );
			return ['success' => $success, 'data' => null];
		}
		return ['success' => true, 'data' => null];
	}//disableAction



	// =================================================================================================================
	// =================================================================================================================
	//
	//
	// Private functions

	private static function _load_API_setup(){
		// check if this object is cached
		$cache_key = "api_configuration";
		if( self::$cache->has( $cache_key ) ) return self::$cache->get( $cache_key );
		//
		$packages = Core::getPackagesList();
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
		// iterate over the API versions -> packages -> services -> actions
		foreach( $api as $api_version => &$api_v_specs ){
			$api_v_enabled = $api_v_specs['enabled'];
			foreach( $packages_ids as $pkg_id ){
				if( $core_only && $pkg_id != 'core' ) continue;
				$api_services_descriptors = sprintf("%s/../packages/%s/modules/api/%s/api-services/specifications/*.json", __DIR__, $pkg_id, $api_version);
				$jsons = glob( $api_services_descriptors );
				//
				foreach ($jsons as $json) {
					$api_service_id = Utils::regex_extract_group($json, "/.*api\/(.+)\/api-services\/specifications\/(.+).json/", 2);
					//
					$api_services_path_regex = sprintf( "/(.+)\/specifications\/%s.json/", $api_service_id );
					$api_service_executor_path = sprintf(
						"%s/executors/%s.php",
						Utils::regex_extract_group($json, $api_services_path_regex, 1),
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
					foreach( $api_service['actions'] as $api_action_id => &$api_action ){
						$api_action_disabled_flag = sprintf('%sapi/%s/flags/%s.%s.disabled.flag', $GLOBALS['__SYSTEM__DIR__'], $api_version, $api_service_id, $api_action_id);
						$api_action['enabled'] = !file_exists($api_action_disabled_flag);
						$api_action['enabled'] = $api_service['enabled'] && $api_action['enabled'];
						// collect user types
						foreach( $api_action['access_level'] as $user_type ){
							Core::registerNewUserType( $user_type );
						}
					}
					//
					$api_v_specs['services'][$api_service_id] = $api_service;
				}
			}
		}
		// cache object
		self::$cache->set( $cache_key, $api, CacheTime::HOURS_24 );
		//
		return $api;
	}//_load_API_setup

}//RestfulAPI

?>

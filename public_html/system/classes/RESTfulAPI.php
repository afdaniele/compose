<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

namespace system\classes;

require_once __DIR__ . '/api/services.php';
require_once __DIR__ . '/api/actions.php';
require_once __DIR__ . '/api/settings.php';
require_once __DIR__ . '/api/response.php';

use exceptions\APIActionNotFoundException;
use exceptions\APIApplicationNotFoundException;
use exceptions\APIServiceNotFoundException;
use exceptions\DatabaseKeyNotFoundException;
use exceptions\FileNotFoundException;
use exceptions\GenericException;
use exceptions\InvalidSchemaException;
use exceptions\IOException;
use exceptions\ModuleNotInitializedException;
use exceptions\NotLoggedInException;
use exceptions\SchemaViolationException;
use JetBrains\PhpStorm\Pure;
use system\classes\api\RESTfulAPIService;
use system\classes\api\RESTfulAPISettings;
use system\classes\enum\CacheTime;



/** RESTfulAPI class: provides an interface for configuring the RESTfulAPI module.
 *
 * @package system\classes
 */
class RESTfulAPI {
    
    private static bool $initialized = false;
    private static RESTfulAPISettings|null $settings = null;
    private static CacheProxy|null $cache = null;
    
    /** This array has the shape:
     *      [
     *          "1.0": [
     *                      "<service_id>" => <RESTfulAPIService>,
     *                      ...
     *          ],
     *          ...
     *      ]
     */
    private static array $endpoints = [];
    
    
    //Disable the constructor
    private function __construct() {}
    
    
    
    // =======================================================================================================
    // Initilization and session management functions
    
    
    /** Initializes the Core module.
     *    It is the first function to call when using the Core module.
     *
     * @throws FileNotFoundException
     * @throws IOException
     * @throws InvalidSchemaException
     * @throws SchemaViolationException
     */
    public static function init() {
        if (!self::$initialized) {
            // create cache proxy
            self::$cache = new CacheProxy('api');
            // load API settings
            self::$settings = self::loadAPISettings();
            // load API configuration
            self::$endpoints = self::loadAPIEndpoints();
            // ---
            self::$initialized = true;
        }
    }//init
    
    
    // =======================================================================================================
    // API management functions
    
    /** Returns whether the RESTfulAPI module is initialized.
     *
     * @return boolean
     *        whether the RESTfulAPI module is initialized;
     */
    public static function isInitialized() {
        return self::$initialized;
    }//isInitialized
    
    /** Screams when the RESTfulAPI module is not initialized.
     * @param string|null $attribute
     */
    private static function assertInitialized(string $attribute = null) {
        if (!self::isInitialized())
            throw new ModuleNotInitializedException("RESTfulAPI", $attribute);
    }//assertInitialized
    
    /**	Returns the setup of the RESTfulAPI module. For more info about the settings
     *  check the file `/system/api/settings.json`.
     *
    */
    public static function getSettings(): RESTfulAPISettings {
        self::assertInitialized("getSettings");
        return self::$settings;
    }//getConfiguration
    
    
    /** Returns a list of endpoints grouped by API version.
    *   @return array
    */
    public static function getEndpoints(): array {
        self::assertInitialized("getConfiguration");
        return self::$endpoints;
    }//getConfiguration
    
    
    /** Returns whether the given API service is installed on the platform.
     *
     * @param string $api_version
     *        the version of the API the service to check belongs to;
     *
     * @param string $service
     *        the name of the API service to check;
     *
     * @return bool
     *        whether the API service exists;
     */
    public static function serviceExists(string $api_version, string $service): bool {
        self::assertInitialized("serviceExists");
        return isset(self::$endpoints[$api_version]) && isset(self::$endpoints[$api_version][$service]);
    }//serviceExists
    
    
    /** Returns whether the specified API service is enabled.
     *
     *    If the API service does not exist, the call will return `FALSE`.
     *
     * @param string $api_version
     *        the version of the API the service to check belongs to;
     *
     * @param string $service
     *        the name of the API service to check;
     *
     * @return bool whether the API service exists and is enabled.
     */
    public static function isServiceEnabled(string $api_version, string $service): bool {
        self::assertInitialized("isServiceEnabled");
        //
        if (!self::serviceExists($api_version, $service)) {
            return false;
        }
        // open API service status database
        $db_name = sprintf('api_%s_disabled_service', $api_version);
        $service_db = new Database('core', $db_name);
        // key exists == service is disabled
        return !$service_db->key_exists($service);
    }//isServiceEnabled
    
    
    /** Enables an API service.
     *
     * @param string $api_version
     *        the version of the API the service to enable belongs to;
     *
     * @param string $service
     *        the name of the API service to enable;
     *
     * @return bool
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function enableService(string $api_version, string $service): bool {
        self::assertInitialized("enableService");
        // make sure that the service exists
        if (!self::serviceExists($api_version, $service)) {
            throw new APIServiceNotFoundException($service);
        }
        // open API service status database
        $db_name = sprintf('api_%s_disabled_service', $api_version);
        $service_db = new Database('core', $db_name);
        if ($service_db->key_exists($service)) {
            return $service_db->delete($service);
        }
        return true;
    }//enableService
    
    
    /** Disables an API service.
     *
     * @param string $api_version
     *        the version of the API the service to disable belongs to;
     *
     * @param string $service
     *        the name of the API service to disable;
     *
     * @return bool
     */
    public static function disableService(string $api_version, string $service): bool {
        self::assertInitialized("disableService");
        // avoid disabling things that cannot be re-enabled
        if ($service == 'api') {
            throw new GenericException("The API service '$service' cannot be disabled");
        }
        // make sure that the service exists
        if (!self::serviceExists($api_version, $service)) {
            throw new APIServiceNotFoundException($service);
        }
        // open API service status database
        $db_name = sprintf('api_%s_disabled_service', $api_version);
        $service_db = new Database('core', $db_name);
        return $service_db->write($service, []);
    }//disableService
    
    
    /** Returns whether the given API action is installed on the platform.
     *
     * @param string $api_version
     *        the version of the API the action to check belongs to;
     *
     * @param string $service
     *        the name of the API service the action to check belongs to;
     *
     * @param string $action
     *        the name of the API action to check;
     *
     * @return bool
     *        whether the API action exists;
     */
    public static function actionExists(string $api_version, string $service, string $action): bool {
        self::assertInitialized("actionExists");
        $epoints = self::getEndpoints();
        return isset($epoints[$api_version])
            && isset($epoints[$api_version][$service])
            && $epoints[$api_version][$service]->hasAction($action);
    }//actionExists
    
    
    /** Returns whether the specified API action is enabled.
     *
     *    If the API action does not exist, the call will return `FALSE`.
     *
     * @param string $api_version
     *        the version of the API the action to check belongs to;
     *
     * @param string $service
     *        the name of the API service the action to check belongs to;
     *
     * @param string $action
     *        the name of the API action to check;
     *
     * @return bool
     *        whether the API action exists and is enabled;
     */
    public static function isActionEnabled(string $api_version, string $service, string $action): bool {
        self::assertInitialized("isActionEnabled");
        // make sure that the service exists
        if (!self::actionExists($api_version, $service, $action)) {
            return false;
        }
        // open API action status database
        $db_name = sprintf('api_%s_disabled_action', $api_version);
        $action_db = new Database('core', $db_name);
        // key exists == action is disabled
        $action_key = sprintf('%s_%s', $service, $action);
        return !$action_db->key_exists($action_key);
    }//isActionEnabled
    
    
    /** Enables an API action.
     *
     * @param string $api_version
     *        the version of the API the action to enable belongs to;
     *
     * @param string $service
     *        the name of the API service the action to enable belongs to;
     *
     * @param string $action
     *        the name of the API action to enable;
     *
     * @return bool
     */
    public static function enableAction(string $api_version, string $service, string $action): bool {
        self::assertInitialized("enableAction");
        // make sure that the service exists
        if (!self::actionExists($api_version, $service, $action)) {
            throw new APIActionNotFoundException($service, $action);
        }
        // open API action status database
        $db_name = sprintf('api_%s_disabled_action', $api_version);
        $action_db = new Database('core', $db_name);
        // remove key if it exists
        $action_key = sprintf('%s_%s', $service, $action);
        if ($action_db->key_exists($action_key)) {
            return $action_db->delete($action_key);
        }
        return true;
    }//enableAction
    
    
    /** Disables an API action.
     *
     * @param string $api_version
     *        the version of the API the action to disable belongs to;
     *
     * @param string $service
     *        the name of the API service the action to disable belongs to;
     *
     * @param string $action
     *        the name of the API action to disable;
     *
     * @return bool
     */
    public static function disableAction(string $api_version, string $service, string $action): bool {
        self::assertInitialized("disableAction");
        // avoid disabling things that cannot be re-enabled
        if ($service == 'api' && in_array($action, ['service_enable', 'action_enable'])) {
            throw new GenericException("The API action '$service.$action' cannot be disabled");
        }
        // make sure that the action exists
        if (!self::actionExists($api_version, $service, $action)) {
            throw new APIActionNotFoundException($service, $action);
        }
        // open API action status database
        $db_name = sprintf('api_%s_disabled_action', $api_version);
        $action_db = new Database('core', $db_name);
        // create key if it does not exist
        $action_key = sprintf('%s_%s', $service, $action);
        return $action_db->write($action_key, []);
    }//disableAction
    
    
    // =======================================================================================================
    // User Applications management functions
    
    /** Returns a list of applications for the given user.
     *
     * @param string $username      Username to get the applications for.
     * @return array
     */
    public static function getUserApplications(string $username) {
        self::assertInitialized("getUserApplications");
        // open applications DB for the current/given user
        $apps_db = new Database('core', 'api_applications', self::_build_app_db_regex($username));
        // iterate through the apps
        $apps = [];
        foreach ($apps_db->list_keys() as $app_id) {
            $app = $apps_db->read($app_id);
            array_push($apps, $app);
        }
        // return list of apps
        return $apps;
    }//getUserApplications
    
    
    /** Returns an API application.
     *
     * @param $app_id
     * @return array
     * @throws APIApplicationNotFoundException
     */
    public static function getApplication(string $app_id): array {
        self::assertInitialized("getApplication");
        // open applications DB
        $apps_db = new Database('core', 'api_applications');
        // retrieve the app
        try {
            return $apps_db->read($app_id);
        } catch (DatabaseKeyNotFoundException $e) {
            throw new APIApplicationNotFoundException($app_id, previous: $e);
        }
    }//getUserApplication
    
    /** Creates a new API application.
     *
     * @param string $app_name
     * @param array $endpoints
     * @param bool $app_enabled
     * @param string|null $username
     * @return array|bool
     */
    public static function createApplication(string $app_name, array $endpoints, bool $app_enabled = true, string $username = null) {
        self::assertInitialized("createApplication");
        if (is_null($username)) {
            if (!Core::isUserLoggedIn()) {
                return ['success' => false, 'data' => 'Only logged users are allowed to create API Applications'];
            }
            // get user id
            $username = Core::getUserLogged('username');
        }
        // open applications DB for the current/given user
        $apps_db = new Database('core', 'api_applications', self::_build_app_db_regex($username));
        // compose the app_id as <username>.<app_id>
        $app_id = sprintf('%s_%s', $username, Utils::string_to_valid_filename($app_name));
        // make sure the app does not exist
        if ($apps_db->key_exists($app_id)) {
            return ['success' => false, 'data' => 'Another application with the same name is already present. Choose another name and retry'];
        }
        // get user record
        $res = Core::getUserInfo($username);
        if (!$res['success']) {
            return $res;
        }
        $user = $res['data'];
        // make sure that the user does not gain powers s/he is not supposed to have
        $endpoints = array_intersect($endpoints, self::endpointsPerRole($user['role']));
        // create app
        $app_data = [
            'id' => $app_id,
            'user' => $username,
            'name' => $app_name,
            'secret' => Utils::generateRandomString(48),
            'endpoints' => $endpoints,
            'enabled' => boolval($app_enabled)
        ];
        return $apps_db->write($app_id, $app_data);
    }//createApplication
    
    
    /** TODO: Edits an app...
     */
    public static function updateApplication($app_id, $endpoints_up, $endpoints_dw, $app_enabled = null, $username = null) {
        self::assertInitialized("updateApplication");
        if (is_null($username)) {
            if (!Core::isUserLoggedIn()) {
                return ['success' => false, 'data' => 'Only logged users are allowed to update API Applications'];
            }
            // get user id
            $username = Core::getUserLogged('username');
        }
        // open applications DB for the current/given user
        $apps_db = new Database('core', 'api_applications', self::_build_app_db_regex($username));
        // make sure that the app exists
        if (!$apps_db->key_exists($app_id)) {
            return ['success' => false, 'data' => sprintf('The application with ID `%s` does not exist', $app_id)];
        }
        // retrieve the app to update
        $res = $apps_db->get_entry($app_id);
        if (!$res['success']) {
            return $res;
        }
        $app = $res['data'];
        // get the list of active API end-points associated to this app
        // NOTE: array_keys(array_flip()) is similar to array_unique() for array w/o keys
        $endpoints_orig = $app->get('endpoints');
        $endpoints = array_keys(array_flip(array_merge(array_diff($endpoints_orig, $endpoints_dw), $endpoints_up)));
        // get user record
        $res = Core::getUserInfo($username);
        if (!$res['success']) {
            return $res;
        }
        $user = $res['data'];
        // make sure that the user does not gain powers s/he is not supposed to have
        $endpoints = array_intersect($endpoints, self::endpointsPerRole($user['role']));
        // update app
        $app->set('endpoints', $endpoints);
        // maintain status if not passed
        if (!is_null($app_enabled)) {
            $app->set('enabled', boolval($app_enabled));
        }
        // write to disk and return
        return $app->commit();
    }//updateApplication
    
    
    /** Deletes an existing API application.
     *
     * @param string $app_id ID of the application to delete.
     * @param string|null $username Optional username of the user the app belongs to. Currently logged in user is used by default.
     * @return bool                     Whether the operation succeded.
     * @throws DatabaseKeyNotFoundException
     * @throws NotLoggedInException
     */
    public static function deleteApplication(string $app_id, string $username = null): bool {
        self::assertInitialized("deleteApplication");
        if (is_null($username)) {
            if (!Core::isUserLoggedIn()) {
                throw new NotLoggedInException('Only logged users are allowed to delete API Applications');
            }
            // get user id
            $username = Core::getUserLogged('username');
        }
        // open applications DB for the current/given user
        $apps_db = new Database('core', 'api_applications', self::_build_app_db_regex($username));
        // remove entry
        return $apps_db->delete($app_id);
    }//deleteApplication
    
    
    // =================================================================================================================
    // =================================================================================================================
    //
    //
    // Private functions
    
    /** REGEX matching (possibly specified) a pair of (username, app_id).
     *
     * @param string|null $username        Optional username.
     * @param string|null $app_id          Optional application id.
     * @return string               REGEX.
     */
    #[Pure] private static function _build_app_db_regex(string $username = null, string $app_id = null): string {
        return sprintf(
            "/^%s_%s$/",
            is_null($username) ? '[0-9]{21}' : $username,
            is_null($app_id) ? '[A-Za-z0-9_]+' : $app_id
        );
    }//_build_app_db_regex
    
    /** Returns all endpoints accessible by the given role.
     *
     * @param string $user_role     User role.
     * @param bool $app_auth_only   Only endpoints accessible via App?
     * @return array
     */
    private static function endpointsPerRole(string $user_role, bool $app_auth_only = true): array {
        $endpoints = [];
        foreach (self::$endpoints as $service_id => $service) {
            foreach ($service->getActions() as $action_id => $action) {
                $action_cfg = $action->configuration();
                if ($app_auth_only && !in_array('app', $action_cfg->authentication())) {
                    continue;
                }
                if (in_array($user_role, $action_cfg->access_level())) {
                    $pair = sprintf('%s/%s', $service_id, $action_id);
                    array_push($endpoints, $pair);
                }
            }
        }
        return $endpoints;
    }//endpointsPerRole
    
    /** Loads API settings from disk.
     *
     * @return RESTfulAPISettings
     * @throws FileNotFoundException
     * @throws IOException
     * @throws InvalidSchemaException
     * @throws SchemaViolationException
     */
    private static function loadAPISettings(): RESTfulAPISettings {
        // check if this object is cached
        $cache_key = "api_settings";
        if (self::$cache->has($cache_key)) {
            return self::$cache->get($cache_key);
        }
        // load global settings
        $settings_file = join_path($GLOBALS['__SYSTEM__DIR__'], "api/settings.json");
        $settings = RESTfulAPISettings::fromFile($settings_file);
        // cache object
        self::$cache->set($cache_key, $settings, CacheTime::HOURS_24);
        // ---
        return $settings;
    }//loadAPISettings
    
    /** Loads API endpoints from disk.
     *
     * @return array
     * @throws FileNotFoundException
     * @throws IOException
     * @throws InvalidSchemaException
     * @throws SchemaViolationException
     */
    private static function loadAPIEndpoints(): array {
        // check if this object is cached
        $cache_key = "api_configuration";
        if (self::$cache->has($cache_key)) {
            return self::$cache->get($cache_key);
        }
        
        // get list of packages
        $packages = Core::getPackagesList();
        $packages_ids = array_keys($packages);
        
        // create resulting object
        $api_endpoints = [];
        
        // iterate over the API versions -> packages -> services -> actions
        foreach (self::$settings->versions() as $api_version => $api_v_specs) {
            $api_endpoints[$api_version] = [];
            $api_v_enabled = $api_v_specs->enabled();
            
            // iterate over the packages
            foreach ($packages_ids as $pkg_id) {
                $pkg_root = $packages[$pkg_id]['root'];
                $package_enabled = $packages[$pkg_id]['enabled'];
                // get location of API endpoints
                $api_endpoints_dir = join_path($pkg_root, 'api', $api_version, 'endpoints');
                $api_service_pattern = join_path($api_endpoints_dir, '*', 'service.json');
                $api_service_matches = glob($api_service_pattern);
                
                // iterate over the API services
                foreach ($api_service_matches as $api_service_match) {
                    // get service name
                    $api_service_dir = dirname($api_service_match);
                    // load service
                    $api_service = new RESTfulAPIService($api_version, $pkg_id, $api_service_dir);
                    // check whether the service is enabled (key exists == service is disabled)
                    $api_service_enabled = $api_v_enabled && $package_enabled && $api_service->enabled();
                    $api_service->setEnabled($api_service_enabled);
                    
                    // iterate over the API actions
                    foreach ($api_service->getActions() as $api_action) {
                        // action is enabled?
                        $api_action_enabled = $api_v_enabled && $package_enabled && $api_service->enabled() && $api_action->enabled();
                        $api_action->setEnabled($api_action_enabled);
                    }//for:action
                    
                    $api_endpoints[$api_version][$api_service->name()] = $api_service;
                }//for:service
                
            }//for:package
            
        }//for:version
        
        // cache object
        self::$cache->set($cache_key, $api_endpoints, CacheTime::HOURS_24);
        // return api config object
        return $api_endpoints;
    }//loadAPIEndpoints
    
}//RESTfulAPI

?>

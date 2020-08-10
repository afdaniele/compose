<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

namespace system\classes;

require_once __DIR__ . '/../environment.php';
require_once __DIR__ . '/../utils/utils.php';

// booleanval function
require_once __DIR__ . '/libs/booleanval.php';
// structure
require_once __DIR__ . '/Configuration.php';
require_once __DIR__ . '/EditableConfiguration.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Utils.php';
require_once __DIR__ . '/Formatter.php';
require_once __DIR__ . '/Cache.php';
require_once __DIR__ . '/enum/StringType.php';
require_once __DIR__ . '/enum/EmailTemplates.php';
require_once __DIR__ . '/enum/CacheTime.php';
// php-mailer classes
require_once __DIR__ . '/PHPMailer/PHPMailerAutoload.php';

require_once __DIR__ . '/yaml/Spyc.php';

require_once __DIR__ . '/jsonDB/JsonDB.php';

require_once __DIR__ . '/Color.php';
require_once __DIR__ . '/Schema.php';

// load Google API client
require_once __DIR__ . '/google_api_php_client/vendor/autoload.php';


use system\classes\enum\EmailTemplates;
use system\classes\enum\CacheTime;


define('INFO', 0);
define('WARNING', 1);
define('ERROR', 2);


/** Core module of the platform <b>\\compose\\</b>.
 */
class Core {
    
    private static $initialized = FALSE;
    private static $cache = NULL;
    private static $packages = NULL;
    private static $pages = NULL;
    private static $verbose = FALSE;
    private static $debug = FALSE;
    private static $settings = NULL;
    private static $debugger_data = [];
    private static $volatile_session = FALSE;
    private static $registered_css_stylesheets = [];
    private static $default_page_per_role = [
        'administrator' => 'profile',
        'supervisor' => 'profile', 'user' => 'profile',
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
        'api', 'data', 'error', 'login',
        'maintenance', 'package_store', 'profile', 'settings',
        'setup', 'users'
    ];
    
    private static $USER_ACCOUNT_TEMPLATE = [
        "username" => ["string", "Google (numeric) user ID"],
        "name" => ["string", "Full name of the user"],
        "email" => ["string", "Email address"],
        "picture" => [
            "string",
            "Link to google account picture (provided by Google Sign-In)"
        ],
        "role" => ["string", "Access level of the user"],
        "active" => [
            "boolean",
            "Whether the user is allowed to login and use the platform"
        ]
    ];

    public static $PAGE_METADATA_TEMPLATE = [
        "name"         => ["string", "Name of the page"],
        "package"      => [
            "string",
            "ID of the package the page belongs to"
        ],
        "menu_entry"   => [
            "__type"        => "associative_array",
            "__nullable"    => TRUE,
            "__details"     => "Associative array containing info about the menu entry for the page",
            "order"         => [
                "float",
                "The order of the page on the top menu bar (smallest number = leftmost entry)"
            ],
            "icon"          => [
                "__type"    => "associative_array",
                "__details" => "Associative array containing info about the menu entry for the page",
                "class"     => [
                    "string",
                    "Class of the icon (e.g., glyphicon)"
                ],
                "name"      => [
                    "string",
                    "ID of the icon to use (e.g., car)"
                ]
            ],
            "responsive"    => [
                "__type"    => "associative_array",
                "__details" => "Associative array containing info about how the responsiveness of the menu entry for small devices",
                "priority"  => [
                    "float",
                    "Priority with which the menu entry will be contracted (highest number = contracted first)"
                ]
            ],
            "exclude_roles" => [
                "__type"    => "array",
                "__details" => "List of user roles for which this page icon should be hidden"
            ]
        ],
        "child_pages"  => [
            "__type"        => "array",
            "__details"     => "The menu entry of the page will be highlighted if the current page matches the `name` of this class or any ID in this list",
            "__sample_item" => [
                "string",
                "ID of a child page"
            ]
        ],
        "access_level" => [
            "__type"        => "array",
            "__details"     => "List of user roles for which this page is accessible",
            "__sample_item" => [
                "string",
                "User role to grand access to"
            ]
        ]
    ];

    public static $PACKAGE_METADATA_TEMPLATE = [
        "name"         => [
            "string",
            "Name of the package"
        ],
        "description"  => [
            "string",
            "Textual description of the package"
        ],
        "core"         => [
            "__type"     => "associative_array",
            "__nullable" => TRUE,
            "__details"  => "Associative array containing info about an (optional) core module to include",
            "namespace"  => [
                "string",
                "Namespace under which the core module is declared. The string '/system/classes/' will be prepended"
            ],
            "file"       => [
                "string",
                "Path to the PHP file (extension included) containing the core class to load. The path must be relative to the PACKAGE_ROOT directory"
            ],
            "class"      => [
                "string",
                "Name of the class as defined in the core file specified above"
            ]
        ],
        "dependencies" => [
            "__type"          => "associative_array",
            "__nullable"      => TRUE,
            "__details"       => "Associative array containing info about (optional) package dependencies",
            "system-packages" => [
                "__type"        => "array",
                "__details"     => "List of system packages required by the package",
                "__sample_item" => [
                    "string",
                    "Name of the system package to install"
                ]
            ],
            "packages"        => [
                "__type"        => "array",
                "__details"     => "List of \compose\ packages required by the package",
                "__sample_item" => [
                    "string",
                    "Name of the package to install"
                ]
            ]
        ]
    ];
    
    public static $PACKAGE_SETTINGS_METADATA_TEMPLATE = [
        "configuration_content" => [
            "__type" => "associative_array",
            "__details" => "Associative array containing (parameter_key, parameter_details) pairs for the package",
            "__sample_item" => [
                "__type" => "associative_array",
                "__details" => "Associative array containing (parameter_detail, parameter_value) pairs for the setting parameter",
                "__sample_key" => [
                    "string",
                    "Parameter key"
                ],
                "__sample_value" => [
                    "title" => [
                        "string",
                        "Name of the setting parameter"
                    ],
                    "type" => [
                        "string",
                        "Type of the parameter (e.g., string, boolean, integer)"
                    ],
                    "default" => [
                        "_same_as_type",
                        "Default value of this parameter"
                    ],
                    "details" => [
                        "string",
                        "A longer description of the parameter"
                    ]
                ]
            ]
        ]
    ];
    
    private static $DEVELOPER_USER_INFO = [
        "username" => '_compose_developer',
        "name" => 'Developer', "email" => NULL,
        "picture" => 'images/developer.jpg',
        "role" => "administrator",
        "active" => TRUE,
        "pkg_role" => []
    ];
    
    
    //Disable the constructor
    private function __construct() {
    }
    
    
    
    
    // =======================================================================================================
    // Initilization and session management functions
    
    
    /** Initializes the Core module.
     *    It is the first function to call when using the Core module.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function init($safe_mode = FALSE) {
        if (!self::$initialized) {
            // set encoding
            mb_internal_encoding("UTF-8");
            // configure umask
            self::_set_umask(0002);
            // init configuration
            $res = Configuration::init();
            if (!$res['success']) {
                return $res;
            }
            //
            // create cache proxy (cache initialization happens later)
            self::$cache = new CacheProxy('core');
            //
            // load settings for the core module only (needed to initialize the cache)
            self::$packages = ['core' => NULL];
            self::$settings = self::_load_packages_settings(TRUE);
            //
            // set timezone
            date_default_timezone_set(self::getSetting('timezone', 'core', 'America/Chicago'));
            //
            // load default page per role
            foreach (self::$registered_user_roles['core'] as $user_role => &$user_role_config) {
                $key = sprintf('%s_default_page', $user_role);
                $user_role_config['default_page'] = self::getSetting($key, 'core', $user_role_config['factory_default_page']);
            }
            //
            // initialize cache
            if (self::getSetting('cache_enabled', 'core')) {
                Cache::init();
            }
            //
            // load list of available packages
            self::$packages = self::_discover_packages($safe_mode);
            // load list of available pages
            self::$pages = self::_discover_pages($safe_mode);
            // load package-specific settings
            self::$settings = self::_load_packages_settings($safe_mode);
            //
            // get current theme
            $theme_parts = explode(':', self::getSetting('theme', 'core', 'core:default'));
            $theme_pkg = $theme_parts[0];
            $theme_name = $theme_parts[1];
            // load theme configuration
            try {
                $res = self::getThemeConfiguration($theme_name, $theme_pkg);
                if (!$res['success']) throw new \Exception($res['data']);
                Configuration::$THEME_CONFIG = $res['data'];
            } catch (\Exception $e) {
                Core::requestAlert(
                    'WARNING',
                    sprintf("An error occurred while loading the theme [%s]%s. ",
                        $theme_pkg, $theme_name) . "We reverted to the default theme." .
                    sprintf("The error reads:<br/>%s", $e->getMessage())
                );
                self::setSetting('core', 'theme', 'core:default');
                self::redirectTo('');
            }
            //
            // safe mode (everything after this point should be optional)
            if ($safe_mode) {
                self::$initialized = TRUE;
                return ['success' => TRUE, 'data' => NULL];
            }
            //
            // load email templates
            EmailTemplates::init();
            //
            // create dependencies graph for the packages
            $dep_graph = [];
            foreach (self::$packages as $pkg_id => $pkg) {
                if ($pkg_id == 'core')
                    continue;
                // collect dependencies
                $dep_graph[$pkg_id] = $pkg['dependencies']['packages'];
            }
            // solve the dependencies graph
            $res = self::_solve_dependencies_graph($dep_graph);
            if (!$res['success'])
                return $res;
            $package_order = $res['data'];
            //
            // initialize all the packages
            foreach ($package_order as $pkg_id) {
                $pkg = self::$packages[$pkg_id];
                if (!$pkg['enabled'])
                    continue;
                // initialize package Core class
                if (!is_null($pkg['core'])) {
                    // try to load the core file
                    $file_loaded = include_once $pkg['core']['file'];
                    if ($file_loaded) {
                        // TODO: do not prepend \system\classes if it is already in $pkg['core']['namespace']
                        $php_init_command = sprintf("return \system\packages\%s\%s::init();", $pkg['core']['namespace'], $pkg['core']['class']);
                        // try to initialize the package core class
                        try {
                            $res = eval($php_init_command);
                            if (!is_array($res))
                                return [
                                    'success' => FALSE,
                                    'data' => sprintf('An error occurred while initializing the package `%s`. Command `%s`', $pkg['id'], $php_init_command)
                                ];
                            if (!$res['success'])
                                return [
                                    'success' => FALSE,
                                    'data' => sprintf('An error occurred while initializing the package `%s`. Command `%s`. The module reports: "%s"', $pkg['id'], $php_init_command, $res['data'])
                                ];
                        } catch (\Error $e) {
                            return ['success' => FALSE, 'data' => $e->getMessage()];
                        }
                    }
                }
            }
            self::$initialized = TRUE;
            return ['success' => TRUE, 'data' => NULL];
        } else {
            return ['success' => TRUE, 'data' => "Core already initialized!"];
        }
    }//init
    
    
    public static function isComposeConfigured() {
        // TODO: Cache this data
        
        // open first_setup DB
        $first_setup_db = new Database('core', 'first_setup');
        // return whether the configuration flag exists
        return $first_setup_db->key_exists('configured');
    }//isComposeConfigured
    
    
    public static function healthCheck() {
        return ['success' => TRUE, 'data' => NULL];
    }//healthCheck
    
    
    public static function setVolatileSession($val) {
        self::$volatile_session = $val;
    }//setVolatileSession
    
    
    public static function isVolatileSession() {
        return self::$volatile_session;
    }//isVolatileSession
    
    
    public static function getCurrentResource() {
        $resource_parts = [
            Configuration::$PAGE, Configuration::$ACTION, Configuration::$ARG1,
            Configuration::$ARG2
        ];
        $resource_parts = array_filter($resource_parts, function ($e) {
            return !is_null($e) && strlen($e) > 0;
        });
        return implode('/', $resource_parts);
    }//getCurrentResource
    
    
    public static function getCurrentResourceURL($qs_array = [], $include_qs = FALSE) {
        $qs_dict = array_merge(($include_qs) ? $_GET : [], $qs_array);
        $qs = toQueryString(array_keys($qs_dict), $qs_dict, TRUE);
        $resource = self::getCurrentResource();
        return sprintf('%s%s%s', Configuration::$BASE, $resource, $qs);
    }//getCurrentResourceURL
    
    
    public static function getURL($page = NULL, $action = NULL, $arg1 = NULL, $arg2 = NULL, $qs = [], $anchor = NULL) {
        return sprintf(
            '%s%s%s%s%s%s%s',
            Configuration::$BASE,
            is_null($page) ? '' : $page,
            is_null($action) ? '' : '/' . $action,
            is_null($arg1) ? '' : '/' . $arg1,
            is_null($arg2) ? '' : '/' . $arg2,
            (is_string($qs)) ?
                ('?' . $qs) :
                ((count($qs) > 0) ? toQueryString(array_keys($qs), $qs, TRUE) : ''),
            (is_null($anchor) || strlen($anchor) <= 0) ? '' : sprintf('#%s', $anchor)
        );
    }//getURL
    
    
    public static function getAPIurl($service, $action, $qs = [], $format = 'json', $token = NULL) {
        if (is_null($token)) {
            $token = $_SESSION['TOKEN'];
        }
        return sprintf('%sweb-api/%s/%s/%s/%s?%s%s&', Configuration::$BASE, Configuration::$WEBAPI_VERSION, $service, $action, $format, sprintf('token=%s&', $token), (count($qs) > 0) ? toQueryString(array_keys($qs), $qs, FALSE, TRUE) : '');
    }//getAPIurl
    
    
    public static function getPackagesModules($module_family = NULL, $pkg_id = NULL, $include_disabled = FALSE) {
        $modules = [];
        foreach (self::$packages as $pkg) {
            if ((!$include_disabled && !$pkg['enabled']) || (!is_null($pkg_id) && $pkg_id != $pkg['id'])) {
                continue;
            }
            $modules[$pkg['id']] = [];
            // collect package modules
            foreach ($pkg['modules'] as $module_fam => $module_scripts) {
                if (!is_null($module_family) && $module_family != $module_fam) {
                    continue;
                }
                $modules[$pkg['id']][$module_fam] = $module_scripts;
            }
        }
        // remove pkg_id level if pkg_id is given
        $out = $modules;
        if (!is_null($pkg_id)) {
            $out = $modules[$pkg_id];
            if (!is_null($module_family)) {
                $out = $out[$module_family];
            }
        } else if (!is_null($module_family)) {
            $out = [];
            foreach ($modules as $pkg => &$mods) {
                if (count($mods[$module_family]) > 0) {
                    $out[$pkg] = $mods[$module_family];
                }
            }
        }
        return $out;
    }//getPackagesModules
    
    
    public static function loadPackagesModules($module_family = NULL, $pkg_id = NULL) {
        foreach (self::$packages as $pkg) {
            if (!$pkg['enabled'] || (!is_null($pkg_id) && $pkg_id != $pkg['id'])) {
                continue;
            }
            // load package modules
            foreach ($pkg['modules'] as $module_fam => $module_scripts) {
                if (!is_null($module_family) && $module_family != $module_fam) {
                    continue;
                }
                foreach ($module_scripts as $module_script) {
                    // check file
                    if (!file_exists($module_script)) {
                        self::collectDebugInfo($pkg['id'], sprintf('Load module script %s of type %s', $module_script, $module_family), FALSE, Formatter::BOOLEAN);
                        continue;
                    }
                    // load module
                    require_once $module_script;
                    self::collectDebugInfo($pkg['id'], sprintf('Load module %s of type %s', $module_script, $module_family), TRUE, Formatter::BOOLEAN);
                }
            }
        }
    }//loadPackagesModules
    
    
    public static function getClasses($parent_class = NULL) {
        $classes = [];
        foreach (get_declared_classes() as $class) {
            if (!is_null($parent_class) && !is_subclass_of($class, $parent_class))
                continue;
            array_push($classes, $class);
        }
        return $classes;
    }//getClasses
    
    
    /** Terminates the Core module.
     *    It is responsible for committing unsaved changes to the disk or closing open connections
     *    (e.g., mySQL) before leaving.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function close() {
        return ['success' => TRUE, 'data' => NULL];
    }//close
    
    
    /** Creates a new PHP Session and assigns a new randomly generated 16-digits authorization token to it.
     *
     * @retval boolean
     *        `TRUE` if the call succeded, `FALSE` otherwise
     */
    public static function startSession() {
        if (!self::isVolatileSession())
            session_start();
        if (!isset($_SESSION['TOKEN'])) {
            // generate a session token
            $token = self::generateRandomString(16);
            $_SESSION['TOKEN'] = $token;
        }
        // init configuration
        $res = Configuration::init();
        if (!$res['success']) {
            return $res;
        }
        Configuration::$TOKEN = $_SESSION['TOKEN'];
        //
        return ['success' => TRUE, 'data' => NULL];
    }//startSession
    
    
    /** Writes and closes the current PHP Session.
     *
     * @retval boolean
     *        `TRUE` if the call succeded, `FALSE` otherwise
     */
    public static function closeSession() {
        if (!self::isVolatileSession())
            return session_write_close();
        return TRUE;
    }//closeSession
    
    
    // =======================================================================================================
    // Users management functions
    
    /** Logs in a user using the Google Sign-In OAuth 2.0 authentication procedure.
     *
     * @param string $id_token
     *        id_token returned by the Google Identity Sign-In tool,
     *        (for more info check:
     *        https://developers.google.com/identity/sign-in/web/reference#gapiauth2authresponse);
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function logInUserWithGoogle($id_token) {
        if ($_SESSION['USER_LOGGED']) {
            return ['success' => FALSE, 'data' => 'You are already logged in!'];
        }
        // verify id_token
        $client = new \Google_Client(['client_id' => self::getSetting('google_client_id', 'core')]);
        $payload = $client->verifyIdToken($id_token);
        if ($payload) {
            $userid = $payload['sub'];
            // create user descriptor
            $user_info = [
                "username" => $userid, "name" => $payload['name'],
                "email" => $payload['email'], "picture" => $payload['picture'],
                "role" => "user", "active" => TRUE, "pkg_role" => []
            ];
            // look for a pre-existing user profile
            $user_exists = self::userExists($userid);
            if ($user_exists) {
                // there exists a user profile, load info
                $res = self::openUserInfo($userid);
                if (!$res['success']) {
                    return $res;
                }
                $user_info = $res['data']->asArray();
            } else {
                $res = self::createNewUserAccount($userid, $user_info);
                if (!$res['success']) {
                    return $res;
                }
            }
            // make sure that the user is active
            if (!boolval($user_info['active'])) {
                return [
                    'success' => FALSE,
                    'data' => 'The user profile you are trying to login with is not active. Please, contact the administrator'
                ];
            }
            // set login system
            self::setLoginSystem('__GOOGLE_SIGNIN__');
            //
            $_SESSION['USER_LOGGED'] = TRUE;
            $_SESSION['USER_RECORD'] = $user_info;
            //
            self::regenerateSessionID();
            return ['success' => TRUE, 'data' => $user_info];
        } else {
            // Invalid ID token
            return ['success' => FALSE, 'data' => "Invalid ID Token"];
        }
    }//logInUserWithGoogle
    
    
    /** Logs in as developer.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function logInAsDeveloper() {
        if ($_SESSION['USER_LOGGED']) {
            return ['success' => FALSE, 'data' => 'You are already logged in!'];
        }
        if (!self::getSetting('developer_mode')) {
            return [
                'success' => FALSE,
                'data' => 'You can login as Developer only when the Developer Mode is active'
            ];
        }
        // create user descriptor
        $user_info = self::$DEVELOPER_USER_INFO;
        // set login system
        self::setLoginSystem('__DEVELOPER__');
        // set login variables
        $_SESSION['USER_LOGGED'] = TRUE;
        $_SESSION['USER_RECORD'] = $user_info;
        // ---
        Core::regenerateSessionID();
        return ['success' => TRUE, 'data' => $user_info];
    }//logInAsDeveloper
    
    
    /** Authorizes a user using an API Application.
     *
     * @param string $app_id
     *        ID of the API Application to authenticate with.
     *
     * @param string $app_secret
     *        Secret key associated with the API Application identified by `$app_id`.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field will contain the info about the API Application used to
     *        authenticate the user or an error string when `success` is `FALSE`.
     */
    public static function authorizeUserWithAPIapp($app_id, $app_secret) {
        RESTfulAPI::init();
        // check if the app exists
        $res = RESTfulAPI::getApplication($app_id);
        if (!$res['success']) {
            return $res;
        }
        // get the app
        $app = $res['data'];
        // check if the app_secret matches
        if (!boolval($app_secret == $app['secret'])) {
            return [
                'success' => FALSE,
                'data' => 'The application secret key provided is not correct'
            ];
        }
        // check if the app is enabled
        if (!boolval($app['enabled'])) {
            return [
                'success' => FALSE,
                'data' => sprintf('The application `%s` is not enabled, thus it cannot be used', $app['id'])
            ];
        }
        // get owner of the app
        $username = $app['user'];
        if (!self::userExists($username)) {
            return [
                'success' => FALSE,
                'data' => sprintf('The application `%s` is not enabled, thus it cannot be used', $app['id'])
            ];
        }
        // load user info
        $res = self::openUserInfo($username);
        if (!$res['success']) {
            return $res;
        }
        $user_info = $res['data']->asArray();
        $user_info['pkg_role'] = [];
        // this data will be deleted if the PHP session was not initialized before this call
        $_SESSION['USER_LOGGED'] = TRUE;
        $_SESSION['USER_RECORD'] = $user_info;
        // return app
        return ['success' => TRUE, 'data' => ['user' => $user_info, 'app' => $app]];
    }//authorizeUserWithAPIapp
    
    
    /** Creates a new user account.
     *
     * @param string $user_id
     *        string containing the (numeric) user id provided by Google Sign-In;
     *
     * @param array $user_info
     *        array containing information about the new user. This array
     *        has to contain at least all the keys defined in $USER_ACCOUNT_TEMPLATE;
     *
     * @return array
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function createNewUserAccount($user_id, &$user_info) {
        $user_exists = self::userExists($user_id);
        if ($user_exists) {
            return [
                'success' => FALSE,
                'data' => sprintf('The user `%s` already exists', $user_id)
            ];
        }
        // validate user info
        $mandatory_fields = array_keys(self::$USER_ACCOUNT_TEMPLATE);
        foreach ($mandatory_fields as $field) {
            if (!isset($user_info[$field])) {
                return [
                    'success' => FALSE,
                    'data' => sprintf('The field "%s" is required for a new user account', $field)
                ];
            }
        }
        // open users DB
        $users_db = new Database('core', 'users');
        // create administrator if this is the first user
        if ($users_db->size() < 1) {
            $user_info['role'] = 'administrator';
            // disable the developer mode (if it is enabled)
            if (self::getSetting('developer_mode')) {
                $res = self::setSetting('core', 'developer_mode', FALSE);
                if (!$res['success']) {
                    return $res;
                }
                // warn the user of what happened
                self::requestAlert('INFO', 'Administrator account created! The Developer Mode was disabled automatically.');
            }
        }
        // add metadata
        $user_info['creation-time'] = time();
        // create a new user account on the server
        $res = $users_db->write($user_id, $user_info);
        return $res;
    }//createNewUserAccount
    
    
    /** Returns whether a user is currently logged in.
     *
     * @retval boolean
     *        whether a user is currently logged in;
     */
    public static function isUserLoggedIn() {
        return isset($_SESSION['USER_LOGGED']) ? $_SESSION['USER_LOGGED'] : FALSE;
    }//isUserLoggedIn
    
    
    /** Returns the list of users registered on the platform.
     *    A user is automatically registered when s/he logs in with google.
     *
     * @retval array
     *        list of user ids. The user id of a user is the numeric user id assigned by Google;
     */
    public static function getUsersList() {
        // open users DB
        $users_db = new Database('core', 'users');
        // get list of users
        return $users_db->list_keys();
    }//getUsersList
    
    
    /** Logs out the user from the platform.
     *    If the user is not logged in yet, the call will return an error status.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function logOutUser() {
        if (!$_SESSION['USER_LOGGED']) {
            return ['success' => FALSE, 'data' => 'User not logged in yet!'];
        }
        // destroy session
        session_destroy();
        //
        return ['success' => TRUE, 'data' => NULL];
    }//logOutUser
    
    
    /** Checks whether a user account exists.
     *
     * @param string $user_id
     *        string containing the (numeric) user id provided by Google Sign-In;
     *
     * @retval boolean
     *        whether a user account with the specified user id exists;
     */
    public static function userExists($user_id) {
        // open users DB
        $users_db = new Database('core', 'users');
        // return whether the user exists
        return $users_db->key_exists($user_id);
    }//userExists
    
    
    /** Opens the user account record for the user specified in write-mode.
     *    This function returns an instance of the class \\system\\classes\\jsonDB\\JsonDB
     *    containing the information about the user specified.
     *
     * @param string $user_id
     *        string containing the (numeric) user id provided by Google Sign-In;
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or instance of
     *        \\system\\classes\\jsonDB\\JsonDB
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        otherwise it will contain an instance of the class \\system\\classes\\jsonDB\\JsonDB
     *        containing the information about the user specified.
     *        The JsonDB object will contain at least the keys specified in
     *        $USER_ACCOUNT_TEMPLATE.
     *        See the documentation for the class JsonDB to understand how to edit and commit
     *        information.
     */
    public static function openUserInfo($user_id) {
        // open users DB
        $users_db = new Database('core', 'users');
        // make sure that the user exists
        if (!$users_db->key_exists($user_id))
            return ['success' => FALSE, 'data' => 'User "' . $user_id . '" not found!'];
        // load user info
        $res = $users_db->get_entry($user_id);
        if (!$res['success'])
            return $res;
        $user_info = $res['data'];
        // sanity check on user entry
        $static_user_info = $user_info->asArray();
        $mandatory_fields = array_keys(self::$USER_ACCOUNT_TEMPLATE);
        foreach ($mandatory_fields as $field) {
            if (!isset($static_user_info[$field])) {
                return [
                    'success' => FALSE,
                    'data' => 'The descriptor file for the user "' . $user_id . '" is corrupted! Contact the administrator'
                ];
            }
        }
        if (strcmp($static_user_info['username'], $user_id) != 0) {
            return [
                'success' => FALSE,
                'data' => 'The descriptor file for the user "' . $user_id . '" is corrupted! Contact the administrator'
            ];
        }
        //
        return ['success' => TRUE, 'data' => $user_info];
    }//openUserInfo
    
    
    /** Returns the user account record for the user specified.
     *    Unlike openUserInfo(), this function returns a read-only copy of the user account.
     *
     * @param string $user_id
     *        string containing the (numeric) user id provided by Google Sign-In;
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or associative array
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        otherwise it will contain an associative array containing the information about the
     *        user specified. The associative array in `data` will contain at least the keys
     *        specified in $USER_ACCOUNT_TEMPLATE.
     */
    public static function getUserInfo($user_id) {
        $res = self::openUserInfo($user_id);
        if (!$res['success']) {
            return $res;
        }
        //
        return ['success' => TRUE, 'data' => $res['data']->asArray()];
    }//getUserInfo
    
    
    /** Returns the user account record of the user currently logged in.
     *
     * @param string $field
     *        (optional) name of the field to retrieve from the user account. It can be any of the
     *        keys specified in $USER_ACCOUNT_TEMPLATE;
     *
     * @retval mixed
     *        If no user is currently logged in, returns `NULL`;
     *        If `$field`=`NULL`, returns associative array containing the information about the
     *        user currently logged in (similar to getUserInfo()); If a value for `$field` is
     *        passed, only the value of the field specified is returned (e.g., name).
     */
    public static function getUserLogged($field = NULL) {
        if (!isset($_SESSION['USER_RECORD']) || is_null($_SESSION['USER_RECORD'])) {
            return NULL;
        }
        $user_record = $_SESSION['USER_RECORD'];
        return ($field == NULL) ? $user_record : $user_record[$field];
    }//getUserLogged
    
    
    /** Returns the role of the user that is currently using the platform.
     *
     * @param string $package
     *        (optional) package with respect to which we want to obtain the current role; Default
     *        is 'core';
     *
     * @retval string
     *        role of the user that is currently using the platform. It can be any of the default
     *        roles defined by <b>\\compose\\</b> or any other role registered by third-party
     *        packages. A list of all the user roles registered can be retrieved using the
     *        function getAllRegisteredUserRoles();
     */
    public static function getUserRole($package = 'core') {
        // not logged => guest
        if (!self::isUserLoggedIn())
            return 'guest';
        // core package
        if ($package == 'core')
            return self::getUserLogged('role');
        // third-party packages
        $pkg_role = self::getUserLogged('pkg_role');
        if (in_array($package, array_keys($pkg_role))) {
            return $pkg_role[$package];
        }
        // no role for this package
        return NULL;
    }//getUserRole
    
    
    /** Sets the package-specific role of the user that is currently using the platform.
     *    NOTE: this function does not update the user account of the current user permanently.
     *    This change will be lost once the session is closed.
     *
     * @param string $user_role
     *        role to assign to the current user;
     *
     * @param string $package
     *        (optinal) package with respect to which we assign the new role; Default is `core`.
     *
     * @retval void
     */
    public static function setUserRole($user_role, $package = 'core') {
        if (!isset($_SESSION['USER_RECORD'])) {
            return;
        }
        //TODO: make sure that the give <pkg,role> pair was previously registered
        if ($package == 'core') {
            if (in_array($user_role, ['guest', 'user', 'supervisor', 'administrator']))
                $_SESSION['USER_RECORD']['role'] = $user_role;
        } else {
            $_SESSION['USER_RECORD']['pkg_role'][$package] = $user_role;
        }
    }//setUserRole
    
    
    /** Returns the list of all the roles of the current user on the platform. It includes the user role
     *    defined by <b>\\compose\\</b> plus all the user roles introduced by third-party packages
     *    and associated to the current user. The main user role (defined by <b>\\compose\\</b>)
     *    is returned in the format
     *    "role". Package-specific roles are returned in the form "package_id:role".
     *
     * @retval array
     *        list of unique strings. Each string represents a different role;
     */
    public static function getUserRolesList() {
        $roles = [self::getUserRole('core')];
        $pkg_roles = self::getUserLogged('pkg_role');
        foreach ($pkg_roles as $pkg_id => $pkg_role) {
            $role = sprintf('%s:%s', $pkg_id, $pkg_role);
            array_push($roles, $role);
        }
        return $roles;
    }//getUserRolesList
    
    
    /**
     *  Creates a new user group.
     *
     * @param $name string          name of the group to create
     * @param $description string   description of the group to create
     * @return array                a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed          // error message or null
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        otherwise it will contain null.
     */
    public static function createUserGroup($name, $description) {
        $db = new Database('core', 'groups');
        $group_key = Utils::string_to_valid_filename($name);
        // check if the group already exists
        if ($db->key_exists($group_key)) {
            return ['success' => false, 'data' => sprintf("A group with key '%s' already exists.", $group_key)];
        }
        // create group
        return $db->write($group_key, [
            'name' => $name,
            'description' => $description,
            'created-by' => self::getUserLogged('username'),
            'creation-time' => time()
        ]);
    }//createUserGroup
    
    
    /**
     *  Checks if a user group exists.
     *
     * @param $group string         key of the group to check
     * @return boolean              whether the group exists
     */
    public static function groupExists($group) {
        $db = new Database('core', 'groups');
        return $db->key_exists($group);
    }//groupExists
    
    
    /**
     *  Returns the list of user groups.
     *
     * @return array      List of group keys, one for each existing group
     */
    public static function getGroupsList() {
        $db = new Database('core', 'groups');
        return $db->list_keys();
    }//getGroupsList
    
    
    /**
     *  Returns information about a group.
     *
     * @param $group string     Key of the group to list members for
     * @return array            A status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed          // error message or null
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        otherwise it will contain a list of usernames, members of the group.
     */
    public static function getGroupInfo($group) {
        // check if the group exists
        if (!self::groupExists($group)) {
            return ['success' => false, 'data' => sprintf("The group with key '%s' does not exists.", $group)];
        }
        // open groups database
        $db = new Database('core', 'groups');
        // read group info
        $res = $db->read($group);
        if (!$res['success']) {
            return $res;
        }
        return ['success' => true, 'data' => $res['data']];
    }//getGroupInfo
    
    
    /**
     *  Returns the list of members of a group.
     *
     * @param $group string     Key of the group to list members for
     * @return array            A status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed          // error message or null
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        otherwise it will contain a list of usernames, members of the group.
     */
    public static function getGroupMembers($group) {
        // check if the group exists
        if (!self::groupExists($group)) {
            return ['success' => false, 'data' => sprintf("The group with key '%s' does not exists.", $group)];
        }
        // open user groupings database with limited scope
        $scope = sprintf("/^%s__(.*)$/", $group);
        $db = new Database('core', 'user_grouping', $scope);
        // read keys
        $keys = $db->list_keys();
        return [
            'success' => true,
            'data' => array_map(function ($k) use ($scope) {
                return Utils::regex_extract_group($k, $scope, 1);
            }, $keys)];
    }//getGroupMembers
    
    
    /**
     *  Returns the list of groups a user belongs to.
     *
     * @param $username string     Username of the user to list the groups for
     * @return array               List of group keys the user belongs to
     */
    public static function getUserGroups($username) {
        // check if the user exists
        if (!self::userExists($username)) {
            return ['success' => false, 'data' => sprintf("The user with key '%s' does not exists.", $username)];
        }
        // open user groupings database with limited scope
        $scope = sprintf("/^(.+)__%s$/", $username);
        $db = new Database('core', 'user_grouping', $scope);
        // read keys
        $keys = $db->list_keys();
        return [
            'success' => true,
            'data' => array_map(function ($k) use ($scope) {
                return Utils::regex_extract_group($k, $scope, 1);
            }, $keys)
        ];
    }//getUserGroups
    
    
    /**
     *  Deletes a user group.
     *
     * @param $group string           key of the group to delete
     * @return array                a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or null
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        otherwise it will contain null.
     */
    public static function deleteUserGroup($group) {
        // check if the group exists
        if (!self::groupExists($group)) {
            return ['success' => false, 'data' => sprintf("The group with key '%s' does not exists.", $group)];
        }
        // delete all user groupings associated to this group
        $scope = sprintf("/^%s__(.*)$/", $group);
        $db = new Database('core', 'user_grouping', $scope);
        foreach ($db->list_keys() as $key) {
            $res = $db->delete($key);
            if (!$res['success']) {
                return $res;
            }
        }
        // open groups database
        $db = new Database('core', 'groups');
        // delete group
        return $db->delete($group);
    }//deleteUserGroup
    
    
    /**
     *  Adds an existing user to an existing group.
     *
     * @param $username string      username of the user to add to the group
     * @param $group string         key of the group to add the user to
     * @return array                a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or null
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        otherwise it will contain null.
     */
    public static function addUserToGroup($username, $group) {
        // check if the user exists
        if (!self::userExists($username)) {
            return ['success' => false, 'data' => sprintf("The user with key '%s' does not exists.", $username)];
        }
        // check if the group exists
        if (!self::groupExists($group)) {
            return ['success' => false, 'data' => sprintf("The group with key '%s' does not exists.", $group)];
        }
        // open user grouping database
        $db = new Database('core', 'user_grouping');
        $key = sprintf('%s__%s', $group, $username);
        // add grouping entry
        return $db->write($key, []);
    }//addUserToGroup
    
    
    /**
     *  Removes an existing user from an existing group.
     *
     * @param $username string      username of the user to remove from the group
     * @param $group string         key of the group to remove the user from
     * @return array                a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or null
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        otherwise it will contain null.
     */
    public static function removeUserFromGroup($username, $group) {
        // check if the user exists
        if (!self::userExists($username)) {
            return ['success' => false, 'data' => sprintf("The user with key '%s' does not exists.", $username)];
        }
        // check if the group exists
        if (!self::groupExists($group)) {
            return ['success' => false, 'data' => sprintf("The group with key '%s' does not exists.", $group)];
        }
        // open user grouping database
        $db = new Database('core', 'user_grouping');
        $key = sprintf('%s__%s', $group, $username);
        // remove grouping entry
        return $db->delete($key);
    }//removeUserFromGroup
    
    
    /** Returns the list of user roles registered by a given package.
     *
     * @param string $package
     *        package to query for user roles;
     *        DEFAULT=core;
     *
     * @retval array
     *        list of unique strings. Each string represents a different user role;
     * @return array
     *          list of unique strings. Each string represents a different user role;
     */
    public static function getPackageRegisteredUserRoles($package = 'core') {
        if (array_key_exists($package, self::$registered_user_roles)) {
            return array_keys(self::$registered_user_roles[$package]);
        }
        return [];
    }//getPackageRegisteredUserRoles
    
    
    /** Returns the list of all user roles known to the platform. It includes all the user roles defined
     *    by <b>\\compose\\</b> plus all the user roles introduced by third-party packages.
     *
     * @retval array
     *        list of unique strings. Each string represents a different user role;
     */
    public static function getAllRegisteredUserRoles() {
        $roles = [];
        foreach (array_keys(self::getPackagesList()) as $pkg_id) {
            $prefix = boolval($pkg_id == 'core') ? '' : sprintf('%s:', $pkg_id);
            $pkg_roles = self::getPackageRegisteredUserRoles($pkg_id);
            array_merge($roles, array_map(function ($v) use ($prefix) {
                return sprintf('%s%s', $prefix, $v);
            }, $pkg_roles));
        }
        return array_unique($roles);
    }//getAllRegisteredUserRoles
    
    
    /** Adds a new user role to the list of roles known to the platform.
     *
     * @param string $package
     *        package registering the new user role;
     *
     * @param string $user_role
     *        ID of the user_role to register;
     *
     * @param string $default_page
     *        ID of the page to set as default for this user role;
     *
     * @retval void
     */
    public static function registerNewUserRole($package, $user_role, $default_page = 'NO_DEFAULT_PAGE') {
        if (!array_key_exists($package, self::$registered_user_roles))
            self::$registered_user_roles[$package] = [];
        // add the user role if not present
        if (!array_key_exists($user_role, self::$registered_user_roles[$package])) {
            self::$registered_user_roles[$package][$user_role] = [
                'default_page' => 'NO_DEFAULT_PAGE',
                'factory_default_page' => 'NO_DEFAULT_PAGE'
            ];
        }
        // update default page
        if ($default_page != 'NO_DEFAULT_PAGE') {
            self::$registered_user_roles[$package][$user_role]['default_page'] = $default_page;
        }
    }//registerNewUserRole
    
    
    /** Sets the login system used to login the current user.
     *
     * @param string $login_system
     *        ID of the login system used to authenticate the current user;
     *
     * @retval void
     */
    public static function setLoginSystem($login_system) {
        $_SESSION['LOGIN_SYSTEM'] = $login_system;
    }//setLoginSystem
    
    
    /** Returns the login system used to login the current user (null if the user is not logged in).
     *
     * @retval string: ID of the login system used to authenticate the current user;
     */
    public static function getLoginSystem() {
        return $_SESSION['LOGIN_SYSTEM'];
    }//getLoginSystem
    
    
    // =======================================================================================================
    // Packages management functions
    
    /** Returns the list of packages installed on the platform.
     *
     * @retval array
     *        an associative array of the form
     *    <pre><code class="php">[
     *        "package_id" => [
     *            "id" : string,                    // ID of the package (identical to package_id)
     *            "root" : string,              // Path to the root of the package
     *            "name" : string,                // name of the package
     *            "description" : string,            // brief description of the package
     *            "dependencies" : [
     *                "system-packages" : [],        // list of system packages required by the
     *                package
     *                "packages" : []                // list of \\compose\\ packages required by
     *                the package
     *            ],
     *            "url_rewrite" : [
     *                "rule_id" : [
     *                    "pattern" : string,        // regex of the rule for the URI to be
     *                    compared against
     *                    "replace" : string        // replacement template using group-specific
     *                    variables (e.g., $1)
     *                ],
     *                ...
     *            ]
     *            "enabled" : boolean                // whether the package is enabled
     *        ],
     *        ...                                // other packages
     *    ]</code></pre>
     */
    public static function getPackagesList() {
        return self::$packages;
    }//getPackagesList
    
    
    /** Returns whether the package specified is installed on the platform.
     *
     * @param string $package
     *        the name of the package to check.
     *
     * @retval boolean
     *        whether the package exists.
     */
    public static function packageExists($package) {
        return array_key_exists($package, self::$packages);
    }//packageExists
    
    
    /** Returns whether the specified package is enabled.
     *
     *    If the package in not installed, `FALSE` will be returned.
     *
     * @param string $package
     *        the name of the package to check.
     *
     * @retval boolean
     *        whether the package is enabled.
     */
    public static function isPackageEnabled($package) {
        // open package status database
        $packages_db = new Database('core', 'disabled_packages');
        // disabled if the key exists
        return !$packages_db->key_exists($package);
    }//isPackageEnabled
    
    
    public static function installPackage($package) {
        return self::packageManagerBatch([$package], [], []);
    }//installPackage
    
    
    public static function updatePackage($package) {
        return self::packageManagerBatch([], [$package], []);
    }//updatePackage
    
    
    public static function removePackage($package) {
        return self::packageManagerBatch([], [], [$package]);
    }//removePackage
    
    
    public static function packageManagerBatch($to_install, $to_update, $to_remove) {
        $to_remove = array_diff($to_remove, ['core']);
        $package_manager_py = sprintf('%s/lib/python/compose/package_manager.py', $GLOBALS['__SYSTEM__DIR__']);
        $install_arg = '--install ' . implode(' ', $to_install);
        $update_arg = '--update ' . implode(' ', $to_update);
        $uninstall_arg = '--uninstall ' . implode(' ', $to_remove);
        $cmd = sprintf('python3 "%s" %s %s %s 2>&1', $package_manager_py, (count($to_install) > 0) ? $install_arg : '', (count($to_update) > 0) ? $update_arg : '', (count($to_remove) > 0) ? $uninstall_arg : '');
        $output = [];
        $exit_code = 0;
        exec($cmd, $output, $exit_code);
        $success = boolval($exit_code == 0);
        // invalidate cache
        self::$cache->clear();
        // ---
        if ($success) {
            return ['success' => TRUE, 'data' => NULL];
        }
        // parse error (remove comments)
        $output = array_values(array_filter(array_values($output), function ($e) {
            return substr(ltrim($e), 0, 1) !== '#';
        }));
        $err_data = json_decode($output[0], TRUE);
        $err_data = array_map(htmlspecialchars, explode('\n', $err_data['message']));
        return ['success' => FALSE, 'data' => $err_data];
    }//packageManagerBatch
    
    
    /** Enables a package installed on the platform.
     *
     *    If the package specified is not installed, the function reports a failure state.
     *
     * @param string $package
     *        the name of the package to enable.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function enablePackage($package) {
        if (!self::packageExists($package)) {
            return [
                'success' => FALSE,
                'data' => sprintf('The package "%s" does not exist', $package)
            ];
        }
        // open package status database
        $packages_db = new Database('core', 'disabled_packages');
        // remove key if it exists
        if ($packages_db->key_exists($package)) {
            return $packages_db->delete($package);
        }
        return ['success' => TRUE, 'data' => NULL];
    }//enablePackage
    
    
    /** Disables a package installed on the platform.
     *
     *    If the package specified is not installed, the function reports a failure state.
     *
     * @param string $package
     *        the name of the package to disable.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function disablePackage($package) {
        if ($package == 'core') {
            return ['success' => FALSE, 'data' => 'The Core package cannot be disabled'];
        }
        if (!self::packageExists($package)) {
            return [
                'success' => FALSE,
                'data' => sprintf('The package "%s" does not exist', $package)
            ];
        }
        // open package status database
        $packages_db = new Database('core', 'disabled_packages');
        // create key if it does not exist
        return $packages_db->write($package, []);
    }//disablePackage
    
    
    /** Returns the settings for a given package as an instance of \system\classes\EditableConfiguration.
     *
     * @param string $package_name
     *        the ID of the package to retrieve the settings for.
     *
     * @retval mixed
     *        If the package is installed, it returns an associative array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the configuration was successfully loaded
     *        "data" => mixed        // instance of EditableConfiguration or a string error
     *        message
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains a string with the error when `success` is `FALSE`.
     *        If the package is not installed, the function returns `NULL`.
     */
    public static function getPackageSettings($package_name) {
        if (key_exists($package_name, self::$settings)) {
            return self::$settings[$package_name];
        }
        return NULL;
    }//getPackageSettings
    
    
    /** Returns information for a given package as an associative array;
     *
     * @param string $package_name
     *        the ID of the package to retrieve the info for.
     *
     * @param string $attribute
     *        (optional) the key of the attribute to fetch from the info array.
     *
     * @retval mixed
     *        If the parameter $attribute is passed, it returns the value of such
     *    attribute for the given package, `NULL` if the attribute cannot be found.
     *
     *    If the parameter $attribute is NOT passed, it returns an associative array
     *    where keys are attribute names and values their value.
     */
    public static function getPackageDetails($package_name, $attribute = NULL) {
        $pkgs = self::getPackagesList();
        $pkg_details = $pkgs[$package_name];
        if (is_null($attribute)) {
            return $pkg_details;
        } else {
            if (is_array($pkg_details)) {
                return $pkg_details[$attribute];
            }
            return NULL;
        }
    }//getPackageDetails
    
    
    /** Returns the root directory of the given package. If the package is not installed
     *  it returns the directory where the package would be placed if installed.
     *
     * @param string $package_name
     *        the ID of the package to get the root for.
     *
     * @return string
     * @retval string
     *        Package's root directory.
     *
     *    If the package is not installed the directory where the package would be placed
     *  if installed is returned.
     */
    public static function getPackageRootDir($package_name) {
        if (self::packageExists($package_name)) {
            return self::getPackageDetails($package_name, 'root');
        } else {
            return join_path($GLOBALS['__USERDATA__PACKAGES__DIR__'], $package_name);
        }
    }//getPackageRootDir
    
    
    /** Returns the settings for a given package as an associative array.
     *
     * @param string $package_name
     *        the ID of the package to retrieve the settings for.
     *
     * @retval mixed
     *        If the function succeeds, it returns an associative array of the form
     *    <pre><code class="php">[
     *        "key" => "value",
     *        ...                // other entries
     *    ]</code></pre>
     *        where, `key` can ba any configuration key exported by the package
     *        and `value` its value.
     *        If the package is not installed, the function returns `NULL`.
     *        If an error occurred while reading the configuration of the given
     *        package, a `string` containing the error is returned.
     */
    public static function getPackageSettingsAsArray($package_name) {
        if (key_exists($package_name, self::$settings)) {
            if (self::$settings[$package_name]['success']) {
                return self::$settings[$package_name]['data']->asArray();
            }
            return self::$settings[$package_name]['data'];
        }
        return NULL;
    }//getPackageSettingsAsArray
    
    
    /** Returns the value of the given setting key for the given package.
     *
     * @param string $key
     *        the setting key to retrieve;
     *
     * @param string $package_name
     *        (optional) Name of the package the requested setting belongs to. Default is 'core';
     *
     * @param string $default_value
     *        the default value returned if the key does not exist.
     *        DEFAULT = null;
     *
     * @retval mixed
     *        If the function succeeds, it returns the value of the setting key specified.
     *        If the package is not installed or an error occurred while reading the
     *        configuration for the given package, `NULL` is returned.
     */
    public static function getSetting($key, $package_name = 'core', $default_value = NULL) {
        if (key_exists($package_name, self::$settings)) {
            if (self::$settings[$package_name]['success']) {
                $res = self::$settings[$package_name]['data']->get($key, $default_value);
                if (!$res['success']) {
                    return $default_value;
                }
                return $res['data'];
            }
            return $default_value;
        }
        return $default_value;
    }//getSetting
    
    
    /** Sets the value for the given setting key of the given package.
     *
     * @param string $package_name
     *        the ID of the package the setting key belongs to;
     *
     * @param string $key
     *        the setting key to set the value for;
     *
     * @param string $value
     *        the new value to store in the package's settings;
     *
     * @retval mixed
     *        If the function succeeds, it returns `TRUE`.
     *        If the package is not installed, the function returns `NULL`.
     *        If an error occurred while writing the configuration of the given
     *        package, a `string` containing the error is returned.
     */
    public static function setSetting($package_name, $key, $value) {
        if (key_exists($package_name, self::$settings)) {
            if (self::$settings[$package_name]['success']) {
                // update the key,value pair
                $res = self::$settings[$package_name]['data']->set($key, $value);
                if (!$res['success']) {
                    return $res;
                }
                // commit the new configuration
                $res = self::$settings[$package_name]['data']->commit();
                if (!$res['success']) {
                    return $res;
                }
                // update cache (if necessary)
                $cache_key = "packages_settings";
                if (self::$cache->has($cache_key)) {
                    self::$cache->set($cache_key, self::$settings, CacheTime::HOURS_24);
                }
                // success
                return ['success' => TRUE, 'data' => NULL];
            }
            return ['success' => FALSE, 'data' => self::$settings[$package_name]['data']];
        }
        return ['success' => FALSE, 'data' => sprintf('Package "%s" not found', $package_name)];
    }//setSetting
    
    
    // =======================================================================================================
    // Package-specific resources functions
    
    
    /** Returns the URL to a package-specific image.
     *    The image file must in the directory `/images` of the package.
     *
     * @param string $image_file_with_extension
     *        Filename of the image (including extension);
     *
     * @param string $package_name
     *        (optional) Name of the package the requested image belongs to. Default is 'core';
     *
     * @retval string
     *        URL to the requested image.
     */
    public static function getImageURL($image_file_with_extension, $package_name = "core") {
        if ($package_name == "core") {
            // TODO: return placeholder if the image does not exist (only for core case, image.php does the same)
            return sprintf("%simages/%s", Configuration::$BASE, $image_file_with_extension);
        } else {
            return sprintf("%simage.php?package=%s&image=%s", Configuration::$BASE, $package_name, $image_file_with_extension);
        }
    }//getImageURL
    
    
    /** Returns the URL to a package-specific Java-Script file.
     *    The JS file must in the directory `/js` of the package.
     *
     * @param string $js_file_with_extension
     *        Filename of the Java-Script file (including extension);
     *
     * @param string $package_name
     *        (optional) Name of the package the requested Java-Script file belongs to. Default is
     *        'core';
     *
     * @retval string
     *        URL to the requested Java-Script file.
     */
    public static function getJSscriptURL($js_file_with_extension, $package_name = "core") {
        if ($package_name == "core") {
            return sprintf("%sjs/%s", Configuration::$BASE, $js_file_with_extension);
        } else {
            return sprintf("%sjs.php?package=%s&script=%s", Configuration::$BASE, $package_name, $js_file_with_extension);
        }
    }//getJSscriptURL
    
    
    /** Returns the URL to a package-specific CSS file.
     *    The CSS file must in the directory `/css` of the package.
     *
     * @param string $css_file_with_extension
     *        Filename of the CSS file (including extension);
     *
     * @param string $package_name
     *        (optional) Name of the package the requested CSS file belongs to. Default is 'core';
     *
     * @retval string
     *        URL to the requested CSS file.
     */
    public static function getCSSstylesheetURL($css_file_with_extension, $package_name = "core") {
        if ($package_name == "core") {
            return sprintf("%scss/%s", Configuration::$BASE, $css_file_with_extension);
        } else {
            return sprintf("%scss.php?package=%s&stylesheet=%s", Configuration::$BASE, $package_name, $css_file_with_extension);
        }
    }//getCSSstylesheetURL
    
    
    public static function registerCSSstylesheet($css_file_with_extension, $package_id) {
        if (!self::packageExists($package_id)) {
            return ['success' => FALSE, 'data' => NULL];
        }
        $pkg_root = self::getPackageDetails($package_id, 'root');
        array_push(self::$registered_css_stylesheets, join_path($pkg_root, 'css', $css_file_with_extension));
        return ['success' => TRUE, 'data' => NULL];
    }//registerCSSstylesheet
    
    
    public static function getRegisteredCSSstylesheets() {
        return self::$registered_css_stylesheets;
    }//getRegisteredCSSstylesheets
    
    
    // =======================================================================================================
    // Pages management functions
    
    public static function getPagesList($order = NULL) {
        if (is_null($order) || !isset(self::$pages[$order])) {
            return self::$pages;
        } else {
            return self::$pages[$order];
        }
    }//getPagesList
    
    public static function getFilteredPagesList($order = 'list', $enabledOnly = FALSE, $accessibleBy = NULL) {
        $pages = [];
        $pages_collection = self::getPagesList($order);
        $accessibleBy = is_null($accessibleBy) ? NULL : (is_array($accessibleBy) ? $accessibleBy : [$accessibleBy]);
        if (is_assoc($pages_collection)) {
            if ($order == 'by-id') {
                // collection in which pages are organized in an associative array by-id
                foreach ($pages_collection as $key => $page) {
                    if ($enabledOnly && !$page['enabled'])
                        continue;
                    if (!is_null($accessibleBy) && count(array_intersect($accessibleBy, $page['access_level'])) == 0)
                        continue;
                    //
                    $pages[$key] = $page;
                }
                return $pages;
            } else {
                // collection in which pages are organized in sub-categories
                foreach ($pages_collection as $group_id => $pages_per_group) {
                    $pages_this_group = [];
                    foreach ($pages_per_group as $page) {
                        if ($enabledOnly && !$page['enabled'])
                            continue;
                        if (!is_null($accessibleBy) && count(array_intersect($accessibleBy, $page['access_level'])) == 0)
                            continue;
                        //
                        array_push($pages_this_group, $page);
                    }
                    $pages[$group_id] = $pages_this_group;
                }
                return $pages;
            }
        } else {
            // collection in which pages are arranged in a sequence, no keys
            foreach ($pages_collection as $page) {
                if ($enabledOnly && !$page['enabled'])
                    continue;
                if (!is_null($accessibleBy) && count(array_intersect($accessibleBy, $page['access_level'])) == 0)
                    continue;
                //
                array_push($pages, $page);
            }
            return $pages;
        }
        return $pages;
    }//getFilteredPagesList
    
    
    /** Returns information for a given page as an associative array;
     *
     * @param string $page_id
     *        the ID of the page to retrieve the info for.
     *
     * @param string $attribute
     *        (optional) the key of the attribute to fetch from the info array.
     *
     * @retval mixed
     *        If the parameter $attribute is passed, it returns the value of such
     *    attribute for the given page, `NULL` if the attribute cannot be found.
     *
     *    If the parameter $attribute is NOT passed, it returns an associative array
     *    where keys are attribute names and values their value.
     */
    public static function getPageDetails($page_id, $attribute = NULL) {
        $pages = self::getPagesList('by-id');
        $page_details = $pages[$page_id];
        if (is_null($attribute)) {
            return $page_details;
        } else {
            if (is_array($page_details)) {
                return $page_details[$attribute];
            }
            return NULL;
        }
    }//getPageDetails
    
    
    /** Returns whether the page specified is installed on the platform as part of the package specified.
     *
     * @param string $package_id
     *        ID of the package the page to check belongs to.
     * @param string $page
     *        the name of the page to check.
     *
     * @retval boolean
     *        whether the page exists.
     */
    public static function pageExists($package_id, $page) {
        if (!self::packageExists($package_id)) {
            return FALSE;
        }
        $pkg_root = self::getPackageDetails($package_id, 'root');
        $page_meta = join_path($pkg_root, 'pages', $page, 'metadata.json');
        return file_exists($page_meta);
    }//pageExists
    
    
    /** Returns whether the specified page is enabled.
     *
     *    If the package in not installed, `FALSE` will be returned.
     *
     * @param string $package
     *        the name of the package the page to check belongs to.
     * @param string $page
     *        the name of the page to check.
     *
     * @retval boolean
     *        whether the page is enabled.
     */
    public static function isPageEnabled($package, $page) {
        if (!self::pageExists($package, $page)) {
            return [
                'success' => FALSE,
                'data' => sprintf('The page "%s/%s" does not exist', $package, $page)
            ];
        }
        // open page status database
        $pages_db = new Database('core', 'disabled_pages');
        // disabled if the key exists
        $page_db_key = $package . '__' . $page;
        return !$pages_db->key_exists($page_db_key);
    }//isPageEnabled
    
    
    /** Enables a page installed on the platform as part of the given package.
     *
     *    If the package specified is not installed, the function reports a failure state.
     *
     * @param string $package
     *        the name of the package the page to enable belongs to..
     * @param string $page
     *        the name of the page to enable.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function enablePage($package, $page) {
        if (!self::pageExists($package, $page)) {
            return [
                'success' => FALSE,
                'data' => sprintf('The page "%s/%s" does not exist', $package, $page)
            ];
        }
        // open page status database
        $pages_db = new Database('core', 'disabled_pages');
        // remove key if it exists
        $page_db_key = $package . '__' . $page;
        if ($pages_db->key_exists($page_db_key)) {
            return $pages_db->delete($page_db_key);
        }
        return ['success' => TRUE, 'data' => NULL];
    }//enablePage
    
    
    /** Disables a page installed on the platform as part of the given package.
     *
     *    If the package specified is not installed, the function reports a failure state.
     *
     * @param string $package
     *        the name of the package the page to disable belongs to..
     * @param string $page
     *        the name of the page to disable.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function disablePage($package, $page) {
        if ($package == 'core') {
            return ['success' => FALSE, 'data' => 'Core pages cannot be disabled'];
        }
        if (!self::pageExists($package, $page)) {
            return [
                'success' => FALSE,
                'data' => sprintf('The page "%s/%s" does not exist', $package, $page)
            ];
        }
        // open page status database
        $pages_db = new Database('core', 'disabled_pages');
        // create key if it does not exist
        $page_db_key = $package . '__' . $page;
        return $pages_db->write($page_db_key, []);
    }//disablePage
    
    
    public static function getFactoryDefaultPagePerRole($user_role) {
        $no_default = 'NO_DEFAULT_PAGE';
        if (!array_key_exists($user_role, self::$registered_user_roles['core']))
            return $no_default;
        // return default page
        return self::$registered_user_roles['core'][$user_role]['factory_default_page'];
    }//getFactoryDefaultPagePerRole
    
    
    public static function getDefaultPagePerRole($user_role, $package = 'core') {
        $no_default = 'NO_DEFAULT_PAGE';
        if (!array_key_exists($package, self::$registered_user_roles))
            return $no_default;
        if (!array_key_exists($user_role, self::$registered_user_roles[$package]))
            return $no_default;
        // return default page
        return self::$registered_user_roles[$package][$user_role]['default_page'];
    }//getDefaultPagePerRole
    
    
    // =======================================================================================================
    // Utility functions
    
    public static function getStatistics() {
        $statistics = [];
        //
        
        // TODO: Configuration::$CACHE_ENABLED is no longer available
        // Configuration::$CACHE_ENABLED = (self::$cache !== null && self::$cache instanceof phpFastCache);
        // // cache stats
        // $statistics['STATS_TOTAL_SELECT_REQS'] = ((Configuration::$CACHE_ENABLED && self::$cache->isExisting('STATS_TOTAL_SELECT_REQS'))? self::$cache->get('STATS_TOTAL_SELECT_REQS' ) : 1);
        // $statistics['STATS_CACHED_SELECT_REQS'] = ((Configuration::$CACHE_ENABLED && self::$cache->isExisting('STATS_CACHED_SELECT_REQS'))? self::$cache->get('STATS_CACHED_SELECT_REQS' ) : 1);
        
        //
        return $statistics;
    }//getStatistics
    
    
    public static function getSiteName() {
        return self::getSetting('website_name', 'core');
    }//getSiteName
    
    
    /** Returns the hash identifying the version of the <b>\\compose\\</b> codebase.
     *    This corresponds to the commit ID on git.
     *
     * @param boolean $long_hash
     *        whether to return the short hash (first 7 digits) or the long (full) commit hash.
     *        DEFAULT = false (7-digits commit hash).
     *
     * @retval string
     *        alphanumeric hash of the commit currently in use on the server
     */
    public static function getCodebaseHash($long_hash = FALSE) {
        return self::getPackageCodebaseHash('core', $long_hash);
    }//getCodebaseHash
    
    
    /** Returns the hash identifying the version of a package's codebase.
     *    This corresponds to the commit ID on git.
     *
     * @param string $package_id
     *        ID of the package for which to retrieve the git hash.
     *
     * @param boolean $long_hash
     *        whether to return the short hash (first 7 digits) or the long (full) commit hash.
     *        DEFAULT = false (7-digits commit hash).
     *
     * @retval string
     *        alphanumeric hash of the commit currently fetched on the server
     */
    public static function getPackageCodebaseHash($package_id, $long_hash = FALSE) {
        // check if this object is cached
        $cache_key = sprintf("pkg_%s_codebase_hash_%s", $package_id, $long_hash ? 'long' : 'short');
        if (self::$cache->has($cache_key))
            return self::$cache->get($cache_key);
        // hash not present in cache, get it from git
        $pkg_root = self::getPackageDetails($package_id, 'root');
        $hash = self::getGitRepositoryHash($pkg_root, $long_hash);
        // cache hash
        self::$cache->set($cache_key, $hash, CacheTime::HOURS_24);
        //
        return $hash;
    }//getPackageCodebaseHash
    
    
    /** Returns the hash identifying the version of a repository.
     *    This corresponds to the commit ID on git.
     *
     * @param string $git_repo_path
     *        absolute path to the git repository for which to retrieve the info.
     *
     * @param boolean $long_hash
     *        whether to return the short hash (first 7 digits) or the long (full) commit hash.
     *        DEFAULT = false (7-digits commit hash).
     *
     * @retval string
     *        alphanumeric hash of the commit currently fetched on the server
     */
    public static function getGitRepositoryHash($git_repo_path, $long_hash = FALSE) {
        exec(sprintf('git -C "%s" log -1', $git_repo_path) . ' --format="%H"', $info, $exit_code);
        if ($exit_code != 0) {
            $hash = 'ND';
        } else {
            $hash = ($long_hash) ? $info[0] : substr($info[0], 0, 7);
        }
        return $hash;
    }//getGitRepositoryHash
    
    
    /** Returns information about the current <b>\\compose\\</b> codebase.
     *
     * @retval array
     *        See Core::getGitRepositoryInfo().
     *
     */
    public static function getCodebaseInfo() {
        return self::getPackageCodebaseInfo('core');
    }//getCodebaseInfo
    
    
    /** Returns information about a package's codebase.
     *
     * @param string $package_id
     *        ID of the package for which to retrieve the codebase info.
     *
     * @retval array
     *        See Core::getGitRepositoryInfo().
     *
     */
    public static function getPackageCodebaseInfo($package_id) {
        // check if this object is cached
        $cache_key = sprintf("pkg_%s_codebase_info", $package_id);
        if (self::$cache->has($cache_key)) {
            return self::$cache->get($cache_key);
        }
        // hash not present in cache, get it from git
        $pkg_root = self::getPackageDetails($package_id, 'root');
        $codebase_info = self::getGitRepositoryInfo($pkg_root);
        // cache object
        self::$cache->set($cache_key, $codebase_info, CacheTime::HOURS_24);
        //
        return $codebase_info;
    }//getCodebaseInfo
    
    
    /** Returns path to theme entrypoint.
     *
     * @param string $theme_name
     *        Name of the theme.
     *
     * @param string $package_id
     *        ID of the package the theme belongs to.
     *
     * @retval string
     * @return string
     *        Path to the theme entrypoint file or NULL if the theme does not exist.
     *
     */
    public static function getThemeFile($theme_name, $package_id = 'core') {
        // check if the package exists
        if (!self::packageExists($package_id)) {
            return null;
        }
        // check if the theme exists
        $theme_file = join_path(
            self::getPackageRootDir($package_id), 'modules', 'theme', $theme_name, 'index.php');
        if (!file_exists($theme_file)) {
            return null;
        }
        // everything looks ok
        return $theme_file;
    }//getThemeFile
    
    
    /** Returns theme configuration schema.
     *
     * @param string $theme_name
     *        Name of the theme.
     *
     * @param string $package_id
     *        ID of the package the theme belongs to.
     *
     * @retval array
     * @return array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed          // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        an associative array containing the theme configuration schema otherwise.
     *
     */
    public static function getThemeConfigurationSchema($theme_name, $package_id = 'core') {
        // check if both the package and the theme exist
        if (is_null(self::getThemeFile($theme_name, $package_id))) {
            return [
                'success' => false, 'data' =>
                sprintf('Either package(%s) or theme(%s) not found', $package_id, $theme_name)
            ];
        }
        // read theme default configuration
        $theme_cfg_schema = [];
        $theme_cfg_file = join_path(self::getPackageRootDir($package_id), 'modules',
            'theme', $theme_name, 'configuration.json');
        if (file_exists($theme_cfg_file)) {
            $theme_cfg_schema = json_decode(file_get_contents($theme_cfg_file), true);
        }
        // ---
        return ['success' => true, 'data' => ComposeSchema::from_schema($theme_cfg_schema)];
    }//getThemeConfigurationSchema
    
    
    /** Returns theme configuration.
     *
     * @param string $theme_name
     *        Name of the theme.
     *
     * @param string $package_id
     *        ID of the package the theme belongs to.
     *
     * @retval array
     * @return array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed          // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`,
     *        an associative array containing the theme configuration otherwise.
     *
     */
    public static function getThemeConfiguration($theme_name, $package_id = 'core') {
        // get configuration schema
        $res = self::getThemeConfigurationSchema($theme_name, $package_id);
        if (!$res['success']) return $res;
        $cfg_schema = $res['data'];
        // read theme default configuration
        $default_res = ['success' => true, 'data' => $cfg_schema->defaults()];
        // open the database
        $db_name = 'theme_configuration';
        if (!Database::database_exists('core', $db_name)) {
            return $default_res;
        }
        $db = new Database('core', $db_name);
        $entry_key = sprintf('%s__%s', $package_id, $theme_name);
        if (!$db->key_exists($entry_key)) {
            return $default_res;
        }
        $res = $db->read($entry_key);
        if (!$res['success']) return $res;
        // merge configs
        return [
            'success' => true,
            'data' => Utils::arrayMergeAssocRecursive($default_res['data'], $res['data'], false)
        ];
    }//getThemeConfiguration
    
    
    /** Sets theme configuration.
     *
     * @param string $theme_name
     *        Name of the theme.
     *
     * @param array $configuration
     *        Theme configuration to set.
     *
     * @param string $package_id
     *        ID of the package the theme belongs to.
     *
     * @retval array
     * @return array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed          // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     *
     */
    public static function setThemeConfiguration($theme_name, $configuration, $package_id = 'core') {
        // read current configuration
        $res = self::getThemeConfiguration($theme_name, $package_id);
        if (!$res['success']) return $res;
        $current_cfg = $res['data'];
        // open the database
        $db_name = 'theme_configuration';
        $db = new Database('core', $db_name);
        // merge current and given configuration
        $cfg = Utils::arrayMergeAssocRecursive($current_cfg, $configuration, false);
        // store configuration
        $entry_key = sprintf('%s__%s', $package_id, $theme_name);
        return $db->write($entry_key, $cfg);
    }//setThemeConfiguration
    
    
    /** Returns information about a git repository (e.g., git user, git repository, remote URL, etc.)
     *
     * @param string $git_repo_path
     *        absolute path to the git repository for which to retrieve the info.
     *
     * @retval array
     * @return array
     *        An array containing info about the repository with the following details:
     *    <pre><code class="php">[
     *        "git_owner" => string,               // username of the owner of the git repository
     *        "git_repo" => string,               // name of the repository
     *        "git_host" => string,               // hostname of the remote git server
     *        "git_remote_url" => string,     // url to the remote repository
     *        "head_hash" => string,               // short commit hash of the head of the local
     *        repository
     *        "head_full_hash" => string,     // full commit hash of the head of the local
     *        repository
     *        "head_tag" => mixed                 // tag associated to the head. null if no tag is
     *        found
     *        "latest_tag" => mixed               // latest tag (back in time) of codebase. null
     *        if no tag is found.
     *    ]</code></pre>
     *
     */
    public static function getGitRepositoryInfo($git_repo_path) {
        // check if this object is cached
        $cache_key = sprintf("path_%s_codebase_info", md5($git_repo_path));
        if (self::$cache->has($cache_key)) {
            return self::$cache->get($cache_key);
        }
        // info not present in cache, get it from git
        $codebase_info = [
            'git_owner' => 'ND', 'git_repo' => 'ND', 'git_host' => 'ND',
            'git_remote_url' => 'ND',
            'head_hash' => self::getGitRepositoryHash($git_repo_path),
            'head_full_hash' => self::getGitRepositoryHash($git_repo_path, TRUE),
            'head_tag' => 'ND', 'latest_tag' => 'ND'
        ];
        exec(sprintf('git -C "%s" config --get remote.origin.url', $git_repo_path), $info, $exit_code);
        if ($exit_code != 0) {
            $codebase_info['git_user'] = 'ND';
            $codebase_info['git_repo'] = 'ND';
        } else {
            if (strcasecmp(substr($info[0], 0, 4), "http") == 0) {
                // the remote URL is in the format "http(s)://(<user>@)<host>/<owner>/<repo>(.git)"
                $pattern = "/http(s)?:\/\/([^@]+@)?(.*)\/(.*)\/(.*)(\.git)?/";
                preg_match_all($pattern, $info[0], $matches);
                $codebase_info['git_host'] = $matches[3][0];
                $codebase_info['git_owner'] = $matches[4][0];
                $codebase_info['git_repo'] = preg_replace('/\.git$/', '', $matches[5][0]);
                $codebase_info['git_remote_url'] = sprintf("http%s://%s/%s/%s.git", $matches[1][0], $codebase_info['git_host'], $codebase_info['git_owner'], $codebase_info['git_repo']);
            } else {
                // the remote URL is in the format "git@<host>:<owner>/<repo>"
                $pattern = "/[^@]+@([^:]+):(.*)\/(.*)/";
                preg_match_all($pattern, $info[0], $matches);
                $codebase_info['git_host'] = $matches[1][0];
                $codebase_info['git_owner'] = $matches[2][0];
                $codebase_info['git_repo'] = $matches[3][0];
                $codebase_info['git_remote_url'] = sprintf("http://%s/%s/%s.git", $codebase_info['git_host'], $codebase_info['git_owner'], $codebase_info['git_repo']);
            }
        }
        // get tag associated to the head (if any)
        exec(sprintf('git -C "%s" tag -l --points-at HEAD', $git_repo_path), $tag, $exit_code);
        if ($exit_code != 0) {
            $codebase_info['head_tag'] = 'ND';
        } else {
            $cb_tag = trim($tag[0]);
            $codebase_info['head_tag'] = (strlen($cb_tag) <= 0) ? 'ND' : $cb_tag;
        }
        // get closest tag going back in time (if any)
        exec(sprintf('git -C "%s" describe --abbrev=0 --tags', $git_repo_path), $latest_tag, $exit_code);
        if ($exit_code != 0) {
            $codebase_info['latest_tag'] = 'ND';
        } else {
            $latest_cb_tag = trim($latest_tag[0]);
            $codebase_info['latest_tag'] = (strlen($latest_cb_tag) <= 0) ? 'ND' : $latest_cb_tag;
        }
        // cache object
        self::$cache->set($cache_key, $codebase_info, CacheTime::HOURS_24);
        // ---
        return $codebase_info;
    }//getGitRepositoryInfo
    
    
    /** Returns the debugger data
     *
     * @retval array
     *        An array containing debugging data. The array contains an entry `key`=>`value` for
     *        each package that produced debug information, where `key` is the package ID and
     *        `value` is an array. Such array contains entries `key`=>`debug_entry`, with `key` a
     *        unique identifier of the test, and
     *        `debug_entry` a tuple of the form [`test_value`, `test_format`]. `test_value` is the
     *        outcome of the test, and `test_format` indicates how the `test_value` should be
     *        interpreted. `test_format` contains values from the enum class
     *        \system\classes\Formatter.
     *
     */
    public static function getDebugInfo() {
        return self::$debugger_data;
    }//getDebugInfo
    
    
    public static function updateBase($version = NULL) {
        $branch = 'stable';
        if (is_null($version)) {
            $version = 'devel';
            $branch = 'devel';
        }
        // fetch everything new
        exec(sprintf('git -C "%s" fetch origin 2>&1', $GLOBALS['__COMPOSE__DIR__']), $info, $exit_code);
        if ($exit_code != 0) {
            return ['success' => FALSE, 'data' => implode('<br/>', $info)];
        }
        // checkout branch
        exec(sprintf('git -C "%s" checkout %s 2>&1', $GLOBALS['__COMPOSE__DIR__'], $branch), $info, $exit_code);
        if ($exit_code != 0) {
            return ['success' => FALSE, 'data' => implode('<br/>', $info)];
        }
        // pull new code
        exec(sprintf('git -C "%s" pull origin %s --tags 2>&1', $GLOBALS['__COMPOSE__DIR__'], $branch), $info, $exit_code);
        if ($exit_code != 0) {
            return ['success' => FALSE, 'data' => implode('<br/>', $info)];
        }
        // switch to tag (if not devel update)
        if ($version !== 'devel') {
            // checkout given version
            exec(sprintf('git -C "%s" checkout %s 2>&1', $GLOBALS['__COMPOSE__DIR__'], $version), $info, $exit_code);
            if ($exit_code != 0) {
                return ['success' => FALSE, 'data' => implode('<br/>', $info)];
            }
        }
        return ['success' => TRUE, 'data' => NULL];
    }//updateBase
    
    
    public static function redirectTo($resource, $append_qs = FALSE) {
        $qs = '';
        $uri = ltrim(trim($_SERVER['REQUEST_URI']), '/');
        if ($append_qs && strlen($uri) > 0) {
            $qs = sprintf('?q=%s', base64_encode($uri));
        }
        $dry_run = isset($_GET['__NR']) ? '//' : '';
        $resource = strlen(trim($resource)) == 0 ? './' : $resource;
        printf('
            <script type="text/javascript" data-tag="__compose__redirect__">
                %swindow.open("%s%s%s", "_top");
            </script>',
            $dry_run,
            (substr($resource, 0, 4) == 'http') ? '' : Configuration::$BASE,
            $resource,
            $qs
        );
        if (!$dry_run) {
            die();
            exit;
        }
    }//redirectTo
    
    
    public static function getBrowserHostname() {
        return strstr($_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'], ':', TRUE);
    }//getBrowserHostname
    
    
    public static function openAlert($type, $message) {
        echo sprintf("<script type=\"application/javascript\">
      	$(document).ready(function() {
      		openAlert('%s', \"%s\");
      	});
      </script>", $type, addslashes($message));
    }//openAlert
    
    public static function requestAlert($type, $message) {
        if (!in_array($type, ['INFO', 'ERROR', 'WARNING'])) {
            self::throwErrorF('Unknown alert type "%s". Allowed values are ["%s"]', $type, implode('", "', [
                'INFO',
                'ERROR',
                'WARNING'
            ]));
        }
        $alert_key = sprintf('_ALERT_%s', $type);
        $_SESSION[$alert_key] = $message;
    }//requestAlert
    
    public static function throwError($errorMsg) {
        $_SESSION['_ERROR_PAGE_MESSAGE'] = $errorMsg;
        //
        self::redirectTo('error');
    }//throwError
    
    
    public static function throwErrorF(...$args) {
        $_SESSION['_ERROR_PAGE_MESSAGE'] = call_user_func_array('sprintf', $args);
        //
        self::redirectTo('error');
    }//throwErrorF
    
    
    public static function throwException($exceptionMsg) {
        self::throwError($exceptionMsg);
    }//throwException
    
    
    // public static function sendEMail($to, $subject, $template, $replace, $replyTo=null){
    // 	// prepare the message body
    // 	$res = EmailTemplates::fill($template, $replace);
    // 	if (!$res['success']) {
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
    // 	if (!in_array(Configuration::$NOREPLY_MAIL_SECURE_PROTOCOL, array('', 'none') )) {
    // 		$mail->SMTPSecure = Configuration::$NOREPLY_MAIL_SECURE_PROTOCOL;  	// Enable TLS encryption, `ssl` also accepted
    // 	}
    // 	$mail->Port = Configuration::$NOREPLY_MAIL_SERVER_PORT;
    // 	//
    // 	$mail->From = Configuration::$NOREPLY_MAIL_ADDRESS;
    // 	$mail->FromName = self::getSiteName();
    // 	$mail->addAddress($to);     										// Add a recipient
    // 	//
    // 	if ($replyTo !== null) {
    // 		$mail->addReplyTo($replyTo['email'], $replyTo['name']);
    // 	}
    // 	//
    // 	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    			// Add an Attachment
    // 	$mail->isHTML(true);                                  				// Set email format to HTML
    // 	//
    // 	$mail->Subject = $subject;
    // 	$mail->Body = $body;
    // 	//
    // 	if(!$mail->send()) {
    // 		return array('success' => false, 'data' => $mail->ErrorInfo);
    // 	} else {
    // 		return array('success' => true, 'data' => null);
    // 	}
    // }//sendEMail
    
    
    public static function getErrorRecordsList() {
        // open errors DB
        $errors_db = new Database('core', 'errors');
        // get list of keys
        return $errors_db->list_keys();
    }//getErrorRecordsList
    
    
    public static function getErrorRecord($error_id) {
        // open errors DB
        $errors_db = new Database('core', 'errors');
        // get item
        return $errors_db->read($error_id);
    }//getErrorRecord
    
    
    public static function collectErrorInfo($error_msg) {
        // open errors DB
        $errors_db = new Database('core', 'errors');
        // get user info
        $user = NULL;
        if (self::isUserLoggedIn()) {
            $user = Core::getUserLogged('username');
        }
        // create error record
        $error_id = strtotime("now");
        $error = [
            'id' => $error_id, 'datetime' => gmdate("Y-m-d H:i:s", $error_id),
            'message' => $error_msg, 'user' => $user
        ];
        // push error to DB
        $errors_db->write($error_id, $error);
    }//collectErrorInfo
    
    
    public static function deleteErrorRecord($error_id) {
        // open errors DB
        $errors_db = new Database('core', 'errors');
        // remove item
        return $errors_db->delete($error_id);
    }//deleteErrorRecord
    
    
    public static function collectDebugInfo($package, $test_id, $test_value, $test_type) {
        if (!Configuration::$DEBUG)
            return;
        if (!key_exists($package, self::$debugger_data))
            self::$debugger_data[$package] = [];
        // add debug test tuple
        self::$debugger_data[$package][$test_id] = [$test_value, $test_type];
    }//collectDebugInfo
    
    // TODO: DO NOT USE: moving to Utils, use Utils::generateRandomString() instead
    public static function generateRandomString($length) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);
        //
        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }
        return $result;
    }//generateRandomString
    
    
    public static function verbose($verbose_flag = TRUE) {
        self::$verbose = $verbose_flag;
    }//verbose
    
    
    public static function debug($debug_flag = TRUE) {
        self::$debug = $debug_flag;
    }//debug
    
    
    public static function log($type, $message, ...$args) {
        if (self::$debug) {
            echo vsprintf($message, $args);
            echo '<br>';
        }
    }//log
    
    
    public static function regenerateSessionID($delete_old_session = FALSE) {
        session_regenerate_id($delete_old_session);
    }//regenerateSessionID
    
    
    // =================================================================================================================
    // =================================================================================================================
    //
    //
    // Private functions
    
    
    private static function _set_umask($umask) {
        /*
        The umask defines what privileges can be assigned to newly created files and directories.

          umask = 0WGE

        where,
          W = Owner
          G = Group
          E = Everybody

        each channel above can be masked using the following values:
          0 : read, write and execute
          1 : read and write
          2 : read and execute
          3 : read only
          4 : write and execute
          5 : write only
          6 : execute only
          7 : no permissions
        */
        umask(octdec($umask));
    }//_set_umask
    
    
    /**
     * Recursive dependency resolution
     *
     * @param string $item Item to resolve dependencies for
     * @param array $items List of all items with dependencies
     * @param array $resolved List of resolved items
     * @param array $unresolved List of unresolved items
     *
     * @return array
     */
    function _dep_solve_dependencies_graph($item, array $items, array $resolved, array $unresolved) {
        array_push($unresolved, $item);
        if (!array_key_exists($item, $items)) {
            return [$resolved, $unresolved];
        }
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
        while (($index = array_search($item, $unresolved)) !== FALSE) {
            unset($unresolved[$index]);
        }
        //
        return [$resolved, $unresolved];
    }//_dep_solve_dependencies_graph
    
    
    private static function _solve_dependencies_graph($graph) {
        $resolved = [];
        $unresolved = [];
        // resolve dependencies for each node
        foreach (array_keys($graph) as $node) {
            try {
                list ($resolved, $unresolved) = self::_dep_solve_dependencies_graph($node, $graph, $resolved, $unresolved);
            } catch (\Exception $e) {
                return ['success' => FALSE, 'data' => $e->getMessage()];
            }
        }
        //
        return ['success' => TRUE, 'data' => $resolved];
    }//_solve_dependencies_graph
    
    
    private static function _load_packages_settings($core_only = FALSE) {
        // check if this object is cached
        $cache_key = sprintf("packages_settings%s", $core_only ? '_core_only' : '');
        if (self::$cache->has($cache_key))
            return self::$cache->get($cache_key);
        //
        $packages = self::getPackagesList();
        $packages_ids = array_keys($packages);
        $settings = [];
        // iterate over the packages
        foreach ($packages_ids as $pkg_id) {
            if ($core_only && $pkg_id != 'core')
                continue;
            $pkg_settings = new EditableConfiguration($pkg_id);
            $res = $pkg_settings->sanityCheck();
            if (!$res['success']) {
                $settings[$pkg_id] = $res;
            } else {
                $settings[$pkg_id] = ['success' => TRUE, 'data' => $pkg_settings];
            }
        }
        // cache object
        self::$cache->set($cache_key, $settings, CacheTime::HOURS_24);
        //
        return $settings;
    }//_load_packages_settings
    
    
    private static function _discover_pages($core_only = FALSE) {
        // check if this object is cached
        $cache_key_pages = sprintf("available_pages%s", $core_only ? '_core_only' : '');
        $cache_key_user_types = sprintf("user_types%s", $core_only ? '_core_only' : '');
        if (self::$cache->has($cache_key_pages) && self::$cache->has($cache_key_user_types)) {
            self::$registered_user_roles = self::$cache->get($cache_key_user_types);
            return self::$cache->get($cache_key_pages);
        }
        //
        $packages = self::getPackagesList();
        $packages_ids = array_keys($packages);
        // iterate over the packages
        $pages = [
            'list' => [], 'by-id' => [], 'by-package' => [], 'by-usertype' => [],
            'by-menuorder' => [], 'by-responsive-priority' => []
        ];
        //
        foreach ($packages_ids as $pkg_id) {
            if ($core_only && $pkg_id != 'core')
                continue;
            $pkg_root = $packages[$pkg_id]['root'];
            $pages_descriptors = join_path($pkg_root, 'pages', '*', 'metadata.json');
            $jsons = glob($pages_descriptors);
            $pages['by-package'][$pkg_id] = [];
            //
            foreach ($jsons as $json) {
                $page_id = Utils::regex_extract_group($json, "/.*pages\/(.+)\/metadata.json/", 1);
                $page_path = Utils::regex_extract_group($json, "/(.+)\/metadata.json/", 1);
                $page = json_decode(file_get_contents($json), TRUE);
                $page['package'] = $pkg_id;
                $page['id'] = $page_id;
                $page['path'] = $page_path;
                $page['enabled'] = $packages[$pkg_id]['enabled'] && (!array_key_exists('disabled', $page) || !boolval($page['disabled'])) && self::isPageEnabled($pkg_id, $page_id);
                // list
                array_push($pages['list'], $page);
                // by-id
                $pages['by-id'][$page_id] = $page;
                // by-package
                array_push($pages['by-package'][$pkg_id], $page);
                // by-usertype
                foreach ($page['access_level'] as $access) {
                    if (!isset($pages['by-usertype'][$access]))
                        $pages['by-usertype'][$access] = [];
                    array_push($pages['by-usertype'][$access], $page);
                }
                // collect user types
                foreach ($page['access_level'] as $lvl) {
                    $parts = explode(':', $lvl);
                    $package = (count($parts) == 1) ? 'core' : $parts[0];
                    $role = (count($parts) == 1) ? $parts[0] : $parts[1];
                    self::registerNewUserRole($package, $role);
                }
            }
        }
        // by-menuorder
        $menuorder = array_filter($pages['list'], function ($e) {
            return !is_null($e['menu_entry']);
        });
        usort($menuorder, function ($a, $b) {
            return ($a['menu_entry']['order'] < $b['menu_entry']['order']) ? -1 : 1;
        });
        $pages['by-menuorder'] = $menuorder;
        // by-responsive-priority
        $responsive_priority = array_filter($pages['list'], function ($e) {
            return !is_null($e['menu_entry']);
        });
        usort($responsive_priority, function ($a, $b) {
            return ($a['menu_entry']['responsive']['priority'] < $b['menu_entry']['responsive']['priority']) ? -1 : 1;
        });
        $pages['by-responsive-priority'] = $responsive_priority;
        // cache objects
        self::$cache->set($cache_key_pages, $pages, CacheTime::HOURS_24);
        self::$cache->set($cache_key_user_types, self::$registered_user_roles, CacheTime::HOURS_24);
        //
        return $pages;
    }//_discover_pages
    
    
    private static function _load_available_packages_in_dir($dir, $core_only = FALSE) {
        // check if this object is cached
        $dir_unique_id = md5($dir);
        $cache_key = sprintf("available_packages_%s%s", $dir_unique_id, $core_only ? '_core_only' : '');
        if (self::$cache->has($cache_key))
            return self::$cache->get($cache_key);
        // discover packages
        $pkgs_descriptors = $dir . "*/metadata.json";
        $jsons = glob($pkgs_descriptors);
        // iterate over the packages
        $pkgs = [];
        foreach ($jsons as $json) {
            $pkg_id = Utils::regex_extract_group($json, "/.*\/([^\/]+)\/metadata.json/", 1);
            if ($core_only && $pkg_id != 'core')
                continue;
            $pkg_root = Utils::regex_extract_group($json, "/(.+)\/metadata.json/", 1) . '/';
            $pkg = json_decode(file_get_contents($json), TRUE);
            $pkg['id'] = $pkg_id;
            $pkg['root'] = $pkg_root;
            if (!key_exists('core', $pkg)) {
                $pkg['core'] = NULL;
                $pkg_core_file_name = ucfirst($pkg_id);
                $pkg_core_file_path = join_path($pkg_root, $pkg_core_file_name . ".php");
                if (file_exists($pkg_core_file_path)) {
                    $pkg['core'] = [
                        'namespace' => $pkg_id, 'file' => $pkg_core_file_name . ".php",
                        'class' => $pkg_core_file_name
                    ];
                }
            }
            if (!is_null($pkg['core'])) {
                $pkg['core']['file'] = join_path($pkg_root, $pkg['core']['file']);
            }
            // check whether the package is enabled
            $pkg['enabled'] = self::isPackageEnabled($pkg_id);
            // get package codebase version
            $pkg['codebase'] = self::getGitRepositoryInfo($pkg_root);
            // load modules
            self::_load_package_modules_list($pkg_root, $pkg);
            // create public data symlink (if it does not exist)
            $sym_link = join_path($GLOBALS['__DATA__DIR__'], $pkg_id);
            $sym_link_exists = file_exists($sym_link);
            if (!$sym_link_exists) {
                $public_data_dir = join_path($pkg_root, "data", "public");
                $pubdata_exists = file_exists($public_data_dir);
                if ($pubdata_exists) {
                    symlink($public_data_dir, $sym_link);
                }
            }
            // by-id
            $pkgs[$pkg_id] = $pkg;
        }
        // cache object
        self::$cache->set($cache_key, $pkgs, CacheTime::HOURS_24);
        //
        return $pkgs;
    }//_load_available_packages_in_dir
    
    
    private static function _discover_packages($core_only = FALSE) {
        // discover embedded packages
        $embed_pkgs = self::_load_available_packages_in_dir($GLOBALS['__EMBEDDED__PACKAGES__DIR__'], $core_only);
        // discover user packages
        $user_pkgs = self::_load_available_packages_in_dir($GLOBALS['__USERDATA__PACKAGES__DIR__'], $core_only);
        // merge packages
        $pkgs = array_merge($embed_pkgs, $user_pkgs);
        return $pkgs;
    }//_discover_packages
    
    
    private static function _load_package_modules_list(&$pkg_root, &$package_descriptor) {
        $package_descriptor['modules'] = [];
        $modules_entrypoint = [
            'renderers/blocks' => '*.php', 'background/global' => '*.php',
            'background/local' => '*.php', 'login' => 'index.php',
            'setup' => 'index.php', 'profile' => 'index.php',
            'theme' => '*/index.php'
        ];
        // load modules
        foreach ($modules_entrypoint as $key => $entrypoint) {
            $modules_path = join_path($pkg_root, 'modules', $key, $entrypoint);
            $modules = glob($modules_path);
            if (count($modules)) {
                $package_descriptor['modules'][$key] = $modules;
            }
        }
    }//_load_package_modules_list
    
}//Core

?>

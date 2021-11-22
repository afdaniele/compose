<?php /** @noinspection PhpIncludeInspection */

/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 11/18/14
 * Time: 5:05 PM
 */

namespace system\classes;

use exceptions\FileNotFoundException;

require_once __DIR__ . '/libs/booleanval.php';


class Configuration {
    
    private static $initialized = false;
    
    // Fields
    public static $TIMEZONE;
    public static $GMT;
    public static $LANG;
    public static $DEBUG = false;
    
    public static $BASE;
    public static $PAGE;
    public static $ACTION;
    public static $ARG1;
    public static $ARG2;
    public static $TOKEN;
    
    public static $IS_MOBILE = false;
    
    public static $THEME_CONFIG = [];
    
    public static $CACHE_SYSTEM;
    public static $WEBAPI_VERSION;
    public static $ASSETS_STORE_URL;
    public static $ASSETS_STORE_VERSION;
    
    
    /** Initializes the Configuration.
     *
     * @throws FileNotFoundException
     */
    public static function init(): bool {
        $configFile = __DIR__ . '/../config/configuration.php';
        //
        if (!self::$initialized) {
            // default values
            $TIMEZONE = null;
            $LANG = "en";
            $CACHE_SYSTEM = null;
            $WEBAPI_VERSION = null;
            $ASSETS_STORE_URL = null;
            $ASSETS_STORE_VERSION = null;
            // load configuration file
            if (!file_exists($configFile)) {
                // try to load the default configuration file
                $configFile = __DIR__ . '/../config/configuration.default.php';
                if (!file_exists($configFile)) {
                    throw new FileNotFoundException($configFile);
                } else {
                    require($configFile);
                }
            } else {
                require($configFile);
            }
            //
            self::$TIMEZONE = $TIMEZONE;
            self::$LANG = $LANG;
            //
            self::$CACHE_SYSTEM = $CACHE_SYSTEM;
            self::$WEBAPI_VERSION = $WEBAPI_VERSION;
            self::$ASSETS_STORE_URL = $ASSETS_STORE_URL;
            self::$ASSETS_STORE_VERSION = $ASSETS_STORE_VERSION;
            // set TOKEN
            self::$TOKEN = $_SESSION['TOKEN'] ?? null;
            //
            self::$initialized = true;
        }
        return true;
    }//init
    
}//Configuration

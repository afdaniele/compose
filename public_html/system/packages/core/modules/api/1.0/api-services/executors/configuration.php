<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once $GLOBALS['__SYSTEM__DIR__'] . '/classes/Core.php';
require_once $GLOBALS['__SYSTEM__DIR__'] . '/classes/Cache.php';

use system\classes\Core;
use system\classes\CacheProxy;

require_once $GLOBALS['__SYSTEM__DIR__'] . '/api/1.0/utils/utils.php';


function execute(&$service, &$actionName, &$arguments) {
    $action = $service['actions'][$actionName];
    //
    switch ($actionName) {
        case 'get':
            if (!Core::packageExists($arguments['package'])) {
                return response400BadRequest(sprintf('The package "%s" does not exist', $arguments['package']));
            }
            //
            $res = Core::getPackageSettings($arguments['package']);
            if (!$res['success']) {
                return response500InternalServerError($res['data']);
            }
            //
            $setts = $res['data'];
            $res = $setts->get($arguments['key']);
            if (!$res['success']) {
                return response500InternalServerError($res['data']);
            }
            //
            return response200OK([
                'package' => $arguments['package'],
                'key' => $arguments['key'],
                'value' => $res['data']
            ]);
            break;
        //
        case 'set':
            $package_name = $arguments['package'];
            unset($arguments['package']);
            // open session to have access to login info
            Core::startSession();
            // handle first-setup case: the user is not logged in but the platform is not configured
            if (!Core::isUserLoggedIn() && Core::isComposeConfigured()) {
                return response401Unauthorized();
            }
            // make sure that the package exists
            if (!Core::packageExists($package_name)) {
                return response400BadRequest(sprintf('The package "%s" does not exist', $package_name));
            }
            // get editable settings for the package
            $res = Core::getPackageSettings($package_name);
            if (!$res['success']) {
                return response500InternalServerError($res['data']);
            }
            // get editable configuration
            $setts = $res['data'];
            // get configuration schema
            $setts_schema = $setts->getSchema();
            // get new configuration
            $pkg_cfg = &$arguments['configuration'];
            // prepare arguments
            $w = function ($_, &$value, &$schema) use (&$pkg_cfg) {
                if (!is_null($schema) && $schema->has('.type')) {
                    $type = $schema->get('.type');
                    prepareArgument($type, $value);
                }
            };
            $setts_schema->walk($w, $pkg_cfg);
            // check arguments
            $out = null;
            $res = checkArgument('configuration', $arguments, $setts_schema->asArray(), $out, false);
            if ($res !== true) {
                return response400BadRequest($out['message']);
            }
            // go through the arguments and try to store them in the configuration
            foreach ($pkg_cfg as $key => $value) {
                $res = $setts->set($key, $value);
                if (!$res['success']) {
                    return response500InternalServerError($res['data']);
                }
            }
            // commit changes to disk
            $res = $setts->commit();
            if (!$res['success']) {
                return response500InternalServerError($res['data']);
            }
            // clear both package-specific and core cache
            $pkg_cache = new CacheProxy($package_name);
            $pkg_cache->clear();
            $core_cache = new CacheProxy('core');
            $core_cache->clear();
            if ($package_name == 'core') {
                $api_cache = new CacheProxy('api');
                $api_cache->clear();
            }
            //
            return response200OK();
            break;
        //
        default:
            return response404NotFound(sprintf("The command '%s' was not found", $actionName));
            break;
    }
}//execute

?>

<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


$SYSTEM = $GLOBALS['__SYSTEM__DIR__'];


require_once $SYSTEM . '/classes/Core.php';
require_once $SYSTEM . '/classes/Cache.php';
require_once $SYSTEM . '/api/1.0/utils/utils.php';

use exceptions\ConfigurationException;
use exceptions\GenericException;
use exceptions\IOException;
use exceptions\PackageNotFoundException;
use exceptions\SchemaViolationException;
use system\classes\Core;
use system\classes\CacheProxy;


function execute($service, $actionName, &$arguments): APIResponse {
    switch ($actionName) {
        case 'get':
            // get package settings
            try {
                $setts = Core::getPackageSettings($arguments['package']);
            } catch (PackageNotFoundException $e) {
                return APIResponse::fromException($e, 400);
            }
            // get value
            try {
                $value = $setts->get($arguments['key']);
            } catch (SchemaViolationException $e) {
                return APIResponse::fromException($e, 400);
            }
            //
            return response200OK([
                'package' => $arguments['package'],
                'key' => $arguments['key'],
                'value' => $value
            ]);
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
            try {
                $setts = Core::getPackageSettings($arguments['package']);
            } catch (PackageNotFoundException $e) {
                return APIResponse::fromException($e, 400);
            }
            // get configuration schema
            $schema = $setts->getSchema();
            // get new configuration
            $pkg_cfg = &$arguments['configuration'];
            // validate new configuration
            try {
                $schema->validate($pkg_cfg);
            } catch (SchemaViolationException $e) {
                return APIResponse::fromException($e, 400);
            }
            // go through the arguments and try to store them in the configuration
            foreach ($pkg_cfg as $key => $value) {
                try {
                    $setts->set($key, $value);
                } catch (ConfigurationException $e) {
                    return APIResponse::fromException($e, 500);
                }
            }
            // commit changes to disk
            try {
                $setts->commit();
            } catch (GenericException | IOException $e) {
                return APIResponse::fromException($e, 500);
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
        //
        default:
            return response404NotFound(sprintf("The command '%s' was not found", $actionName));
    }
}//execute

?>

<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes\api\endpoints;


use exceptions\ConfigurationException;
use exceptions\GenericException;
use exceptions\IOException;
use exceptions\PackageNotFoundException;
use exceptions\SchemaViolationException;
use system\classes\api\APIResponse;
use system\classes\api\IAPIAction;
use system\classes\api\RESTfulAPIAction;
use system\classes\CacheProxy;
use system\classes\Core;
use system\classes\api\APIUtils;


class APIAction extends IAPIAction {
    
    static function execute(RESTfulAPIAction $action, array $input): APIResponse {
        $package_name = $input['package'];
        unset($input['package']);
        // open session to have access to login info
        Core::startSession();
        // handle first-setup case: the user is not logged in but the platform is not configured
        if (!Core::isUserLoggedIn() && Core::isComposeConfigured()) {
            return APIUtils::response401Unauthorized();
        }
        // make sure that the package exists
        if (!Core::packageExists($package_name)) {
            return APIUtils::response400BadRequest(sprintf('The package "%s" does not exist', $package_name));
        }
        // get editable settings for the package
        $setts = Core::getPackageSettings($package_name);
        // get configuration schema
        $schema = $setts->getSchema();
        // get new configuration
        $pkg_cfg = $input['configuration'] ?? [];
        // validate new configuration
        $schema->validate($pkg_cfg);
        // go through the arguments and try to store them in the configuration
        foreach ($pkg_cfg as $key => $value) {
            $setts->set($key, $value);
        }
        // commit changes to disk
        $success = $setts->commit();
        if (!$success)
            return APIUtils::response400BadRequest("Generic error, configuration could not be saved.");
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
        return APIUtils::response200OK();
    }
    
}

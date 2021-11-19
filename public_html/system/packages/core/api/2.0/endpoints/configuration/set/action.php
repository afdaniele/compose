<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use exceptions\ConfigurationException;
use exceptions\GenericException;
use exceptions\IOException;
use exceptions\PackageNotFoundException;
use exceptions\SchemaViolationException;
use system\classes\CacheProxy;
use system\classes\Core;


class APIAction extends RESTfulAPIAction {
    
    protected function execute(array $input): APIResponse {
        $package_name = $input['package'];
        unset($input['package']);
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
            $setts = Core::getPackageSettings($package_name);
        } catch (PackageNotFoundException $e) {
            return APIResponse::fromException($e, 400);
        }
        // get configuration schema
        $schema = $setts->getSchema();
        // get new configuration
        $pkg_cfg = &$input['configuration'];
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
    }
    
}

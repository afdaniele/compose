<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use exceptions\PackageNotFoundException;
use exceptions\SchemaViolationException;
use system\classes\Core;


class APIAction extends RESTfulAPIAction {
    
    protected function execute(array $input): APIResponse {
        // get package settings
        try {
            $setts = Core::getPackageSettings($input['package']);
        } catch (PackageNotFoundException $e) {
            return APIResponse::fromException($e, 400);
        }
        // get value
        try {
            $value = $setts->get($input['key']);
        } catch (SchemaViolationException $e) {
            return APIResponse::fromException($e, 400);
        }
        //
        return response200OK([
            'package' => $input['package'],
            'key' => $input['key'],
            'value' => $value
        ]);
    }
    
}

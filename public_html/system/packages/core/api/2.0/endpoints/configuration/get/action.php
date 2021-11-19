<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes\api\endpoints;


use exceptions\PackageNotFoundException;
use exceptions\SchemaViolationException;
use system\classes\api\APIResponse;
use system\classes\api\APIUtils;
use system\classes\api\IAPIAction;
use system\classes\api\RESTfulAPIAction;
use system\classes\Core;


class APIAction extends IAPIAction {
    
    static function execute(RESTfulAPIAction $action, array $input): APIResponse {
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
        return APIUtils::response200OK([
            'package' => $input['package'],
            'key' => $input['key'],
            'value' => $value
        ]);
    }
    
}

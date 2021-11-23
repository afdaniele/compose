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
        $setts = Core::getPackageSettings($input['package']);
        // get value
        $value = $setts->get($input['key']);
        //
        return APIUtils::response200OK([
            'package' => $input['package'],
            'key' => $input['key'],
            'value' => $value
        ]);
    }
    
}

<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes\api\endpoints;


use system\classes\api\APIResponse;
use system\classes\api\APIUtils;
use system\classes\api\IAPIAction;
use system\classes\api\RESTfulAPIAction;


class APIAction extends IAPIAction {
    
    static function execute(RESTfulAPIAction $action, array $input): APIResponse {
        // TODO: implement API action here
        return APIUtils::response200OK();
    }
    
}

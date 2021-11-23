<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes\api\endpoints;


use exceptions\BaseRuntimeException;
use system\classes\api\APIResponse;
use system\classes\api\APIUtils;
use system\classes\api\IAPIAction;
use system\classes\api\RESTfulAPIAction;
use system\classes\Core;


class APIAction extends IAPIAction {
    
    static function execute(RESTfulAPIAction $action, array $input): APIResponse {
        if (Core::isUserLoggedIn()) {
            // error
            return APIUtils::response412PreconditionFailed('You are already logged in');
        }
        // try to login
        $logged_in = Core::logInAsDeveloper();
        if (!$logged_in)
            return APIUtils::response400BadRequest("Generic error, user could not be logged in.");
        // success
        return APIUtils::response200OK();
    }
    
}

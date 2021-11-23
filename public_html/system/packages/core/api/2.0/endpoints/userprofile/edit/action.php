<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes\api\endpoints;


use system\classes\api\APIResponse;
use system\classes\api\APIUtils;
use system\classes\api\IAPIAction;
use system\classes\api\RESTfulAPIAction;
use system\classes\Core;


class APIAction extends IAPIAction {
    
    static function execute(RESTfulAPIAction $action, array $input): APIResponse {
        // open user profile
        $user = Core::openUserInfo($input['user']);
        // update info
        // - active
        if (array_key_exists('active', $input)) {
            $user->set('active', boolval($input['active']));
        }
        // - role
        if (array_key_exists('role', $input)) {
            $user->set('role', $input['role']);
        }
        // commit
        $success = $user->commit();
        if (!$success)
            return APIUtils::response400BadRequest("Generic error, user could not be updated.");
        // ---
        return APIUtils::response200OK();
    }
    
}

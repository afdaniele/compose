<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes\api\endpoints;


use exceptions\BaseRuntimeException;
use exceptions\UserNotFoundException;
use system\classes\api\APIResponse;
use system\classes\api\APIUtils;
use system\classes\api\IAPIAction;
use system\classes\api\RESTfulAPIAction;
use system\classes\Core;


class APIAction extends IAPIAction {
    
    static function execute(RESTfulAPIAction $action, array $input): APIResponse {
        $user = Core::getUserLogged('username');
        // allow administrators to fetch group lists for any user
        if (isset($input['user'])) {
            if (Core::getUserRole() == 'administrator') {
                $user = $input['user'];
            } else {
                return APIUtils::response400BadRequest('Only administrators can fetch groups for other users');
            }
        }
        // fetch groups for the user
        $groups = Core::getUserGroups($user);
        // ---
        return APIUtils::response200OK(['groups' => $groups]);
    }
    
}

<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


$SYSTEM = $GLOBALS['__SYSTEM__DIR__'];

// TODO: these can probably go
require_once join_path($SYSTEM, "classes", "Core.php");
require_once join_path($SYSTEM, "classes", "Cache.php");

use system\classes\Core;


function execute($service, $actionName, &$arguments): APIResponse {
    $action = $service['actions'][$actionName];
    Core::startSession();
    //
    switch ($actionName) {
        case 'create':
        	$res = Core::createUserGroup($arguments['name'], $arguments['description']);
        	if (!$res['success']) {
        		return response400BadRequest($res['data']);
			}
        	return response200OK();
            break;
        //
        case 'list':
        	return response200OK(['groups' => Core::getGroupsList()]);
            break;
        //
        case 'delete':
        	$res = Core::deleteUserGroup($arguments['group']);
        	if (!$res['success']) {
        		return response400BadRequest($res['data']);
			}
        	return response200OK();
            break;
        //
        case 'members':
        	$res = Core::getGroupMembers($arguments['group']);
        	if (!$res['success']) {
        		return response400BadRequest($res['data']);
			}
        	return response200OK(['members' => $res['data']]);
            break;
        //
        case 'link':
        	$res = Core::addUserToGroup($arguments['user'], $arguments['group']);
        	if (!$res['success']) {
        		return response400BadRequest($res['data']);
			}
        	return response200OK();
            break;
        //
		case 'unlink':
            $res = Core::removeUserFromGroup($arguments['user'], $arguments['group']);
        	if (!$res['success']) {
        		return response400BadRequest($res['data']);
			}
        	return response200OK();
            break;
        //
        default:
            $msg = "The command '" . $actionName . "' was not found";
            return response404NotFound($msg);
            break;
    }
}//execute

?>

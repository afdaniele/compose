<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use system\classes\Core as Core;


function execute(&$service, &$actionName, &$arguments){
	$action = $service['actions'][$actionName];
	Core::startSession();
	//
	switch($actionName){
		case 'login_with_google':
			if(Core::isUserLoggedIn()){
				// error
        return response412PreconditionFailed('You are already logged in');
			}
			//
			$id_token = $arguments['id_token'];
			$res = Core::logInUserWithGoogle($id_token);
			if(!$res['success']){
        return response500InternalServerError($res['data']);
			}
			// success
			return response200OK(null);
			break;
    //
    case 'login_as_developer':
			if(Core::isUserLoggedIn()){
				// error
        return response412PreconditionFailed('You are already logged in');
			}
			//
			$res = Core::logInAsDeveloper();
			if(!$res['success']){
        return response500InternalServerError($res['data']);
			}
			// success
			return response200OK(null);
			break;
    //
		case 'edit':
			// open user profile
			$res = Core::openUserInfo($arguments['userid']);
			if(!$res['success']){
				return response400BadRequest($res['data']);
			}
			$user = $res['data'];
			// update info
			if(array_key_exists('active', $arguments)){
				$user->set('active', boolval($arguments['active']));
			}
			// commit
			$res = $user->commit();
			if(!$res['success']){
				return response400BadRequest($res['data']);
			}
			//
			return response200OK(null);
			break;
		//
		case 'logout':
			$res = Core::logOutUser();
			if(!$res['success']){
        return response500InternalServerError($res['data']);
			}
			// success
			return response200OK(null);
			break;
		//
		default:
      $msg = "The command '".$actionName."' was not found";
      return response404NotFound($msg);
			break;
	}
}//execute

?>

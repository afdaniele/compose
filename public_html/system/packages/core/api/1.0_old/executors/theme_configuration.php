<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


$SYSTEM = $GLOBALS['__SYSTEM__DIR__'];

// TODO: these can probably go
require_once join_path($SYSTEM, "classes", "Core.php");
require_once join_path($SYSTEM, "classes", "Cache.php");

use system\classes\Core;
use system\api\apiinterpreter\APIInterpreter;

require_once join_path($SYSTEM, "api", APIInterpreter::$API_VERSION, "utils", "utils.php");


function execute($service, $actionName, &$arguments): APIResponse {
    $action = $service['actions'][$actionName];
    //
    switch ($actionName) {
        case 'get':
            $package = $arguments['package'];
            $theme = $arguments['theme'];
            //
            $res = Core::getThemeConfiguration($theme, $package);
            if (!$res['success']) {
                return response500InternalServerError($res['data']);
            }
            //
            return response200OK([
                'package' => $package,
                'theme' => $theme,
                'configuration' => $res['data']
            ]);
            break;
        //
        case 'set':
            $package = $arguments['package'];
            $theme = $arguments['theme'];
            // get configuration schema
            $res = Core::getThemeConfigurationSchema($theme, $package);
            if (!$res['success']) {
                return response500InternalServerError($res['data']);
            }
            $theme_schema = $res['data'];
            // check arguments
            $out = null;
            $res = checkArgument('configuration', $arguments, $theme_schema->asArray(), $out, false);
            if ($res !== true) {
                return response400BadRequest($out['message']);
            }
            // get new theme configuration
            $theme_cfg = array_key_exists('configuration', $arguments)?
                $arguments['configuration'] : [];
            // update existing theme configuration
            $res = Core::setThemeConfiguration($theme, $theme_cfg, $package);
            if (!$res['success']) {
                return response500InternalServerError($res['data']);
            }
            // ---
            return response200OK();
            break;
        //
        default:
            return response404NotFound(sprintf("The command '%s' was not found", $actionName));
            break;
    }
}//execute

?>

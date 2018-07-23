<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 14th 2018



require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Core.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/Cache.php';
use system\classes\Core;
use system\classes\CacheProxy;

require_once $GLOBALS['__SYSTEM__DIR__'].'/api/1.0/utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'get':
			if( !Core::packageExists( $arguments['package'] ) )
				return response400BadRequest( sprintf('The package "%s" does not exist', $arguments['package']) );
			//
			$res = Core::getPackageSettings( $arguments['package'] );
			if( !$res['success'] )
				return response500InternalServerError( $res['data'] );
			//
			$setts = $res['data'];
			$res = $setts->get( $arguments['key'] );
			if( !$res['success'] )
				return response500InternalServerError( $res['data'] );
			//
			return response200OK( [
				 'package' => $arguments['package'],
				 'key' => $arguments['key'],
				 'value' => $res['data']
			] );
			break;
		//
		case 'set':
			$package_name = $arguments['package'];
			unset( $arguments['package'] );
			// get editable settings for the package
			if( !Core::packageExists( $package_name ) )
				return response400BadRequest( sprintf('The package "%s" does not exist', $package_name ) );
			//
			$res = Core::getPackageSettings( $package_name );
			if( !$res['success'] )
				return response500InternalServerError( $res['data'] );
			//
			$setts = $res['data'];
			// go through the arguments and try to store them in the configuration
			foreach( $arguments as $key => $value ){
				$res = $setts->set( $key, $value );
				if( !$res['success'] )
					return response500InternalServerError( $res['data'] );
			}
			// commit changes to disk
			$res = $setts->commit();
			if( !$res['success'] )
				return response500InternalServerError( $res['data'] );
			// clear cache
			$cache = new CacheProxy($package_name);
			$cache->clear();
			//
			return response200OK();
			break;
		//
		default:
			return response404NotFound( sprintf("The command '%s' was not found", $actionName) );
			break;
	}
}//execute

?>

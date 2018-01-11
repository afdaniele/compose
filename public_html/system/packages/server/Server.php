<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Sunday, December 31st 2017
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018



namespace system\packages\server;

require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Core.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Configuration.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Utils.php';

use \system\classes\Core as Core;
use \system\classes\Configuration as Configuration;
use \system\classes\Utils as Utils;

/**
*   Module for controlling cameras, managing recorded videos, and streaming in real-time.
*/
class Server{

	private static $initialized = false;

	// disable the constructor
	private function __construct() {}

	/** Initializes the module.
     *
     *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
     */
	public static function init(){
		if( !self::$initialized ){
			// do stuff
			return array( 'success' => true, 'data' => null );
		}else{
			return array( 'success' => true, 'data' => "Module already initialized!" );
		}
	}//init

    /** Safely terminates the module.
     *
     *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
     */
	public static function close(){
        // do stuff
		return array( 'success' => true, 'data' => null );
	}//close



	// =======================================================================================================
	// System info functions


	/** Provides information about the server.
	 *
	 *	@retval array
	 *		an array containing the following data
	 *	<pre><code class="php">[
	 *		"os_release" => string,	// the Operating System installed on the server
	 *		"cpu_model" => string,	// the model of the CPU
	 *		"ram_total" => string, 	// the total amount of RAM formatted as '# GB'
	 *		"cpu_usage" => float,	// the current CPU usage, normalized to the interval [0,1]
	 *		"ram_usage" => float 	// the current RAM usage, normalized to the interval [0,1]
	 *	]</code></pre>
	 */
	public static function getServerStatus(){
		// OS release
		exec( 'grep "DISTRIB_DESCRIPTION" /etc/lsb-release', $lsb_release, $exit_code );
		if( $exit_code != 0 ){
			$lsb_release = '<error><code>/etc/lsb-release</code> was not found</error>';
		}else{
			$lsb_release = Utils::regex_extract_group($lsb_release[0], "/DISTRIB_DESCRIPTION=\"(.+)\".*/", 1);
		}
		// CPU model
		exec( 'grep "model name" /proc/cpuinfo | sort -u', $cpu_model, $exit_code );
		if( $exit_code != 0 ){
			$cpu_model = '<error><code>/proc/cpuinfo</code> was not found</error>';
		}else{
			$cpu_model = Utils::regex_extract_group($cpu_model[0], "/model\sname\s*:\s*(.*)/", 1);
		}
		// RAM total
		exec( "free -m | grep Mem | awk '{print $2}'", $ram_total, $exit_code );
		if( $exit_code != 0 ){
			$ram_total = "<error>The command <code>free</code> is not installed</error>";
			$ram_total_float = 1.0;
		}else{
			$ram_total_float = (float)$ram_total[0];
			$ram_total = sprintf("%d GB", floor(pow(2.0, ceil(log($ram_total_float, 2.0)))/1000.0) );
		}
		// RAM usage
		exec( "free -m | grep 'buffers/cache' | awk '{print $3}'", $ram_used, $exit_code );
		if( $exit_code != 0 ){
			$ram_usage = "<error>The command <code>free</code> is not installed</error>";
		}else{
			$ram_usage = (float)$ram_used[0] / $ram_total_float;
		}
		// CPU usage
		$cpu_load = sys_getloadavg();
		$cpu_usage = (float)$load[0];
		//
		return array(
			'os_release' => $lsb_release,
			'cpu_model' => $cpu_model,
			'ram_total' => $ram_total,
			'cpu_usage' => $cpu_usage,
			'ram_usage' => $ram_usage
		);
	}//getServerStatus

}//Server

?>

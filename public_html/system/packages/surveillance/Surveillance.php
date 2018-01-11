<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Sunday, December 31st 2017
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018



namespace system\packages\surveillance;

require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Core.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Configuration.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Utils.php';

use \system\classes\Core as Core;
use \system\classes\Configuration as Configuration;
use \system\classes\Utils as Utils;

/**
*   Module for controlling cameras, managing recorded videos, and streaming in real-time.
*/
class Surveillance{

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
	// Cameras management functions

	public static function getDiskStatus( $cameraNum ){
		// Disk usage
		$disk_device = Configuration::$SURVEILLANCE[$cameraNum]['disk_dev'];
		exec( "df | grep '".$disk_device."' | sed 's/\s\s*/ /g' | awk '{print $5}'", $df, $exit_code );
		if( $exit_code != 0 ){
			$disk_usage = 1.0;
		}else{
			$disk_usage = (float)$df[0] / 100.0;
		}
		//
		return $disk_usage;
	}//getServerDiskStatus



	// =======================================================================================================
	// Live streaming functions

	public static function getSurveillanceStatus( $cameraNum ){
		// Surveillance status
		$video_path = Configuration::$SURVEILLANCE[$cameraNum]['raw_data_path'];
		exec( "ps -aux | grep ffmpeg | grep 'rtsp://duckietown-visitor:' | grep '".$video_path."' | grep -v grep", $ffmpeg, $exit_code );
		$is_recording = booleanval( $exit_code == 0 );
		$current_chunk = null;
		if( $is_recording ){
			$current_chunk = Utils::regex_extract_group($ffmpeg[0], '/.*[0-9]{4}-[0-9]{2}-[0-9]{2}_([0-9]{2}\.[0-9]{2})\.mp4/', 1);
		}
		//
		return array( 'is_recording' => $is_recording, 'chunk' => $current_chunk );
	}//getSurveillanceStatus



	// =======================================================================================================
	// Footage management functions

	public static function getSurveillancePostProcessingHistory( $cameraNum, $history_len=null, $month=null, $reverse_order=false ){
		$result = array( 'total_minutes' => 0, 'days' => array() );
		// Get the last $history_len dates
		$activity_path = Configuration::$SURVEILLANCE[$cameraNum]['activity_data_path'];
		$command = "ls -l '".$activity_path."' ";
		$command = $command."| grep -E '[0-9]{4}-".( ( $month != null )? sprintf('%02d', $month) : '[0-9]{2}' )."-[0-9]{2}' ";
		$command = $command."| awk '{print $9}' ";
		$command = $command."| sort ".( ( $reverse_order )? '-r' : '' );
		$command = $command. ( ( $history_len != null )? "| head -".$history_len : '' );
		exec( $command, $history, $exit_code );
		// Go through the dates and compute the number of hours recorded
		$chunk_len = Configuration::$SURVEILLANCE_CHUNKS_DURATION_MINUTES;
		foreach( $history as $day ){
			$day_path = $activity_path.'/'.$day.'/';
			$day_activities = array();
			exec( "ls -l '".$day_path."' | grep -E '[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}\.json' | awk '{print $9}' | sort", $day_activities, $exit_code );
			$total_recording_this_day = $chunk_len * sizeof( $day_activities );
			$result['days'][ $day ] = array(
				'chunks' => array(),
				'total_minutes' => $total_recording_this_day
			);
			$result['total_minutes'] += $total_recording_this_day;
			foreach( $day_activities as $chunk ){
				$chunk = Utils::regex_extract_group($chunk, "/[0-9]{4}-[0-9]{2}-[0-9]{2}_([0-9]{2}\.[0-9]{2})\.json/", 1);
				array_push( $result['days'][ $day ][ 'chunks' ], $chunk );
			}
		}
		return $result;
	}//getSurveillancePostProcessingHistory

	public static function getSurveillanceRecordingHistory( $cameraNum, $history_len=null, $month=null, $reverse_order=false ){
		$result = array( 'total_minutes' => 0, 'days' => array() );
		// Get the last $history_len dates
		$video_path = Configuration::$SURVEILLANCE[$cameraNum]['raw_data_path'];
		$command = "ls -l '".$video_path."' ";
		$command = $command."| grep -E '[0-9]{4}-".( ( $month != null )? sprintf('%02d', $month) : '[0-9]{2}' )."-[0-9]{2}' ";
		$command = $command."| awk '{print $9}' ";
		$command = $command."| sort ".( ( $reverse_order )? '-r' : '' );
		$command = $command. ( ( $history_len != null )? "| head -".$history_len : '' );
		exec( $command, $history, $exit_code );
		// Go through the dates and compute the number of hours recorded
		$chunk_len = Configuration::$SURVEILLANCE_CHUNKS_DURATION_MINUTES;
		foreach( $history as $day ){
			$day_path = $video_path.'/'.$day.'/';
			$day_recordings = array();
			exec( "ls -l '".$day_path."' | grep -E '[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}\.mp4' | awk '{print $9}' | sort", $day_recordings, $exit_code );
			$total_recording_this_day = $chunk_len * sizeof( $day_recordings );
			$result['days'][ $day ] = array(
				'chunks' => array(),
				'total_minutes' => $total_recording_this_day
			);
			$result['total_minutes'] += $total_recording_this_day;
			foreach( $day_recordings as $chunk ){
				$chunk = Utils::regex_extract_group($chunk, "/[0-9]{4}-[0-9]{2}-[0-9]{2}_([0-9]{2}\.[0-9]{2})\.mp4/", 1);
				array_push( $result['days'][ $day ][ 'chunks' ], $chunk );
			}
		}
		return $result;
	}//getSurveillanceRecordingHistory

	public static function sizeOfSurveillanceSegment( $cameraNum, $segment_name ){
		if( self::isSurveillanceSegmentPresent( $cameraNum, $segment_name ) ){
			$video_path = Configuration::$SURVEILLANCE[$cameraNum]['raw_data_path'];
			$segment_parts = explode( '_', $segment_name );
			$date = $segment_parts[0];
			$segment_path = sprintf("%s/%s/%s.mp4", $video_path, $date, $segment_name);
			return human_filesize( filesize($segment_path) );
		}
		//
		return null;
	}//sizeOfSurveillanceSegment


	public static function sizeOfWebMSurveillanceSegment( $cameraNum, $segment_name ){
		if( self::isSurveillanceSegmentPresent( $cameraNum, $segment_name ) ){
			$video_path = Configuration::$SURVEILLANCE[$cameraNum]['webm_data_path'];
			$segment_parts = explode( '_', $segment_name );
			$date = $segment_parts[0];
			$segment_path = sprintf("%s/%s/web_%s.mp4", $video_path, $date, $segment_name);
			return human_filesize( filesize($segment_path) );
		}
		//
		return null;
	}//sizeOfWebMSurveillanceSegment

	public static function getSurveillanceSegmentActivity( $cameraNum, $segment_name ){
		if( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}/', $segment_name) !== 1 ){
			return array('success' => false, 'data' => 'segment_name does not conform to the format required "YYYY-mm-dd_HH.mm"');
		}
		$date = Utils::regex_extract_group($segment_name, "/([0-9]{4}-[0-9]{2}-[0-9]{2})_[0-9]{2}\.[0-9]{2}/", 1);
		$activity_file = sprintf( "%s/%s/%s.json",
			Configuration::$SURVEILLANCE[$cameraNum]['activity_data_path'],
			$date,
			$segment_name
		);
		if( file_exists($activity_file) ){
			$activity_log = json_decode( file_get_contents($activity_file), true );
			return array('success' => true, 'data' => $activity_log);
		}else{
			return array('success' => false, 'data' => 'The activity log for this segment does not exist');
		}
	}//getSurveillanceSegmentActivity


	public static function getSurveillanceActivityThumbnail( $cameraNum, $date ){
		if( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $date) !== 1 ){
			return array('success' => false, 'data' => 'date does not conform to the format required "YYYY-mm-dd"');
		}
		$activity_file = sprintf( "%s/%s/thumbnail.json",
			Configuration::$SURVEILLANCE[$cameraNum]['activity_data_path'],
			$date
		);
		if( file_exists($activity_file) ){
			$activity_log = json_decode( file_get_contents($activity_file), true );
			return array('success' => true, 'data' => $activity_log);
		}else{
			return array('success' => false, 'data' => 'The activity thumbnail for this date does not exist');
		}
	}//getSurveillanceActivityThumbnail

	public static function isSurveillanceSegmentPresent( $cameraNum, $segment_name ){
		$video_path = Configuration::$SURVEILLANCE[$cameraNum]['raw_data_path'];
		if( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}/', $segment_name) !== 1 ){
			return false;
		}
		$segment_parts = explode( '_', $segment_name );
		$date = $segment_parts[0];
		$segment_path = sprintf("%s/%s/%s.mp4", $video_path, $date, $segment_name);
		//
		return file_exists($segment_path);
	}//isSurveillanceSegmentPresent


	public static function isWebMSurveillanceSegmentPresent( $cameraNum, $segment_name ){
		$video_path = Configuration::$SURVEILLANCE[$cameraNum]['webm_data_path'];
		if( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}/', $segment_name) !== 1 ){
			return false;
		}
		$segment_parts = explode( '_', $segment_name );
		$date = $segment_parts[0];
		$segment_path = sprintf("%s/%s/web_%s.mp4", $video_path, $date, $segment_name);
		//
		return file_exists($segment_path);
	}//isWebMSurveillanceSegmentPresent


}//Surveillance

?>

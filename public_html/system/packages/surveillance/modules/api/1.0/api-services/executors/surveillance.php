<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018



require_once __DIR__.'/../../../../../../../classes/Core.php';
use system\classes\Core as Core;

require_once __DIR__.'/../../../../../../../api/1.0/utils/utils.php';

require_once __DIR__.'/../../../../../Surveillance.php';
use system\packages\surveillance\Surveillance as Surveillance;


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'disk_usage':
			$disk_usage = Surveillance::getDiskStatus($arguments['camera_num']);
			//
			return array( 'code' => 200, 'status' => 'OK',
				'data' => array(
					'camera_num' => $arguments['camera_num'],
					'used' => round( $disk_usage, 2 ),
					'free' => round( 1.0-$disk_usage, 2 )
				)
			);
			break;
		//
		case 'surveillance_status':
			$surveillanceStatus = Surveillance::getSurveillanceStatus($arguments['camera_num']);
			$surveillanceStatus['camera_num'] = $arguments['camera_num'];
			//
			return array( 'code' => 200, 'status' => 'OK',
				'data' => $surveillanceStatus
			);
			break;
		//
		case 'surveillance_history':
			$history_len = 999999;
			$history_type = $arguments['type'];
			if( isset($arguments['size']) ){
				$history_len = $arguments['size'];
			}
			$history = null;
			switch ($history_type) {
				case 'recording':
					$history = Surveillance::getSurveillanceRecordingHistory( $arguments['camera_num'], $history_len, null, true );
					break;
				case 'post-processing':
					$history = Surveillance::getSurveillancePostProcessingHistory( $arguments['camera_num'], $history_len, null, true );
					break;
			}
			$history['camera_num'] = $arguments['camera_num'];
			//
			return array( 'code' => 200, 'status' => 'OK',
				'data' => $history
			);
			break;
		//
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "The command '".$actionName."' was not found" );
			break;
	}
}//execute

?>

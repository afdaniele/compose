<?php

/* Timezone settings */
$TIMEZONE = 'America/Chicago';
date_default_timezone_set($TIMEZONE);

/* Platform URLs settings */
$SHORT_SITE_NAME = "Duckieboard";
$SHORT_SITE_LINK = "Duckietown.org";
$FOOTBAR_MESSAGE = "Developed by <a href='http://www.afdaniele.com/'>Andrea F. Daniele</a>";
$BASE_URL = "http://duckietown.afdaniele.com/";
$MOBILE_BASE_URL = "http://duckietown.afdaniele.com/";

/* Database connection settings */
$MYSQL_HOST = "localhost";
$MYSQL_DBNAME = "duckietown";
$MYSQL_USERNAME = "duckietown";
$MYSQL_PASSWORD = "duckiet0wn";

/* No-Reply email address settings */
$NOREPLY_MAIL_ADDRESS = 'no-reply@duckietown.org';
$NOREPLY_MAIL_HOST = 'mail.duckietown.org';
$NOREPLY_MAIL_AUTH = true;
$NOREPLY_MAIL_USERNAME = 'no-reply.duckietown';
$NOREPLY_MAIL_PASSWORD = 'duckietown';
$NOREPLY_MAIL_SECURE_PROTOCOL = 'none';
$NOREPLY_MAIL_SERVER_PORT = 587;

/* Platform API settings */
$WEBAPI_VERSION = '1.0';

/* Caching system settings */
$CACHE_SYSTEM = 'apc';

/* Default value for dashboard settings */
$DEFAULT_VALUES_DASHBOARD_CONFIG = array(
    "maintenance_mode" => false,
	"main_page_title" => "Duckietown",
	"admin_contact_email_address" => "afdaniele@ttic.edu",
	"cache_enabled" => false,
    "camera_1_enabled" => false,
	"camera_2_enabled" => false
);

/* Default value for system settings */
$DEFAULT_VALUES_DUCKIETOWN_CONFIG = array(
    "what_the_duck" => array(
        "tests_data_path" => "/tmp"
    ),
    "surveillance" => array(
		"camera_1_disk_dev" => "/dev/sda1",
		"camera_1_raw_data_path" => "/tmp",
		"camera_1_log_data_path" => "/tmp",
		"camera_1_webm_data_path" => "/tmp",
		"camera_1_activity_data_path" => "/tmp",
		"camera_2_disk_dev" => "/dev/sda1",
		"camera_2_raw_data_path" => "/tmp",
		"camera_2_log_data_path" => "/tmp",
		"camera_2_webm_data_path" => "/tmp",
		"camera_2_activity_data_path" => "/tmp"
    ),
    "duckiefleet" => array(
        "path" => "/home/duckietown-surveillance/duckiefleet",
		"branch" => "chicago"
    ),
    "duckiebot" => array(
        "ssh_username" => "ubuntu",
		"ssh_password" => "ubuntu",
        "ros_path" => "/opt/ros/kinetic",
        "w_config_vid_pid_list" => array(
            '7392:b822'
        ),
        "d_config_vid_pid_list" => array(
            '0781:5583',
            '090c:1000'
        )
    )
);

?>

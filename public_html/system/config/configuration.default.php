<?php

/* Timezone settings */
$TIMEZONE = 'America/Chicago';
date_default_timezone_set($TIMEZONE);

/* Platform URLs settings */
$SHORT_SITE_NAME = "Duckietown";
$SHORT_SITE_LINK = "Duckietown.org";
$FOOTBAR_MESSAGE = "Developed by <a href='http://www.afdaniele.com/'>Andrea F. Daniele</a>";
$BASE_URL = "http://localhost/";
$MOBILE_BASE_URL = "http://localhost/";

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

/* Default value for customizable settings */
$MAIN_PAGE_TITLE = "Welcome!";
$ADMIN_CONTACT_MAIL_ADDRESS = 'afdaniele@ttic.edu';
$CACHE_ENABLED = false;

?>

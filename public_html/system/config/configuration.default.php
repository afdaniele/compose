<?php

/* Platform API settings */
$WEBAPI_VERSION = '1.0';

/* Caching system settings */
$CACHE_SYSTEM = 'apcu';

/* URL to a \compose\ assets store */
$ASSETS_STORE_URL = 'https://raw.githubusercontent.com/afdaniele/compose-assets-store/';

/* Branch of the \compose\ assets store defined in $ASSETS_STORE_URL */
$ASSETS_STORE_VERSION = 'v3';

/* Databases connectors */
$DEFAULT_DATABASE_TYPE = "FileDatabase";

$DATABASE_CONNECTORS = [
// Examples:
//    "core/groups" => [
//        "type" => "SQLiteDatabase",
//        "arguments" => []
//    ],
//    "my_mysql_dbs/*" => [
//        "type" => "MySQLDatabase",
//        "arguments" => [
//            "sql_hostname" => "localhost",
//            "sql_database" => "my_db",
//            "sql_username" => "my_username",
//            "sql_password" => "my_password"
//       ]
//    ]
];

?>

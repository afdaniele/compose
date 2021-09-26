<?php

namespace system\classes;

include_once __DIR__ . '/database/DatabaseEntryAbs.php';
include_once __DIR__ . '/database/FileDatabase.php';
include_once __DIR__ . '/database/SQLiteDatabase.php';
include_once __DIR__ . '/database/MySQLDatabase.php';

use system\classes\database\FileDatabase;
use system\classes\database\SQLiteDatabase;
use system\classes\database\MySQLDatabase;


class Database {
    
    // private attributes
    private $db;
    
    // Constructor
    function __construct($package, $database, $entry_regex = null) {
        list($dbclass, $dbargs) = self::_get_impl($package, $database);
        switch ($dbclass) {
            case FileDatabase::class:
                $this->db = new FileDatabase($package, $database, $entry_regex);
                return;
            case SQLiteDatabase::class:
                $this->db = new SQLiteDatabase($package, $database, $entry_regex);
                return;
            case MySQLDatabase::class:
                $this->db = new MySQLDatabase(
                    $package,
                    $database,
                    $dbargs["sql_hostname"],
                    $dbargs["sql_database"],
                    $dbargs["sql_username"],
                    $dbargs["sql_password"],
                    $entry_regex
                );
                return;
        }
    }//__construct
    
    // Private static functions
    
    private static function _get_impl($package, $database="*") {
        $db_name = sprintf("%s/%s", $package, $database);
        $db_type_name = Configuration::$DEFAULT_DATABASE_TYPE;
        $db_type_args = [];
        foreach (Configuration::$DATABASE_CONNECTORS as $db_regex => $db_connector) {
            if (fnmatch($db_regex, $db_name)) {
                $db_type_name = $db_connector["type"];
                $db_type_args = $db_connector["arguments"];
            }
        }
        $dbtype_to_dbclass = [
            "FileDatabase" => FileDatabase::class,
            "SQLiteDatabase" => SQLiteDatabase::class,
            "MySQLDatabase" => MySQLDatabase::class,
        ];
        if (!array_key_exists($db_type_name, $dbtype_to_dbclass))
            $db_type_name = Configuration::$DEFAULT_DATABASE_TYPE;
        
        return [$dbtype_to_dbclass[$db_type_name], $db_type_args];
    }
    
    // Public static functions
    
    public static function database_exists($package, $database){
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($dbclass, $_) = self::_get_impl($package, $database);
        return $dbclass::database_exists($package, $database);
    }
    
    public static function list_dbs($package){
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($dbclass, $_) = self::_get_impl($package);
        return $dbclass::list_dbs($package);
    }
    
    public static function delete_db($package, $database){
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($dbclass, $_) = self::_get_impl($package, $database);
        return $dbclass::delete_db($package, $database);
    }
    
    
    // Public functions
    
    public function read($key){
        return $this->db->read($key);
    }
    
    public function get_entry($key){
        return $this->db->get_entry($key);
    }
    
    public function write($key, $data){
        return $this->db->write($key, $data);
    }
    
    public function delete($key){
        return $this->db->delete($key);
    }
    
    public function key_exists($key){
        return $this->db->key_exists($key);
    }
    
    public function list_keys(){
        return $this->db->list_keys();
    }
    
    public function size(){
        return $this->db->size();
    }
    
    public function key_size($key){
        return $this->db->key_size($key);
    }
    
    public function is_writable($key){
        return $this->db->is_writable($key);
    }
    
}

?>

<?php

namespace system\classes\database;

use Exception;
use PDO;

require_once __DIR__ . '/SQLDatabase.php';


class MySQLDatabase extends SQLDatabase {
    
    function __construct($package, $table, $sql_hostname, $sql_database, $sql_username, $sql_password, $entry_regex = null) {
        parent::__construct($package, $table, $entry_regex);
        $conn = new PDO("mysql:host=$sql_hostname;dbname=$sql_database", $sql_username, $sql_password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }//__construct
	
	// Public functions

    public function is_writable($key) {
        return True;
    }//is_writable
    
    public static function database_exists ($package, $database) {
        // TODO: Implement database_exists() method.
        throw new Exception("Not Implemented: Function 'database_exists' is not implemented.");
    }
    
    public static function list_dbs($package) {
        // TODO: Implement list_dbs() method.
        throw new Exception("Not Implemented: Function 'list_dbs' is not implemented.");
    }
    
    public static function delete_db($package, $database) {
        // TODO: Implement delete_db() method.
        throw new Exception("Not Implemented: Function 'delete_db' is not implemented.");
    }
    
}//MySQLDatabase
?>

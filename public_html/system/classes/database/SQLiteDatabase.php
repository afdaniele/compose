<?php

namespace system\classes\database;

use PDO;

require_once __DIR__ . '/SQLDatabase.php';


class SQLiteDatabase extends SQLDatabase {
	
    // private static attributes
	private static $TABLE_SQL = "
create table data(
	key TEXT constraint data_pk primary key,
	value JSON,
	meta JSON
);
create unique index data_key_uindex on data (key);
";

    private static $dbs_location = "%sdatabases/%s";
    
    function __construct($package, $database, $entry_regex = null) {
        parent::__construct($package, $database, $entry_regex);
        // get path to DB file
        $db_fpath = $this->get_db_fpath();
        // make sure the directory exists
        $res = $this->_create_dir();
        if (!$res["success"]) {
            throw new \Exception($res["data"]);
        }
        // find out whether the DB exists
		$db_exists = file_exists($db_fpath);
        // create connector
        $this->connector = new PDO(sprintf("sqlite:%s", $db_fpath));
        // create `data` table (if needed)
		if (!$db_exists)
			$this->_create_structure();
    }//__construct
	
	// Public functions

    public function is_writable($key) {
        // return wether the DB can be written to disk
        return !file_exists($this->get_db_fpath()) || is_writable($this->get_db_fpath());
    }//is_writable
	
	
	// Private functions
    
    private function get_db_fpath() {
        return self::_get_db_fpath($this->package, $this->database);
    }//_get_db_fpath
	
	private function _create_structure() {
 		$this->_execute_query(self::$TABLE_SQL, []);
	}
 
	private function _create_dir(){
		$file_parent_dir = dirname(self::_get_db_fpath($this->package, $this->database));
		if( file_exists($file_parent_dir) )
			return ['success' => true, 'data' => null];
		if( !@mkdir( $file_parent_dir, 0775, true ) ){
			return [
				'success' => false,
				'data' => sprintf(
					'The path `%s` cannot be created. Error: %s',
					$file_parent_dir,
					error_get_last()
				)
			];
		}
		return ['success' => true, 'data' => null];
	}//_create_dir
    
    // Public static functions
    
    public static function database_exists($package, $database) {
        return file_exists(self::_get_db_fpath($package, $database));
    }//database_exists

    public static function list_dbs($package) {
        // get list of all .db files
        $entry_wild = self::_get_db_fpath($package, '*');
        // cut the path and keep the key
        $keys = [];
        $files = glob($entry_wild);
        // cut the path and keep the key
        foreach ($files as $file) {
            $parts = explode('/', $file);
            if (count($parts) <= 0) {
                continue;
            }
            $key = $parts[count($parts) - 1];
            // add key to list of keys
            array_push($keys, $key);
        }
        // return list of keys
        return $keys;
	}//list_dbs

    public static function delete_db($package, $database) {
        $db_fpath = self::_get_db_fpath($package, $database);
        // remove db
		unlink($db_fpath);
        // ---
        return ['success' => true, 'data' => null];
    }//delete_db
    
    
    // Private static functions
    
    private static function _get_db_fpath($package, $database) {
        return sprintf(self::$dbs_location . "/%s.db", $GLOBALS['__USERDATA__DIR__'], $package, $database);
    }//_get_db_fpath
    
}//MySQLDatabase
?>

<?php

namespace system\classes;

use \system\classes\Utils;
use \system\classes\jsonDB\JsonDB as JsonDB;

class Database{

    // private attributes
    private $package;
    private $database;
    private $db_dir;

    // Constructor
    function __construct( $package, $database ){
        $this->package = $package;
        $this->database = $database;
        $this->db_dir = sprintf("%s%s/data/%s", $GLOBALS['__PACKAGES__DIR__'], $package, $database);
    }//__construct



    // Public functions

    public function read( $key ){
        $res = self::get_entry( $key );
        if( !$res['success'] )
            return $res;
        return ['success' => true, 'data' => $res['data']->asArray()];
    }//read

    public function get_entry( $key ){
        // check if key exists
        if( !self::key_exists($key) ){
            return ['success' => false, 'data' => sprintf("Entry with key '%s' not found!", $key)];
        }
        // load data
        $entry_file = self::key_to_db_file( $key );
        $jsondb = new JsonDB( $entry_file, '_data' );
        return ['success' => true, 'data' => $jsondb];
    }//get_entry

    public function write( $key, $data ){
        $entry_file = self::key_to_db_file( $key );
        // create json object
        $jsondb = new JsonDB( $entry_file );
        $jsondb->set('_data', $data);
        $jsondb->set('_metadata', []);
        // make sure that the path to the file exists
        $res = $jsondb->createDestinationIfNotExists();
        if( !$res['success'] )
            return $res;
        // write data to file
        return $jsondb->commit();
    }//write

    public function key_exists( $key ){
        $entry_file = self::key_to_db_file( $key );
        // check if file exists
        return file_exists($entry_file);
    }//key_exists

    public function list_keys(){
        // get list of all json files
        $entry_wild = sprintf('%s/*.json', $this->db_dir);
        $files = glob( $entry_wild );
        // cut the path and keep the key
        $keys = [];
		foreach ($files as $file) {
			$key = Utils::regex_extract_group($file, "/.*\/(.+).json/", 1);
			array_push( $keys, $key );
		}
        // return list of keys
        return $keys;
    }//list_keys

    public function size(){
        // get list of all json files
        $entry_wild = sprintf('%s/*.json', $this->db_dir);
        $files = glob( $entry_wild );
        // return count of list of keys
        return count( $files );
    }//size



    // Private functions

    private function key_to_db_file( $key ){
        $entry_filename = Utils::string_to_valid_filename( $key );
        $entry_file = sprintf('%s/%s.json', $this->db_dir, $entry_filename);
        return $entry_file;
    }//key_to_db_file

}//Database
?>

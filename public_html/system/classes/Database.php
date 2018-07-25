<?php

namespace system\classes;

use \system\classes\Utils;
use \system\classes\jsonDB\JsonDB as JsonDB;

class Database{

    // private attributes
    private $package;
    private $database;
    private $entry_regex;
    private $db_dir;

    // Constructor
    function __construct( $package, $database, $entry_regex=null ){
        $this->package = $package;
        $this->database = $database;
        $this->entry_regex = $entry_regex;
        $this->db_dir = sprintf("%s%s/data/private/databases/%s", $GLOBALS['__PACKAGES__DIR__'], $package, $database);
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
        $key = Utils::string_to_valid_filename( $key );
        if( !is_null($this->entry_regex) && !preg_match($this->entry_regex, $key) ){
            return [
                'success' => false,
                'data' => 'The given key does not match the given pattern. This instance of Database has a limited scope'
            ];
        }
        // get filename from key
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

    public function delete( $key ){
        $entry_file = self::key_to_db_file( $key );
        // delete if exists
        if( file_exists($entry_file) )
            return ['success' => @unlink($entry_file), 'data' => null];
        return ['success' => false, 'data' => 'The entry was not found'];
    }//delete

    public function key_exists( $key ){
        $key = Utils::string_to_valid_filename( $key );
        if( !is_null($this->entry_regex) && !preg_match($this->entry_regex, $key) )
            return false;
        // get filename from key
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
            // (optional) match the key against the given pattern
            if( !is_null($this->entry_regex) && !preg_match($this->entry_regex, $key) )
                continue;
            // add key to list of keys
			array_push( $keys, $key );
		}
        // return list of keys
        return $keys;
    }//list_keys

    public function size(){
        // return count of list of keys
        return count( self::list_keys() );
    }//size



    // Private functions

    private function key_to_db_file( $key ){
        $entry_filename = Utils::string_to_valid_filename( $key );
        $entry_file = sprintf('%s/%s.json', $this->db_dir, $entry_filename);
        return $entry_file;
    }//key_to_db_file

}//Database
?>

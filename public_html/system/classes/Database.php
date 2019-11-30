<?php

namespace system\classes;

use \system\classes\Utils;
use \system\classes\Core;
use \system\classes\jsonDB\JsonDB as JsonDB;

class Database{

  // private static attributes
  private static $dbs_location = "%sdatabases/%s/";

  // private attributes
  private $package;
  private $database;
  private $entry_regex;
  private $db_dir;

  // Constructor
  function __construct($package, $database, $entry_regex=null) {
    if (!Core::packageExists($package)) {
      Core::throwError(sprintf('Tried to create a Database for the package `%s` but the package does not exist', $package));
    }
    if (is_null($database) || strlen(trim($database)) <= 0) {
      Core::throwError(sprintf('Invalid database name "%s".', $database));
    }
    $this->package = $package;
    $this->database = $database;
    $this->entry_regex = $entry_regex;
    $this->db_dir = self::_get_db_dir($package, $database);
  }//__construct


  // Private static functions

  private static function _get_db_dir($package, $database) {
    return sprintf(self::$dbs_location."%s", $GLOBALS['__USERDATA__DIR__'], $package, $database);
  }//_get_db_dir

  // Public static functions

  public static function database_exists($package, $database) {
    $db_dir = self::_get_db_dir($package, $database);
    if (!Core::packageExists($package) || !file_exists($db_dir)) {
      return false;
    }
    return true;
  }//database_exists

  public static function list_dbs($package) {
    // get list of all json files
    $entry_wild = self::_get_db_dir($package, '*').'/';
    // cut the path and keep the key
    $keys = [];
    // get list of all json files
    $files = glob($entry_wild);
    // cut the path and keep the key
    foreach($files as $file) {
      $parts = explode('/', rtrim($file, '/'));
      if (count($parts) <= 0) {
        continue;
      }
      $key = $parts[count($parts)-1];
      // add key to list of keys
      array_push($keys, $key);
    }
    // return list of keys
    return $keys;
  }//list_dbs

  public static function delete_db($package, $database) {
    $db_dir = self::_get_db_dir($package, $database);
    // remove all keys
    array_map('unlink', glob("$db_dir/*.*"));
    // remove empty db
    rmdir($db_dir);
    // ---
    return ['success' => true, 'data' => null];
  }//delete_db


  // Public functions

  public function read($key) {
    $key = self::_safe_key($key);
    $res = self::get_entry($key);
    if (!$res['success']) {
      return $res;
    }
    return ['success' => true, 'data' => $res['data']->asArray()];
  }//read

  public function get_entry($key) {
    $key = self::_safe_key($key);
    // check if key exists
    if (!self::key_exists($key)) {
      return ['success' => false, 'data' => sprintf("Entry with key '%s' not found!", $key)];
    }
    // load data
    $entry_file = self::_key_to_db_file($key);
    $jsondb = new JsonDB($entry_file, '_data');
    return ['success' => true, 'data' => $jsondb];
  }//get_entry

  public function write($key, $data) {
    $key = self::_safe_key($key);
    if (!is_null($this->entry_regex) && !preg_match($this->entry_regex, $key)) {
      return [
        'success' => false,
        'data' => 'The given key does not match the given pattern. This instance of Database has a limited scope'
      ];
    }
    // get filename from key
    $entry_file = self::_key_to_db_file($key);
    // create json object
    $jsondb = new JsonDB($entry_file);
    $jsondb->set('_data', $data);
    $jsondb->set('_metadata', []);
    // make sure that the path to the file exists
    $res = $jsondb->createDestinationIfNotExists();
    if (!$res['success']) {
      return $res;
    }
    // write data to file
    return $jsondb->commit();
  }//write

  public function delete($key) {
    $key = self::_safe_key($key);
    $entry_file = self::_key_to_db_file($key);
    // delete if exists
    if (file_exists($entry_file)) {
      return ['success' => @unlink($entry_file), 'data' => null];
    }
    return ['success' => false, 'data' => 'The entry was not found'];
  }//delete

  public function key_exists($key) {
    $key = self::_safe_key($key);
    if (!is_null($this->entry_regex) && !preg_match($this->entry_regex, $key)) {
      return false;
    }
    // get filename from key
    $entry_file = self::_key_to_db_file($key);
    // check if file exists
    return file_exists($entry_file);
  }//key_exists

  public function list_keys() {
    // get list of all json files
    $entry_wild = sprintf('%s/*.json', $this->db_dir);
    $files = glob($entry_wild);
    // cut the path and keep the key
    $keys = [];
    foreach($files as $file) {
      $key = Utils::regex_extract_group($file, "/.*\/(.+).json/", 1);
      // (optional) match the key against the given pattern
      if (!is_null($this->entry_regex) && !preg_match($this->entry_regex, $key)) {
        continue;
      }
      // add key to list of keys
      array_push($keys, $key);
    }
    // return list of keys
    return $keys;
  }//list_keys

  public function size() {
    // return count of list of keys
    return count(self::list_keys());
  }//size

  public function key_size($key) {
    // return size of key file in number of bytes
    return filesize(self::_key_to_db_file($key));
  }//key_size

  public function is_writable($key) {
    // return wether the key can be written to disk
    $entry_file = self::_key_to_db_file($key);
    return !file_exists($entry_file) || is_writable($entry_file);
  }//is_writable



  // Private functions

  private function _safe_key($key) {
    return Utils::string_to_valid_filename($key);
  }//_safe_key

  private function _key_to_db_file($key) {
    $entry_filename = self::_safe_key($key);
    $entry_file = sprintf('%s/%s.json', $this->db_dir, $entry_filename);
    return $entry_file;
  }//_key_to_db_file

  private function _key_list_to_regex($key) {
    $key_regex = '';
    for($i = 0; $i < count($key); $i++) {
      $k = $key[$i];
      if ($i > 0) {
        $key_regex .= '\.';
      }
      if (is_null($k)) {
        $key_regex .= '([A-Za-z0-9_]+)';
      }else{
        $key_regex .= Utils::string_to_valid_filename($key);
      }
    }
    // compose regex
    return sprintf("/^%s$/", $key_regex);
  }//_key_list_to_regex

}//Database
?>

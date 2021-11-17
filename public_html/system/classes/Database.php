<?php

namespace system\classes;

use exceptions\DatabaseKeyNotFoundException;
use exceptions\GenericException;
use exceptions\IOException;
use JetBrains\PhpStorm\Pure;
use \system\classes\jsonDB\JsonDB;


class Database {
    
    // private static attributes
    private static string $dbs_location = "%sdatabases/%s/";
    
    // private attributes
    private string $package;
    private string $database;
    private string|null $entry_regex;
    private string $db_dir;
    
    // Constructor
    
    /**
     * Database constructor.
     *
     * @param string $package       Package the database belongs to.
     * @param string $database      Database name.
     * @param string|null $entry_regex
     */
    function __construct(string $package, string $database, string $entry_regex = null) {
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
    
    // Public static functions
    
    /** Check whether a database exists.
     *
     * @param string $package       Package the database belongs to.
     * @param string $database      Database name.
     * @return bool
     */
    public static function database_exists(string $package, string $database): bool {
        $db_dir = self::_get_db_dir($package, $database);
        if (!Core::packageExists($package) || !file_exists($db_dir)) {
            return false;
        }
        return true;
    }//database_exists
    
    /** Lists all databases for a package.
     *
     * @param string $package   package to list databases for.
     * @return array
     */
    public static function list_dbs(string $package): array {
        // get list of all json files
        $entry_wild = self::_get_db_dir($package, '*') . '/';
        // cut the path and keep the key
        $keys = [];
        // get list of all json files
        $files = glob($entry_wild);
        // cut the path and keep the key
        foreach ($files as $file) {
            $parts = explode('/', rtrim($file, '/'));
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
    
    /** Deletes a database.
     *
     * @param string $package       Package the database belongs to.
     * @param string $database      Database name.
     * @return bool
     */
    public static function delete_db(string $package, string $database): bool {
        $db_dir = self::_get_db_dir($package, $database);
        // remove all keys
        array_map('unlink', glob("$db_dir/*.*"));
        // remove empty db
        rmdir($db_dir);
        // ---
        return true;
    }//delete_db
    
    
    // Public functions
    
    /** Read entry from the database as array.
     *
     * @param string $key       Key of the entry to read.
     * @return array            Database entry.
     * @throws DatabaseKeyNotFoundException
     */
    public function read(string $key): array {
        $key = self::_safe_key($key);
        return self::get_entry($key)->asArray();
    }//read
    
    /** Read entry from the database as editable JsonDB.
     *
     * @param string $key       Key of the entry to read.
     * @return JsonDB           Database entry.
     * @throws DatabaseKeyNotFoundException
     */
    public function get_entry(string $key): JsonDB {
        $key = self::_safe_key($key);
        // check if key exists
        if (!self::key_exists($key)) {
            throw new DatabaseKeyNotFoundException($this->package, $this->database, $key);
        }
        // load data
        $entry_file = self::_key_to_db_file($key);
        return new JsonDB($entry_file, '_data');
    }//get_entry
    
    /** Write a (key, value) pair to the database.
     *
     * @param string $key       The key.
     * @param mixed $data       The value.
     * @return boolean
     * @throws GenericException
     * @throws IOException
     */
    public function write(string $key, mixed $data): bool {
        $key = self::_safe_key($key);
        if (!is_null($this->entry_regex) && !preg_match($this->entry_regex, $key)) {
            throw new GenericException("The given key does not match the given pattern.
                                        This instance of Database has a limited scope");
        }
        // get filename from key
        $entry_file = self::_key_to_db_file($key);
        // create json object
        $jsondb = new JsonDB($entry_file);
        $jsondb->set('_data', $data);
        $jsondb->set('_metadata', []);
        // make sure that the path to the file exists
        $jsondb->createDestinationIfNotExists();
        // write data to file
        return $jsondb->commit();
    }//write
    
    /** Deletes a key from the database.
     *
     * @param string $key       Key to delete.
     * @return bool
     * @throws DatabaseKeyNotFoundException
     */
    public function delete(string $key): bool {
        $key = self::_safe_key($key);
        $entry_file = self::_key_to_db_file($key);
        // delete if exists
        if (file_exists($entry_file)) {
            return @unlink($entry_file);
        }
        throw new DatabaseKeyNotFoundException($this->package, $this->database, $key);
    }//delete
    
    /** Checks whether a key exists in the database.
     *
     * @param string $key       The key to check for.
     * @return bool
     */
    public function key_exists(string $key): bool {
        $key = self::_safe_key($key);
        if (!is_null($this->entry_regex) && !preg_match($this->entry_regex, $key)) {
            return false;
        }
        // get filename from key
        $entry_file = self::_key_to_db_file($key);
        // check if file exists
        return file_exists($entry_file);
    }//key_exists
    
    /** Lists keys inside the database.
     *
     * @return array
     */
    public function list_keys(): array {
        // get list of all json files
        $entry_wild = sprintf('%s/*.json', $this->db_dir);
        $files = glob($entry_wild);
        // cut the path and keep the key
        $keys = [];
        foreach ($files as $file) {
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
    
    /** Returns the number of (key, value) pairs in the database.
     *
     * @return int
     */
    public function size(): int {
        // return count of list of keys
        return count(self::list_keys());
    }//size
    
    /** Returns the length in number of bytes of the value corresponding to the given key.
     *
     * @param string $key       The key to check the size for.
     * @return int
     */
    public function key_size(string $key): int {
        // return size of key file in number of bytes
        return filesize(self::_key_to_db_file($key));
    }//key_size
    
    /** Checks whether the given key is writable.
     *
     * @param string $key       The key to check for.
     * @return bool
     */
    public function is_writable(string $key): bool {
        // return wether the key can be written to disk
        $entry_file = self::_key_to_db_file($key);
        return !file_exists($entry_file) || is_writable($entry_file);
    }//is_writable
    
    
    // Private functions
    
    /** Sanitizes a key.
     *
     * @param string $key       The key to sanitizie.
     * @return string
     */
    private function _safe_key(string $key): string {
        return Utils::string_to_valid_filename($key);
    }//_safe_key
    
    /** Returns the database file a key corresponds to.
     *
     * @param string $key       The key.
     * @return string
     */
    private function _key_to_db_file(string $key): string {
        $entry_filename = self::_safe_key($key);
        return sprintf('%s/%s.json', $this->db_dir, $entry_filename);
    }//_key_to_db_file
    
    
    // Private static functions
    
    /** Get path to database directory.
     *
     * @param string $package       Package the database belongs to.
     * @param string $database      Database name.
     * @return string
     */
    #[Pure] private static function _get_db_dir(string $package, string $database): string {
        return sprintf(self::$dbs_location . "%s", $GLOBALS['__USERDATA__DIR__'], $package, $database);
    }//_get_db_dir
    
}//Database

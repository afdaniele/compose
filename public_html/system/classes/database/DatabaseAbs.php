<?php

namespace system\classes\database;


use system\classes\Core;

abstract class DatabaseAbs {
    
    // private attributes
    protected $package;
    protected $database;
    protected $entry_regex;
    protected $db_dir;
    
    // Constructor
    function __construct($package, $database, $entry_regex = null) {
        if (!Core::packageExists($package)) {
            Core::throwError(sprintf('Tried to create a Database for the package `%s` but the
            package does not exist', $package));
        }
        if (is_null($database) || strlen(trim($database)) <= 0) {
            Core::throwError(sprintf('Invalid database name "%s".', $database));
        }
        $this->package = $package;
        $this->database = $database;
        $this->entry_regex = $entry_regex;
    }//__construct
    
    // Public static functions
    
    public static abstract function database_exists($package, $database);
    
    public static abstract function list_dbs($package);
    
    public static abstract function delete_db($package, $database);
    
    
    // Public functions
    
    public abstract function read($key);
    
    public abstract function get_entry($key);
    
    public abstract function write($key, $data);
    
    public abstract function delete($key);
    
    public abstract function key_exists($key);
    
    public abstract function list_keys();
    
    public abstract function size();
    
    public abstract function key_size($key);
    
    public abstract function is_writable($key);
    
    // Protected functions
    
    protected function _matches_key_regex($key) {
        return is_null($this->entry_regex) || preg_match($this->entry_regex, $key);
    }
    
}

?>

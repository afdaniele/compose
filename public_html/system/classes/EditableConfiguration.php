<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes;

require_once __DIR__ . '/Database.php';


use Exception;
use exceptions\ConfigurationException;
use exceptions\DatabaseContentException;
use exceptions\DatabaseKeyNotFoundException;
use exceptions\FileNotFoundException;
use exceptions\GenericException;
use exceptions\InvalidSchemaException;
use exceptions\IOException;
use exceptions\SchemaViolationException;


class EditableConfiguration {
    
    private string $package;
    private array $configuration = [];
    private Database $db;
    private Schema $schema;
    private bool $is_configurable = false;
    
    private static string $database_name = '__configuration__';
    private static string $configuration_key = 'content';
    
    
    // constructor
    
    /**
     * EditableConfiguration constructor.
     * @param $package
     * @throws FileNotFoundException
     * @throws GenericException
     * @throws DatabaseContentException
     * @throws InvalidSchemaException
     *
     */
    public function __construct(string $package) {
        $this->package = $package;
        $schema_file = sprintf("%s/../packages/%s/configuration/schema.json", __DIR__, $package);
        if (!file_exists($schema_file)) {
            $schema_file = sprintf("%s%s/configuration/schema.json", $GLOBALS['__USERDATA__PACKAGES__DIR__'], $package);
        }
        // ---
        // load configuration schema (if present)
        $schema_array = [];
        if (file_exists($schema_file)) {
            try {
                $schema_array = json_decode(file_get_contents($schema_file), true);
            } catch (Exception $e) {
                $msg = "The configuration schema for the package '$package' is corrupted.
                    The error reads:<br/> {$e->getMessage()}";
                throw new GenericException($msg);
            }
        }
        // make a Schema object
        $this->schema = new Schema($schema_array);
        // if the schema defines no parameters, then the package is simply not configurable
        if (count($schema_array) <= 0) {
            $this->is_configurable = false;
            return;
        }
        $this->is_configurable = true;
        // load the default values
        $this->configuration = [];
        // try to load the custom settings from the database if it exists
        $this->db = new Database($package, self::$database_name);
        if (Database::database_exists($package, self::$database_name)) {
            try {
                $cfg = $this->db->read(self::$configuration_key);
            } catch (DatabaseKeyNotFoundException) {
                throw new DatabaseContentException("Configuration database for package '$package'
                is in a bad state. Missing 'content' entry.");
            }
            $this->configuration = $cfg;
        }
    }//__construct
    
    /** Access underlying schema.
     *
     * @return Schema
     */
    public function getSchema(): Schema {
        return $this->schema;
    }//getSchema
    
    /** Access underlying schema (returned as array).
     *
     * @return array
     */
    public function getSchemaAsArray(): array {
        return $this->schema->asArray();
    }//getSchema
    
    /** Return configuration as array.
     *
     * @param bool $use_defaults        Fill missing values with defaults.
     * @return array
     */
    public function asArray(bool $use_defaults = false): array {
        $cfg = $this->configuration;
        $dcfg = $this->getDefaults();
        if ($use_defaults) {
            $existing_paths = Utils::arrayPaths($cfg);
            $default_paths = Utils::arrayPaths($dcfg);
            // add to $cfg those paths that exist in $dcfg but not in $cfg
            $missing_paths = array_diff($default_paths, $existing_paths);
            foreach ($missing_paths as $path) {
                $val = Utils::cursorTo($dcfg, $path);
                if (is_null($val)) {
                    continue;
                }
                $sel = &Utils::cursorTo($cfg, $path, true);
                $sel = $val;
            }
        }
        return $cfg;
    }//asArray
    
    /** Get default values as defined in the schema.
     *
     * @return array
     */
    public function getDefaults(): array {
        return $this->schema->defaults();
    }//getDefaults
    
    /** Get the value corresponding to the given key.
     *
     * @param string $key               Key to fetch the value for.
     * @param null $default             Default value to return if none is available.
     * @return mixed
     * @throws SchemaViolationException
     */
    public function get(string $key, $default = null): mixed {
        $path = explode('/', $key);
        $cfg = $this->configuration;
        $dcfg = $this->getDefaults();
        // ---
        if (!Utils::pathExists($dcfg, $path)) {
            throw new SchemaViolationException("Unknown parameter '{$key}'' for the package '{$this->package}'");
        }
        $default_cursor = Utils::cursorTo($dcfg, $path);
        // ---
        $cfg_cursor = Utils::cursorTo($cfg, $path);
        if (!is_null($cfg_cursor) && (is_array($cfg_cursor) || strlen($cfg_cursor) > 0)) {
            return $cfg_cursor;
        }
        if (is_null($default) && !is_null($default_cursor)) {
            return $default_cursor;
        }
        return $default;
    }//get
    
    /** Set the value to a key;
     *
     * @param string $key       the key.
     * @param mixed $val        the value.
     * @return bool
     * @throws ConfigurationException
     */
    public function set(string $key, mixed $val): bool {
        $path = explode('/', $key);
        // make fake input
        $input = [];
        $key_cursor = &Utils::cursorTo($input, $path, true);
        $key_cursor = $val;
        // check against the schema
        try {
            $this->schema->validate($input);
        } catch (SchemaViolationException $e) {
            $msg = "Unknown parameter '{$key}' for the package '{$this->package}'. Error: {$e->getMessage()}";
            throw new ConfigurationException($msg);
        }
        $cfg_cursor = &Utils::cursorTo($this->configuration, $path, true);
        $cfg_cursor = $val;
        return true;
    }//set
    
    /** Commit changes to underlying database.
     * @return bool     whether changes were committed successfully.
     *
     * @throws GenericException
     * @throws IOException
     */
    public function commit(): bool {
        if (!$this->is_configurable()) {
            return false;
        }
        return $this->db->write(self::$configuration_key, $this->configuration);
    }//commit
    
    /** Returns whether this instance is writable.
     * @return bool     whether this instance is writable.
     */
    public function is_writable(): bool {
        if (!$this->is_configurable()) {
            return false;
        }
        return $this->db->is_writable(self::$configuration_key);
    }//is_writable
    
    /** Returns whether this instance is configurable.
     * @return bool     whether this instance is configurable.
     */
    public function is_configurable(): bool {
        return $this->is_configurable;
    }//is_configurable
    
}//EditableConfiguration

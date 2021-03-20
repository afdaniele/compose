<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes;

require_once __DIR__ . '/Database.php';


class EditableConfiguration {

    private $package_name = null;
    private $configuration = [];
    private $default_configuration = [];
    private $db = null;
    private $schema = null;
    private $is_configurable = false;
    private $error_state = null;
    private $database_name = '__configuration__';
    private $configuration_key = 'content';


    // constructor
    public function __construct($package_name) {
        $this->package_name = $package_name;
        $schema_file = sprintf("%s/../packages/%s/configuration/schema.json", __DIR__, $package_name);
        if (!file_exists($schema_file)) {
            $schema_file = sprintf("%s%s/configuration/schema.json", $GLOBALS['__USERDATA__PACKAGES__DIR__'], $package_name);
        }
        // ---
        // load configuration schema. This file must be always present.
        if (!file_exists($schema_file)) {
            $this->error_state = sprintf('The configuration schema for the package "%s" does not exist or is corrupted.', $package_name);
            return;
        }
        try {
            $this->schema = ComposeSchema::from_schema(json_decode(file_get_contents($schema_file), true));
        } catch (\Exception $e) {
            $this->error_state = sprintf(
                'The configuration schema for the package "%s" is corrupted. The error reads:<br/>%s',
                $package_name, $e->getMessage()
            );
            return;
        }
        // if the schema defines no parameters, then the package is simply not configurable
        if ($this->schema->is_empty()) {
            $this->is_configurable = false;
            return;
        }
        $this->is_configurable = true;
        // load the default values
        $this->configuration = [];
        $this->default_configuration = $this->schema->defaults();
        // try to load the custom settings from the database if it exists
        $this->db = new Database($package_name, $this->database_name);
        if (Database::database_exists($package_name, $this->database_name)) {
            $res = $this->db->read($this->configuration_key);
            if (!$res['success']) {
                $this->error_state = $res['data'];
                return;
            }
            $this->configuration = $res['data'];
        }
    }//__construct


    public function sanityCheck() {
        if (!is_null($this->error_state)) {
            return ['success' => false, 'data' => $this->error_state];
        }
        return ['success' => true, 'data' => null];
    }//sanityCheck


    public function getSchema() {
        return $this->schema;
    }//getSchema


    public function asArray(bool $use_defaults = false) {
        $cfg = $this->configuration;
        $dcfg = $this->default_configuration;
        if ($use_defaults) {
            $existing_paths = Utils::arrayPaths($cfg);
            $default_paths = Utils::arrayPaths($dcfg);
            // add to $cfg those paths that exist in $dcfg but not in $cfg
            $missing_paths = array_diff($default_paths, $existing_paths);
            foreach ($missing_paths as $path) {
                $val = Utils::cursorTo($dcfg, $path);
                if (is_null($val))
                    continue;
                $sel = &Utils::cursorTo($cfg, $path, true);
                $sel = $val;
            }
        }
        return $cfg;
    }//asArray


    public function getDefaults(): array {
        return $this->default_configuration;
    }//getDefaults


    public function get($key, $default = null) {
        $path = explode('/', $key);
        if (!Utils::pathExists($this->default_configuration, $path)) {
            return ['success' => false, 'data' => sprintf('Unknown parameter "%s" for the package "%s"', $key, $this->package_name)];
        }
        $default_cursor = Utils::cursorTo($this->default_configuration, $path);
        // ---
        $cfg_cursor = Utils::cursorTo($this->configuration, $path);
        if (!is_null($cfg_cursor) && (is_array($cfg_cursor) || strlen($cfg_cursor) > 0)) {
            return ['success' => true, 'data' => $cfg_cursor];
        }
        if (is_null($default) && !is_null($default_cursor)) {
            return ['success' => true, 'data' => $default_cursor];
        }
        return ['success' => true, 'data' => $default];
    }//get


    public function set($key, $val) {
        $path = explode('/', $key);
        if (!Utils::pathExists($this->default_configuration, $path)) {
            return ['success' => false, 'data' => sprintf('Unknown parameter "%s" for the package "%s"', $key, $this->package_name)];
        }
        $cfg_cursor = &Utils::cursorTo($this->configuration, $path, true);
        $cfg_cursor = $val;
        return ['success' => true, 'data' => null];
    }//set


    public function commit() {
        if (!$this->is_configurable()) {
            return ['success' => false, 'data' => 'The package is not configurable'];
        }
        return $this->db->write($this->configuration_key, $this->configuration);
    }//commit


    public function is_writable() {
        if (!$this->is_configurable()) {
            return false;
        }
        return $this->db->is_writable($this->configuration_key);
    }//is_writable


    public function is_configurable() {
        return $this->is_configurable;
    }//is_configurable

}//EditableConfiguration

?>

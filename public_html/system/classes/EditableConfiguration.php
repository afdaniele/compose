<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes;

require_once __DIR__ . '/Database.php';

use system\classes\Database;


class EditableConfiguration {

    private $package_name = null;
    private $configuration = [];
    private $default_configuration = [];
    private $configuration_db = null;
    private $configuration_details = null;
    private $is_configurable = false;
    private $error_state = null;
    private $database_name = '__configuration__';
    private $configuration_key = 'content';


    // constructor
    public function __construct($package_name) {
        $this->package_name = $package_name;
        $configuration_details_file = sprintf("%s/../packages/%s/configuration/metadata.json", __DIR__, $package_name);
        if (!file_exists($configuration_details_file)) {
            $configuration_details_file = sprintf("%s%s/configuration/metadata.json", $GLOBALS['__USERDATA__PACKAGES__DIR__'], $package_name);
        }
        // ---
        // load configuration metadata. This file must be always present.
        if (!file_exists($configuration_details_file)) {
            $this->error_state = sprintf('The configuration metadata for the package "%s" does not exist or is corrupted.', $package_name);
            return;
        }
        $this->configuration_details = json_decode(file_get_contents($configuration_details_file), true);
        if (is_null($this->configuration_details)) {
            $this->error_state = sprintf('The configuration metadata for the package "%s" is corrupted.', $package_name);
            return;
        }
        // if the metadata defines no parameters, then the package is simply not configurable
        if (count($this->configuration_details['configuration_content']) <= 0) {
            $this->is_configurable = false;
            return;
        }
        $this->is_configurable = true;
        // load the default values
        $this->configuration = [];
        $this->default_configuration = [];
        foreach ($this->configuration_details['configuration_content'] as $key => $value) {
            $this->default_configuration[$key] = $value['default'];
        }
        // try to load the custom settings from the database if it exists
        $this->configuration_db = new Database($package_name, $this->database_name);
        if (Database::database_exists($package_name, $this->database_name)) {
            $res = $this->configuration_db->read($this->configuration_key);
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


    public function getMetadata() {
        return $this->configuration_details;
    }//getMetadata


    public function asArray() {
        return $this->configuration;
    }//asArray


    public function get($key, $default = null) {
        if (!array_key_exists($key, $this->default_configuration)) {
            return ['success' => false, 'data' => sprintf('Parameter "%s" unknown', $key)];
        }
        if (array_key_exists($key, $this->configuration) && strlen($this->configuration[$key]) > 0) {
            return ['success' => true, 'data' => $this->configuration[$key]];
        }
        if (is_null($default) && array_key_exists($key, $this->default_configuration)) {
            return ['success' => true, 'data' => $this->default_configuration[$key]];
        }
        return ['success' => true, 'data' => $default];
    }//get


    public function set($key, $val) {
        if (!array_key_exists($key, $this->default_configuration)) {
            return ['success' => false, 'data' => sprintf('Unknown parameter "%s" for the package "%s"', $key, $this->package_name)];
        }
        $this->configuration[$key] = $val;
        return ['success' => true, 'data' => null];
    }//set


    public function commit() {
        if (!$this->is_configurable()) {
            return ['success' => false, 'data' => 'The package is not configurable'];
        }
        return $this->configuration_db->write($this->configuration_key, $this->configuration);
    }//commit


    public function is_writable() {
        if (!$this->is_configurable()) {
            return false;
        }
        return $this->configuration_db->is_writable($this->configuration_key);
    }//is_writable


    public function is_configurable($package_name) {
        return $this->is_configurable;
    }//is_configurable


}//EditableConfiguration

?>

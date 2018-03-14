<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, February 4th 2018


namespace system\classes;

require_once __DIR__.'/jsonDB/JsonDB.php';
use system\classes\jsonDB\JsonDB;


class EditableConfiguration {

	// JSONdb instance
	private $jsondb = null;
	private $package_name = null;
	private $jsondb_config_file = null;
	private $configuration_details = null;
	private $is_configurable = false;
	private $error_state = null;


	// constructor
	public function __construct( $package_name ){
		$this->package_name = $package_name;
		$jsondb_config_file = sprintf("%s/../packages/%s/configuration/configuration.json", __DIR__, $package_name);
		$configuration_details_file = sprintf("%s/../packages/%s/configuration/metadata.json", __DIR__, $package_name);
		if( !file_exists($jsondb_config_file) && !file_exists($configuration_details_file) ){
			$this->is_configurable = false;
			return;
		}
		// load configuration metadata. This file must be always present.
		if( !file_exists($configuration_details_file) ){
			$this->error_state = sprintf('The configuration metadata for the package "%s" does not exist or is corrupted.', $package_name);
			return;
		}
		$this->configuration_details = json_decode( file_get_contents($configuration_details_file), true );
		if( is_null($this->configuration_details) ){
			$this->error_state = sprintf('The configuration metadata for the package "%s" is corrupted.', $package_name);
			return;
		}
		// if the metadata defines no parameters, then the package is simply not configurable
		if( count($this->configuration_details['configuration_content']) <= 0 ){
			$this->is_configurable = false;
			return;
		}
		$this->is_configurable = true;
		// try to load the custom settings from 'configuration.json' if it exists.
		$this->jsondb_config_file = $jsondb_config_file;
		$this->jsondb = new JsonDB( $this->jsondb_config_file );
		// If it doesn't exist, create a new one and use the default values from the metadata file
		if( !file_exists($jsondb_config_file) ){
			// create new file
			foreach ($this->configuration_details['configuration_content'] as $key => $value) {
				$this->jsondb->set( $key, $value['default'] );
			}
			// (try to) write the file to disk
			$res = $this->jsondb->commit();
			if( !$res['success'] ){
				$this->error_state = $res['data'];
				return;
			}
		}
	}//__construct


	public function sanityCheck(){
		if( !is_null($this->error_state) ){
			return array( 'success' => false, 'data' => $this->error_state );
		}
		return array( 'success' => true, 'data' => null );
	}//sanityCheck


	public function getMetadata(){
		return $this->configuration_details;
	}//getMetadata


	public function asArray(){
		return $this->jsondb->asArray();
	}//asArray


	public function get( $key, $default=null ){
		if( $this->jsondb != null ){
			if( $this->jsondb->contains($key) ){
				$val = $this->jsondb->get($key, $default);
				return array('success' => true, 'data' => $val);
			}elseif( array_key_exists($key, $this->configuration_details['configuration_content']) ){
				return array('success' => true, 'data' => '');
			}else{
				return array('success' => false, 'data' => sprintf('Parameter "%s" unknown', $key));
			}
		}
		return array('success' => false, 'data' => 'An error occurred while reading the configurations. Please, retry!');
	}//get


	public function set( $key, $val ){
		if( $this->jsondb != null ){
			if( array_key_exists($key, $this->configuration_details['configuration_content']) ){
				$this->jsondb->set($key, $val);
				return array('success' => true );
			}
			return array('success' => false, 'data' => sprintf('Unknown parameter "%s" for the package "%s"', $key, $this->package_name));
		}
		return array('success' => false, 'data' => 'An error occurred while writing the configurations. Please, retry!');
	}//set


	public function commit(){
		if( $this->jsondb != null ){
			$res = $this->jsondb->commit();
			return $res;
		}
		return array('success' => false, 'data' => 'An error occurred while writing the configurations. Please, retry!');
	}//commit


	public function is_writable(){
		return is_writable( $this->jsondb_config_file );
	}//is_writable


	public function is_configurable( $package_name ){
		return $this->is_configurable;
	}//is_configurable


}//EditableConfiguration

?>

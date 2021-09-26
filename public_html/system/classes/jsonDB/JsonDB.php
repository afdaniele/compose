<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 3/13/15
 * Time: 1:22 AM
 */

namespace system\classes\jsonDB;

use Exception;
use system\classes\database\DatabaseEntryAbs;


class JsonDB extends DatabaseEntryAbs {
    
    private $file;
    private $json;
    private $mask_key;
    
    public function __construct($filename, $mask_key = null) {
        $this->file = $filename;
        $this->mask_key = $mask_key;
        // load the file content
        $file_content = file_get_contents($filename);
        if ($file_content === false) {
            // the file does not exist
            $this->json = array();
        } else {
            $this->json = json_decode($file_content, true);
            if (!is_null($this->mask_key)) {
                $this->json = $this->json[$this->mask_key];
            }
        }
    }//constructor
    
    public function contains($key) {
        return (isset($this->json[$key]));
    }//contains
    
    public function get($key, $default = null) {
        return ((isset($this->json[$key])) ? $this->json[$key] : $default);
    }//get
    
    public function set($key, $val) {
        $this->json[$key] = $val;
    }//set
    
    public function commit() {
        $is_present = file_exists($this->file);
        $is_writable = is_writable($this->file);
        if ($is_present === true && $is_writable === false) {
            return [
                'success' => false,
                'data' => 'The file `' . $this->file . '` is not writable.'
            ];
        }
        try {
            if (!is_null($this->mask_key)) {
                $orig_file_content = [];
                if ($is_present) {
                    $orig_file_content = json_decode(file_get_contents($this->file), true);
                }
                $orig_file_content[$this->mask_key] = $this->json;
                $file_content = prettyPrint(json_encode($orig_file_content));
            } else {
                $file_content = prettyPrint(json_encode($this->json));
            }
            $res = file_put_contents($this->file, $file_content);
            if ($res === false) {
                $error = error_get_last();
                return [
                    'success' => false,
                    'data' => 'An error occurred while writing the file.' .
                              'The server reports: (' . $error['message'] . ')'
                ];
            } else {
                if (!$is_present) {
                    chmod($this->file, 0664);
                }
                return ['success' => true, 'data' => null];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => 'An error occurred while writing the file. The server reports: (' . $e->getMessage() . ')'
            ];
        }
    }//commit
    
    public function asArray() {
        return $this->json;
    }//asArray
    
    public function createDestinationIfNotExists() {
        $file_parent_dir = dirname($this->file);
        if (file_exists($file_parent_dir)) {
            return ['success' => true, 'data' => null];
        }
        if (!@mkdir($file_parent_dir, 0775, true)) {
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
    }//createFileIfNotExists
    
} //JsonDB

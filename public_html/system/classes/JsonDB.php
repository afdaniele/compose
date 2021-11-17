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
use exceptions\GenericException;
use exceptions\IOException;

class JsonDB {
    
    private $file;
    private $json;
    private $mask_key;
    
    /**
     * JsonDB constructor.
     * @param string $filename
     * @param null $mask_key
     */
    public function __construct(string $filename, $mask_key = null) {
        $this->file = $filename;
        $this->mask_key = $mask_key;
        // load the file content
        $file_content = false;
        if (file_exists($filename)) {
            $file_content = file_get_contents($filename);
        }
        if ($file_content === false) {
            // the file does not exist
            $this->json = [];
        } else {
            $this->json = json_decode($file_content, true);
            if (!is_null($this->mask_key)) {
                $this->json = $this->json[$this->mask_key];
            }
        }
    }//constructor
    
    /** TODO
     * @param $key
     * @return bool
     */
    public function contains(string $key): bool {
        return isset($this->json[$key]);
    }//contains
    
    /** TODO
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get(string $key, $default = null) {
        return ((isset($this->json[$key])) ? $this->json[$key] : $default);
    }//get
    
    /** TODO
     * @param $key
     * @param $val
     */
    public function set(string $key, $val) {
        $this->json[$key] = $val;
    }//set
    
    /** TODO
     * @return bool
     * @throws IOException
     */
    public function commit(): bool {
        $is_present = file_exists($this->file);
        $is_writable = is_writable($this->file);
        if ($is_present === true && $is_writable === false) {
            throw new IOException("The file '$this->file' is not writable.");
        }
        try {
            if (!is_null($this->mask_key)) {
                $orig_file_content = [];
                if ($is_present) {
                    $orig_file_content = json_decode(file_get_contents($this->file), true);
                }
                $orig_file_content[$this->mask_key] = $this->json;
                $file_content = self::prettyPrint(json_encode($orig_file_content));
            } else {
                $file_content = self::prettyPrint(json_encode($this->json));
            }
            $res = file_put_contents($this->file, $file_content);
            if ($res === false) {
                $error = error_get_last();
                $msg = $error['message'];
                $msg = "An error occurred while writing the file. The server reports: $msg";
                throw new IOException($msg);
            } else {
                if (!$is_present) {
                    chmod($this->file, 0664);
                }
                return true;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            $msg = "An error occurred while writing the file. The server reports: $error";
            throw new IOException($msg);
        }
    }//commit
    
    /** TODO
     * @return array
     */
    public function asArray(): array {
        return $this->json;
    }//asArray
    
    /** TODO
     * @return bool
     * @throws GenericException
     */
    public function createDestinationIfNotExists(): bool {
        $file_parent_dir = dirname($this->file);
        if (file_exists($file_parent_dir)) {
            return true;
        }
        if (!@mkdir($file_parent_dir, 0775, true)) {
            $msg = sprintf(
                'The path `%s` cannot be created. Error: %s',
                $file_parent_dir,
                error_get_last()
            );
            throw new GenericException($msg);
        }
        return true;
    }//createDestinationIfNotExists
    
    
    // utility
    
    private function prettyPrint(string $json): string {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = null;
        $json_length = strlen($json);
        
        for ($i = 0; $i < $json_length; $i++) {
            $char = $json[$i];
            $new_line_level = null;
            $post = "";
            if ($ends_line_level !== null) {
                $new_line_level = $ends_line_level;
                $ends_line_level = null;
            }
            if ($in_escape) {
                $in_escape = false;
            } else {
                if ($char === '"') {
                    $in_quotes = !$in_quotes;
                } else {
                    if (!$in_quotes) {
                        switch ($char) {
                            case '}':
                            case ']':
                                $level--;
                                $ends_line_level = null;
                                $new_line_level = $level;
                                break;
                            
                            case '{':
                            case '[':
                                $level++;
                                break;
                            
                            case ',':
                                $ends_line_level = $level;
                                break;
                            
                            case ':':
                                $post = " ";
                                break;
                            
                            case " ":
                            case "\t":
                            case "\n":
                            case "\r":
                                $char = "";
                                $ends_line_level = $new_line_level;
                                $new_line_level = null;
                                break;
                        }
                    } else {
                        if ($char === '\\') {
                            $in_escape = true;
                        }
                    }
                }
            }
            if ($new_line_level !== null) {
                $result .= "\n" . str_repeat("\t", $new_line_level);
            }
            $result .= $char . $post;
        }
        return $result;
    }//prettyPrint
    
} //JsonDB

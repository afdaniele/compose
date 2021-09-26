<?php

namespace system\classes\database;

class SQLEntry extends DatabaseEntryAbs {
    
    private $db;
    private $key;
    private $content;
    
    public function __construct($db, $key) {
        $this->db = $db;
        $this->key = $key;
        $this->content = $this->asArray();
    }
    
    public function contains($key) {
        return array_key_exists($key, $this->content);
    }//contains
    
    public function get($key, $default = null) {
        if ($this->contains($key)) {
            return $this->content[$key];
        }
        return $default;
    }//get
    
    public function set($key, $val) {
        $this->content[$key] = $val;
    }//set
    
    public function commit() {
        return $this->db->write($this->key, $this->content);
    }//commit
    
    public function asArray() {
        $res = $this->db->read($this->key);
        if ($res['success']) {
            return $res['data'];
        }
        return [];
    }//asArray
    
}
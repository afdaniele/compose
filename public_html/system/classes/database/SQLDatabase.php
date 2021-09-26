<?php

namespace system\classes\database;

use PDO;

require_once __DIR__ . '/DatabaseAbs.php';


abstract class SQLDatabase extends DatabaseAbs {
    
    // private attributes
    protected $connector;
    
    // Protected functions
    
    protected function _execute_query($query, $args = [], $mode=PDO::FETCH_ASSOC) {
        /* Select queries return a resultset */
        $stmt = $this->connector->prepare($query);
        $stmt->execute($args);
        return $stmt->fetch($mode);
    }//_execute_query
    
    protected function _fetch_all($query, $args = []) {
        /* Select queries return a resultset */
        $stmt = $this->connector->prepare($query);
        $stmt->execute($args);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }//_fetch_all
    
    
    // Public functions
    
    public function read($key) {
        $value = $this->_execute_query(
            "SELECT value FROM data WHERE key=:key",
            ["key" => $key]
        )["value"];
        $decoded = json_decode($value, true);
        if ($decoded == null)
            $decoded = $value;
        return ['success' => true, 'data' => $decoded];
    }//read

    public function get_entry($key) {
        // TODO: test this
        return new SQLEntry($this, $key);
    }//get_entry

    public function write($key, $data) {
        if (!$this->_matches_key_regex($key)) {
            return [
                'success' => false,
                'data' => 'The given key does not match the given pattern. ' .
                          'This instance of Database has a limited scope'
            ];
        }
        // ---
        if (is_array($data))
            $data = json_encode($data);
        // ---
        $this->_execute_query(
            "INSERT INTO data VALUES (:key, :value, '')",
            ["key" => $key, "value" => $data]
        );
        return ['success' => true, 'data' => null];
    }//write

    public function delete($key) {
        $this->_execute_query(
            "DELETE FROM data WHERE key=:key",
            ["key" => $key]
        );
        return ['success' => true, 'data' => null];
    }//delete

    public function key_exists($key) {
        if (!$this->_matches_key_regex($key)) {
            return false;
        }
        // ---
        //TODO: test this
        return boolval($this->_execute_query(
            "SELECT count(*) AS count FROM data WHERE key=:key",
            ["key" => $key]
        )["count"]);
    }//key_exists

    public function list_keys() {
        $keys = $this->_fetch_all("SELECT key FROM data");
        // filter keys (if needed)
        if (!is_null($this->entry_regex)) {
            $all_keys = $keys;
            $keys = [];
            foreach ($all_keys as $key) {
                // match the key against the given pattern
                if ($this->_matches_key_regex($key)) {
                    // add key to list of keys
                    array_push($keys, $key);
                }
            }
        }
        // return list of keys
        return $keys;
    }//list_keys

    public function size() {
        return intval($this->_execute_query(
            "SELECT count(*) AS count FROM data"
        )["count"]);
    }//size

    public function key_size($key) {
        return intval($this->_execute_query(
            "SELECT length(value) AS length FROM data WHERE key=:key",
            ["key" => $key]
        )["length"]);
    }//key_size

}//SQLDatabase
?>

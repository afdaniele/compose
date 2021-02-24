<?php
/**
 * Schema
 *
 * A class modeling the Compose Schema
 *
 */


namespace system\classes;



/**
 * ComposeSchema Class
 *
 */
class ComposeSchema {
    
    /**
     * The internal schema value stored as a flat dictionary.
     * For example, the schema:
     *
     *      {
     *          "details": "Description 1",
     *          "type": "object",
     *          "_data": {
     *              "a": "1",
     *              "b": "2",
     *              "c": "3"
     *          }
     *      }
     *
     *  will be stored as:
     *
     *      {
     *          "/details": "Description 1",
     *          "/type": "object",
     *          "/_data/a": "1",
     *          "/_data/b": "2",
     *          "/_data/c": "3"
     *      }
     *
     *
     *      /details
     *
     * @access protected.
     * @var array
     */
    protected $schema = [];
    
    //============================================================
    // PUBLIC STATIC FUNCTIONS
    //============================================================
    
    /**
     * Parses a compose schema and returns the corresponding ComposeSchema object.
     *
     * @access public
     * @param $schema array The input compose schema.
     * @return ComposeSchema Returns the ComposeSchema
     */
    public static function from_schema($schema) {
        // create result
        $obj_schema = new ComposeSchema();
        // parse schema
        $w = function ($ns, $key, $value, $expected_type) use (&$w, &$obj_schema) {
            $nns = array_merge($ns, [$key]);
            $nns_key = implode('/', $nns);
            // validate type
            $valid = self::_validate_part($value, $expected_type);
            if ($valid !== true) {
                throw new \Exception(sprintf(
                    "Invalid schema: %s at level '%s'", $valid, $nns_key));
            }
            //
            if (is_assoc($value)) {
                foreach ($value as $k => $v) {
                    $valid_key = self::_validate_key($k);
                    if ($valid_key !== true) {
                        throw new \Exception(sprintf(
                            "Invalid schema: %s at level '%s'", $valid_key, $nns_key));
                    }
                    // ---
                    $type = null;
                    if ($expected_type == 'schema' && $k == '_data') {
                        $type = $value['type'];
                    }
                    // ---
                    $w($nns, $k, $v, $type);
                }
            } else if (is_array($value) && !is_assoc($value)) {
                if (count($value) == 0) {
                    $w($ns, $key, '![]', null);
                } else {
                    foreach ($value as $k => $v) {
                        $type = ($expected_type == 'schema' && $k == '_data')? 'schema' : null;
                        $w($nns, $k, $v, $type);
                    }
                }
            } else {
                $obj_schema->schema[$nns_key] = $value;
            }
        };
        // start recursion
        if (count($schema) > 0) {
            $w([], '', $schema, 'schema');
        }
        // ---
        return $obj_schema;
    }//from_schema
    
    
    private static function _validate_key($key) {
        foreach (['/', '.'] as $symbol) {
            if (strpos($key, $symbol) !== false) {
                return sprintf("You cannot have the symbol '%s' inside your keys.", $symbol);
            }
        }
        return true;
    }//_validate_key
    
    
    private static function _validate_part($value, $expected_type) {
        switch ($expected_type) {
            case 'schema':
                if (!is_assoc($value)) {
                    return "Expected object of type 'schema'";
                }
                $mandatory_keys = ['type', 'details'];
                foreach ($mandatory_keys as $mkey) {
                    if (!array_key_exists($mkey, $value)) {
                        return sprintf("Expected mandatory key '%s'", $mkey);
                    }
                }
                break;
            case 'object':
                if (!is_assoc($value)) {
                    return "Expected object of type 'object' (aka associative-array)";
                }
                break;
            case 'array':
                if (!is_assoc(value)) {
                    return "Expected object of type 'array' (aka positional-array)";
                }
                break;
            default:
                break;
        }
        return true;
    }//_validate_part
    
    
    private static function _decode_value(&$value) {
        if ($value === '![]') {
            return [];
        }
        return $value;
    }
    
    
    private static function &_encode_value(&$value) {
        if (is_array($value) && count($value) == 0) {
            $str = '![]';
            return $str;
        }
        return $value;
    }
    
    
    private static function _path_to_namespace($path) {
        // reconstruct absolute ns
        $ns_key = str_replace('/', '/_data/', $path);
        $ns_key = str_replace('.', '/', $ns_key);
        // ---
        return $ns_key;
    }
    
    
    private static function _namespace_to_path($ns) {
        // go from absolute ns to path
        $path = rtrim($ns, '/');
        $path = str_replace('/_data/', '|', $path);
        $path = str_replace('/', '.', $path);
        $path = str_replace('|', '/', $path);
        // ---
        return $path;
    }
    
    
    /**
     * Reconstruct schema from a given namespace;
     *
     * @param string $ns       The path to the field inside the schema.
     * @return ComposeSchema   Schema from the given path, an empty schema is returned if the subschema does not exist.
     */
    private function _subschema($ns) {
        // find all keys with this prefix
        $subschema = [];
        foreach ($this->schema as $k => $v) {
            if (startsWith($k, $ns)) {
                $nk = substr($k, strlen($ns) - 1);
                $subschema[$nk] = $v;
            }
        }
        if (count($subschema) === 0) {
            return null;
        }
        // ---
        $schema = new ComposeSchema();
        $schema->schema = $subschema;
        return $schema;
    }
    
    /** Finds all leaves corresponding to the field `field_name` of subschemas
     *
     * @param $field_name string    Name of the field corresponding to the leaves to extract
     * @return array                Namespaces identifying leaves of type `$field_name`
     */
    private function _leaves($field_name) {
        $match = sprintf('/%s', $field_name);
        // find all namespaces ending in /$field_name
        $leaves_candidates = [];
        foreach ($this->schema as $ns => &$_) {
            if (!endsWith($ns, $match)) continue;
            array_push($leaves_candidates, $ns);
        }
        // keep only those namespaces that are not pointing to subschemas
        $leaves = [];
        foreach ($leaves_candidates as $leaf) {
            $is_leaf = true;
            $parent_ns = substr($leaf, 0, strlen($leaf) - strlen($field_name));
            $_data_ns = sprintf('%s%s', $parent_ns, '_data');
            foreach ($this->schema as $ns => &$_) {
                if (startsWith($ns, $_data_ns)) {
                    $is_leaf = false;
                    break;
                }
            }
            if ($is_leaf) {
                array_push($leaves, $leaf);
            }
        }
        // ---
        return $leaves;
    }//_leaves
    
    //============================================================
    // PUBLIC FUNCTIONS
    //============================================================
    
    /**
     * Class constructor.
     *
     * @access private
     */
    private function __construct() {}
    
    /**
     * Returns whether the schema is empty.
     *
     * @return boolean
     */
    public function is_empty() {
        return count($this->schema) <= 0;
    }
    
    /**
     * Returns the schema as array;
     *
     * @return array
     */
    public function asArray() {
        // create result
        $schema = [];
        foreach ($this->schema as $ns => $value) {
            $ns = explode('/', ltrim($ns, '/'));
            // navigate to the right place in the result
            $sel = &Utils::cursorTo($schema, array_splice($ns, 0, count($ns)-1), true);
            // plant the value
            $sel[$ns[count($ns)-1]] = self::_decode_value($value);
        }
        // ---
        return $schema;
    }//asArray
    
    /**
     * Returns all the default values of the form in a Values array.
     *
     * @return array
     */
    public function defaults() {
        // create result
        $res = [];
        foreach ($this->schema as $ns => $value) {
            // filter nss (avoid navigating positional arrays)
            if (strpos($ns, '/0/') !== false) continue;
            // filter nss (only interested in default values)
            if (!endsWith($ns, '/default')) continue;
            // remove _data level
            $ns = str_replace('/_data', '', $ns);
            $ns = explode('/', ltrim($ns, '/'));
            // navigate to the right place in the result
            $sel = &Utils::cursorTo($res, array_splice($ns, 0, count($ns)-2), true);
            // plant the value
            $sel[$ns[count($ns)-2]] = self::_decode_value($value);
        }
        // ---
        return $res;
    }
    
    /**
     * Returns whether an element is contained in the schema;
     *
     * @param string $path The path to the field inside the schema.
     * @return boolean
     */
    public function has($path) {
        // reconstruct absolute ns
        $ns_key = self::_path_to_namespace($path);
        // ---
        return array_key_exists($ns_key, $this->schema);
    }
    
    /**
     * Returns a schema field's value;
     *
     * @param string $path The path to the field inside the schema.
     * @param null $default The default value to return when the given path does not exist.
     * @return array
     */
    public function get($path, $default = null) {
        // reconstruct absolute ns
        $ns_key = self::_path_to_namespace($path);
        // ---
        return array_key_exists($ns_key, $this->schema)?
            self::_decode_value($this->schema[$ns_key]) : $default;
    }
    
    /**
     * Sets a schema field's value;
     *
     * @param string $path The path to the field inside the schema.
     * @param mixed $value The value to assign to the field.
     * @param boolean $allow_new Whether we allow new paths to be formed. By default only existing paths can be updated.
     * @return boolean      Whether the change had an effect.
     */
    public function set($path, $value, $allow_new=false) {
        // reconstruct absolute ns
        $ns_key = self::_path_to_namespace($path);
        // ---
        if ($allow_new || array_key_exists($ns_key, $this->schema)) {
            $this->schema[$ns_key] = &self::_encode_value($value);
            return true;
        }
        return false;
    }
    
    /**
     * Reconstruct schema from a given path;
     *
     * @param string $path     The path to the field inside the schema.
     * @return ComposeSchema   Schema from the given path, an empty schema is returned if the subschema does not exist.
     */
    public function subschema($path) {
        // reconstruct absolute ns
        $ns_prefix = self::_path_to_namespace($path) . '/';
        // extract subschema
        return $this->_subschema($ns_prefix);
    }
    
    /**
     * Walk over a Values array and apply a callback function. The callback
     * receives the arguments ($ns, $value, $schema), where:
     *      - $ns is the absolute namespace of the leaf currently visiting;
     *      - $value is a leaf value from the Values array (or NULL if values are not given
     *          or the value for the current leaf is not set in $values);
     *      - $schema is the Subschema corresponing to the level of the leaf value;
     *
     * @param callable $callback The callback to apply to each (schema, value) pair.
     * @param array $values A Values array to walk over.
     */
    public function walk(&$callback, &$values=null) {
        $type_leaves = $this->_leaves('type');
        foreach ($type_leaves as &$leaf_ns) {
            $parent_ns = substr($leaf_ns, 0, strlen($leaf_ns) - 4);
            $subschema = $this->_subschema($parent_ns);
            $path = self::_namespace_to_path($parent_ns);
            $value_ptr = &Utils::cursorTo($values, $path);
            // execute callback
            $callback($path, $value_ptr, $subschema);
        }
    }
    
}

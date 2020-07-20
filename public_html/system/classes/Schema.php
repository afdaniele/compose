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
            return '![]';
        }
        return $value;
    }
    
    
    private static function _full_ns($path) {
        // reconstruct absolute ns
        $ns_key = str_replace('/', '/_data/', $path);
        $ns_key = str_replace('.', '/', $ns_key);
        // ---
        return $ns_key;
    }
    
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
     * as_array magic method.
     *
     * @return array
     */
    public function as_array() {
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
    }
    
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
        $ns_key = self::_full_ns($path);
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
        $ns_key = self::_full_ns($path);
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
        $ns_key = self::_full_ns($path);
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
        $ns_prefix = self::_full_ns($path) . '/';
        // find all keys with this prefix
        $subschema = [];
        foreach ($this->schema as $k => $v) {
            if (startsWith($k, $ns_prefix)) {
                $nk = substr($k, strlen($ns_prefix) - 1);
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
    
    /**
     * Walk over a Values array and apply a callback function. The callback
     * receives the arguments ($value, $schema), where:
     *      - $value is a leaf value from the Values array;
     *      - $schema is the subschema corresponing to the level of the leaf value;
     *
     * @param array $values A Values array to walk over.
     * @param callable $callback The callback to apply to each (schema, value) pair.
     */
    public function walk(&$values, &$callback) {
        $w = function ($ns, &$value) use (&$w, &$callback) {
            if (is_array($value)) {
                if (is_assoc($value)) {
                    foreach ($value as $k => &$v) {
                        $w(array_merge($ns, [$k]), $v);
                    }
                } else {
                    foreach ($value as $_ => &$v) {
                        $w(array_merge($ns, [0]), $v);
                    }
                }
            } else {
                // reconstruct full ns
                $ns_key = [];
                foreach ($ns as $p) {
                    array_push($ns_key, $p);
                }
                $ns_key = implode('/', array_merge([''], $ns_key));
                // get subschema
                $subschema = $this->subschema($ns_key);
                // execute callback
                $callback($ns_key, $value, $subschema);
            }
        };
        // start recursion
        $w([], $values);
    }
    
}

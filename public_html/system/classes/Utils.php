<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes;

use Throwable;

/**
 *   Utility module.
 */
class Utils {
    
    // disable the constructor
    private function __construct() {
    }
    
    
    // =======================================================================================================
    // Utility functions
    
    public static function regex_extract_group($string, $pattern, $groupNum) {
        preg_match_all($pattern, $string, $matches);
        return $matches[$groupNum][0];
    }//regex_extract_group
    
    public static function string_to_valid_filename($string) {
        //lowercase
        $string = strtolower($string);
        //replace more than one space to underscore
        $string = preg_replace('/([\s])\1+/', '_', $string);
        //convert any single space to underscrore
        $string = str_replace(" ", "_", $string);
        //remove non alpha numeric characters
        $string = preg_replace("/[^A-Za-z0-9_]/", '', $string);
        // return sanitized string
        return $string;
    }//string_to_valid_filename
    
    public static function generateRandomString($length, $set = "alphanumeric"): string {
        switch ($set) {
            case 'alphanumeric':
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
            case 'alphabetic':
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric':
                $chars = '0123456789';
                break;
            default: // alphanumeric
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
        }
        $count = mb_strlen($chars);
        //
        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }
        return $result;
    }//generateRandomString
    
    
    public static function arrayIntersectAssocRecursive(&$arr1, &$arr2) {
        if (!is_array($arr1) || !is_array($arr2)) {
            return (string)$arr1 == (string)$arr2;
        }
        $commonkeys = array_intersect(array_keys($arr1), array_keys($arr2));
        $ret = array();
        foreach ($commonkeys as $key) {
            $ret[$key] =& self::arrayIntersectAssocRecursive($arr1[$key], $arr2[$key]);
        }
        return $ret;
    }//arrayIntersectAssocRecursive
    
    
    public static function arrayMergeAssocRecursive(&$arr1, &$arr2, $allow_create = true) {
        if (!is_array($arr1) || !is_array($arr2)) {
            return $arr2;
        }
        $allkeys = array_merge(array_keys($arr1), array_keys($arr2));
        $ret = [];
        foreach ($allkeys as $key) {
            if (array_key_exists($key, $arr1) && !array_key_exists($key, $arr2)) {
                $ret[$key] =& $arr1[$key];
            } else {
                if (array_key_exists($key, $arr2) && !array_key_exists($key, $arr1)) {
                    if ($allow_create) {
                        $ret[$key] =& $arr2[$key];
                    }
                } else {
                    $ret[$key] =& self::arrayMergeAssocRecursive($arr1[$key], $arr2[$key], $allow_create);
                }
            }
        }
        return $ret;
    }//arrayMergeAssocRecursive
    
    
    public static function pathToNS(string $path): array {
        return preg_split('[/|\.]', trim($path, '/.'));
    }//pathToNS
    
    
    public static function &cursorTo(&$array, $ns, bool $create = false) {
        if (is_string($ns)) {
            $ns = Utils::pathToNS($ns);
        }
        $sel = &$array;
        foreach ($ns as $ptr) {
            if (!array_key_exists($ptr, $sel)) {
                if ($create) {
                    $sel[$ptr] = [];
                } else {
                    $nullptr = null;
                    return $nullptr;
                }
            }
            $sel = &$sel[$ptr];
        }
        return $sel;
    }//cursorTo
    
    
    public static function pathExists(array &$array, string|array $ns): bool {
        if (is_string($ns)) $ns = Utils::pathToNS($ns);
        $sel = &$array;
        foreach ($ns as $ptr) {
            if (!array_key_exists($ptr, $sel)) {
                return false;
            }
            $sel = &$sel[$ptr];
        }
        return true;
    }//cursorTo
    
    
    public static function arrayPaths(&$array): array {
        $paths = [];
        Utils::_arrayPathsWorker($paths, $array, '');
        return $paths;
    }
    
    
    private static function _arrayPathsWorker(&$paths, &$a, $ns) {
        foreach ($a as $k => &$v) {
            $current_ns = sprintf("%s.%s", $ns, $k);
            if (!is_array($v)) {
                array_push($paths, $current_ns);
            } else {
                Utils::_arrayPathsWorker($paths, $v, $current_ns);
            }
        }
    }
    
    public static function formatStacktrace(Throwable $exception): string {
        $i = 0;
        $out = "";
        foreach ($exception->getTrace() as $frame) {
            $file = $frame["file"] ?? "&#10096;nofile&#10097;";
            $line = $frame["line"] ?? "&#10096;noline&#10097;";
            $function = $frame["function"] ?? "&#10096;nofunction&#10097;";
            $args = $frame["args"] ?? [];
            $args_str = implode(", ", array_map(function ($e) { return var_export($e, true); }, $args));
            $out .= sprintf("#%d %s(%d): %s(%s)\n", $i++, $file, $line, $function, $args_str);
        }
        return $out;
    }
    
    public static function assocArrayToObject(array $array) {
        return json_decode(json_encode($array));
    }
    
}//Utils

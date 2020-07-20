<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\classes;

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
    
    public static function generateRandomString($length) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
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
            }
            else {
                if (array_key_exists($key, $arr2) && !array_key_exists($key, $arr1)) {
                    if ($allow_create) {
                        $ret[$key] =& $arr2[$key];
                    }
                }
                else {
                    $ret[$key] =& self::arrayMergeAssocRecursive($arr1[$key], $arr2[$key], $allow_create);
                }
            }
        }
        return $ret;
    }//arrayMergeAssocRecursive
    
    
    public static function &cursorTo(&$array, $ns, $create = false) {
        $sel = &$array;
        foreach ($ns as $ptr) {
            if (!array_key_exists($ptr, $sel)) {
                if ($create) {
                    $sel[$ptr] = [];
                } else {
                    return null;
                }
            }
            $sel = &$sel[$ptr];
        }
        return $sel;
    }//cursorTo
    
    
    public static function &pathExists(&$array, $ns) {
        $sel = &$array;
        foreach ($ns as $ptr) {
            if (!array_key_exists($ptr, $sel)) {
                return false;
            }
            $sel = &$sel[$ptr];
        }
        return true;
    }//cursorTo
    
}//Utils

?>

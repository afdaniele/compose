<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, January 10th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018


namespace system\classes;

/**
*   Utility module.
*/
class Utils{

	// disable the constructor
	private function __construct() {}



	// =======================================================================================================
	// Utility functions

    public static function regex_extract_group($string, $pattern, $groupNum){
        preg_match_all($pattern, $string, $matches);
        return $matches[$groupNum][0];
    }//regex_extract_group

}//Utils

?>

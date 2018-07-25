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

    public static function regex_extract_group( $string, $pattern, $groupNum ){
        preg_match_all($pattern, $string, $matches);
        return $matches[$groupNum][0];
    }//regex_extract_group

	public static function string_to_valid_filename( $string ){
		//remove non alpha numeric characters
		$string = preg_replace("/[^A-Za-z0-9 _]/", '', $string);
		//lowercase
		$string = strtolower($string);
		//replace more than one space to underscore
		$string = preg_replace('/([\s])\1+/', '_', $string );
		//convert any single space to underscrore
		$string = str_replace(" ","_",$string);
		// return sanitized string
		return $string;
	}//string_to_valid_filename

	public static function generateRandomString( $length ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = mb_strlen($chars);
		//
		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}
		return $result;
	}//generateRandomString

}//Utils

?>

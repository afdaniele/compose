<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 11/28/14
 * Time: 12:46 PM
 */

namespace system\classes\enum;


class StringType {

	// constants
	const ALPHABETIC = "/^[a-zA-Z]+$/";
	const ALPHABETIC_SPACE = "/^[a-zA-Z\\s]+$/";
	const ALPHANUMERIC = "/^[a-zA-Z0-9]+$/";
	const ALPHANUMERIC_SPACE = "/^[a-zA-Z0-9\\s]+$/";
	const NUMERIC = "/^[0-9]+$/";
	const FLOAT = "/^-?(?:\\d+|\\d*\\.\\d+)$/";
	const PASSWORD = "/^[a-zA-Z0-9_.-]+$/";
	const TEXT = "/^[\\w\\D\\s_.,-:\(\)]*$/";
	const EMAIL = "/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/";
	const KEY = "/^[a-zA-Z_]+$/";
	const VERSION = "/^v?\\d+(\\.\\d+)?(\\.\\d+)?$/";


	// methods

	public static function isValid( $string, $type, $length=null ){
		if( is_null($type) ) return false;
		if( is_null($string) ) return false;
		if( !is_null($length) && $length != strlen($string) ) return false;
		//
		return ( preg_match($type, $string) == 1 );
	}//isValid


	public static function isAlphabetic( $string, $length=null ){
		return self::isValid($string, self::ALPHABETIC, $length);
	}//isAlphabetic


	public static function isNumeric( $string, $length=null ){
		return self::isValid($string, self::NUMERIC, $length);
	}//isNumeric


	public static function isAlphaNumeric( $string, $length=null ){
		return self::isValid($string, self::ALPHANUMERIC, $length);
	}//isAlphaNumeric


	public static function isValidEmailAddress( $string, $length=null ){
		return self::isValid($string, self::EMAIL, $length);
	}//isAvalidEmailAddress


	public static function getRegexByTypeName( $name ){
		switch( $name ){
			case 'alpha':
			case 'alphabetic':
				return self::ALPHABETIC;
			case 'alphaspace':
				return self::ALPHABETIC_SPACE;
			case 'alphanumeric':
				return self::ALPHANUMERIC;
			case 'alphanumericspace':
				return self::ALPHANUMERIC_SPACE;
			case 'boolean':
			case 'numeric':
				return self::NUMERIC;
			case 'float':
				return self::FLOAT;
			case 'password':
				return self::PASSWORD;
			case 'text':
				return self::TEXT;
			case 'email':
				return self::EMAIL;
			case 'key':
				return self::KEY;
			case 'version':
				return self::VERSION;
			default:
				return null;
		}
	}//getRegexByTypeName

}//StringType

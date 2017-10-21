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

	public static $ALPHABETIC = "/^[a-zA-Z]+$/";
	public static $ALPHABETIC_SPACE = "/^[a-zA-Z\\s]+$/";
	public static $ALPHANUMERIC = "/^[a-zA-Z0-9]+$/";
	public static $ALPHANUMERIC_SPACE = "/^[a-zA-Z0-9\\s]+$/";
	public static $NUMERIC = "/^[0-9]+$/";
	public static $FLOAT = "/^-?(?:\\d+|\\d*\\.\\d+)$/";
	public static $PASSWORD = "/^[a-zA-Z0-9_.-]+$/";
	public static $TEXT = "/^[\\w\\D\\s_.,-:\(\)]*$/";
	public static $EMAIL = "/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/";
	public static $KEY = "/^[a-zA-Z_]+$/";
	public static $VERSION = "/^\\d+\\.\\d+$/";



	// methods

	public static function isValid( $string, $regex ){
		return ( ($regex === null)? false : (preg_match($regex, $string) == 1) );
	}//isValid

	public static function byName( $name ){
		switch( $name ){
			case 'alpha':
			case 'alphabetic':
				return self::$ALPHABETIC;
			case 'alphaspace':
				return self::$ALPHABETIC_SPACE;
			case 'alphanumeric':
				return self::$ALPHANUMERIC;
			case 'alphanumericspace':
				return self::$ALPHANUMERIC_SPACE;
			case 'boolean':
			case 'numeric':
				return self::$NUMERIC;
			case 'float':
				return self::$FLOAT;
			case 'password':
				return self::$PASSWORD;
			case 'text':
				return self::$TEXT;
			case 'email':
				return self::$EMAIL;
			case 'key':
				return self::$KEY;
			case 'version':
				return self::$VERSION;
			default:
				return null;
		}
	}//byName

	//TODO: translate
	public static function getDescription( $type ){
		switch( $type ){
			case 'alpha':
			case 'alphabetic':
				return 'Puo\' contenere solo lettere';
			case 'alphaspace':
				return 'Puo\' contenere solo lettere e spazi';
			case 'alphanumeric':
				return 'Puo\' contenere solo lettere e numeri';
			case 'alphanumericspace':
				return 'Puo\' contenere solo lettere, numeri e spazi';
			case 'numeric':
				return 'Puo\' contenere solo numeri (formato intero)';
			case 'float':
				return 'Puo\' contenere solo numeri (formato decimale)';
			case 'password':
				return 'Puo\' contenere solo lettere, numeri, e i caratteri \'_-.\'';
			case 'text':
				return 'Puo\' contenere solo lettere, numeri, spazi e punteggiatura';
			case 'email':
				return 'Deve contenere un indirizzo email valido';
			case 'key':
				return 'Puo\' contenere solo lettere ed il carattere _';
			case 'version':
				return 'Deve contenere un numero di versione valido';
			default:
				return null;
		}
	}//getDescription

}//StringType

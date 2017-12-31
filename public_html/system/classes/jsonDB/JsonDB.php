<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 3/13/15
 * Time: 1:22 AM
 */

namespace system\classes\jsonDB;


class JsonDB {

	private $file;
	private $json;

	public function __construct( $filename ){
		$this->file = $filename;
		// load the file content
		$file_content = file_get_contents( $filename );
		if( $file_content === false ){
			// the file does not exist
			$this->json = array('_empty_config' => true);
		}else{
			$this->json = json_decode($file_content, true);
		}
	}//constructor

	public function contains( $key ){
		return (isset($this->json[$key]));
	}//contains

	public function get( $key, $default=null ){
		return ( (isset($this->json[$key]))? $this->json[$key] : $default );
	}//get

	public function set( $key, $val ){
		$this->json[$key] = $val;
	}//set

	public function commit(){
		$file_content = self::prettyPrint(json_encode( $this->json ));
		try{
			$res = file_put_contents( $this->file, $file_content );
			if( $res === false ){
				$error = error_get_last();
				return array('success' => false, 'data' => 'Impossibile scrivere le impostazioni. Il server riporta: ('.$error['message'].')');
			}else{
				return array('success' => true );
			}
		}catch( \Exception $e ){
			return array('success' => false, 'data' => 'Impossibile scrivere le impostazioni. Il server riporta: ('.$e->getMessage().')');
		}
		return array('success' => false, 'data' => 'Impossibile scrivere le impostazioni. Riprova!');
	}//commit

	public function asArray(){
		return $this->json;
	}//asArray


	// utility

	private function prettyPrint( $json ){
		$result = '';
		$level = 0;
		$in_quotes = false;
		$in_escape = false;
		$ends_line_level = NULL;
		$json_length = strlen( $json );

		for( $i = 0; $i < $json_length; $i++ ) {
			$char = $json[$i];
			$new_line_level = NULL;
			$post = "";
			if( $ends_line_level !== NULL ) {
				$new_line_level = $ends_line_level;
				$ends_line_level = NULL;
			}
			if ( $in_escape ) {
				$in_escape = false;
			} else if( $char === '"' ) {
				$in_quotes = !$in_quotes;
			} else if( ! $in_quotes ) {
				switch( $char ) {
					case '}': case ']':
					$level--;
					$ends_line_level = NULL;
					$new_line_level = $level;
					break;

					case '{': case '[':
					$level++;
					case ',':
						$ends_line_level = $level;
						break;

					case ':':
						$post = " ";
						break;

					case " ": case "\t": case "\n": case "\r":
					$char = "";
					$ends_line_level = $new_line_level;
					$new_line_level = NULL;
					break;
				}
			} else if ( $char === '\\' ) {
				$in_escape = true;
			}
			if( $new_line_level !== NULL ) {
				$result .= "\n".str_repeat( "\t", $new_line_level );
			}
			$result .= $char.$post;
		}
		return $result;
	}//prettyPrint

} //JsonDB

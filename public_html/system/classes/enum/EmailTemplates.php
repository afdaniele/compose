<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 11/28/14
 * Time: 12:46 PM
 */

namespace system\classes\enum;

use system\classes\Configuration;

require_once __DIR__.'/../Configuration.php';


class EmailTemplates {

	// constants

	private static $_DEFAULT_PLACEHOLDERS = array('#{companyname}#', '#{sitename}#', '#{location}#', '#{date}#', '#{sitelink}#', '#{company}#');
	private static $_DEFAULT_PLACEHOLDERS_VALUES;

	// available emails
	public static $USER_NEW_USER_REGISTERED = array(
		'type' => 'template_button',
		'placeholders' => array('#{title}#', '#{first_name}#', '#{username}#', '#{password}#'),
		'message' => 	'Hi #{first_name}#,
						<br/>
						Welcome to #{sitename}#.<br/>
						You can access the system using the following credentials:<br/>
						<br/>
						<strong>Username:</strong> #{username}#<br/>
						<strong>Password:</strong> #{password}#<br/>
						<br/><br/>
						Thank you.<br/>#{sitename}#.'
	);


	// methods

	public static function init(){
		// company name
		$company_name_str = Configuration::$SHORT_SITE_NAME;
		// sitename
		$sitename_str = Configuration::$SHORT_SITE_LINK;
		// location
		$location_str = 'Duckietown';
		// date
		$date_str = date("m/d/Y");
		// sitelink
		$sitelink_str = Configuration::$BASE;
		//
		self::$_DEFAULT_PLACEHOLDERS_VALUES = array( $company_name_str, $sitename_str, $location_str, $date_str, $sitelink_str, $company_name_str );
	}//init

	public static function getFile( &$template ){
		return $template['type'].'.html';
	}//getFile

	public static function getPlaceholders( &$template ){
		return array_merge( self::$_DEFAULT_PLACEHOLDERS, $template['placeholders'] );
	}//getPlaceholders

	public static function fill( &$template, &$replace ){
		$filepath = __DIR__.'/../../templates/emails/'.self::getFile( $template );
		$body_template = file_get_contents( $filepath );
		if( $body_template === false ){
			return array( 'success' => false, 'data' => 'Template ('.$filepath.') non trovato!' );
		}
		//
		$placeholders = self::getPlaceholders($template);
		$data = array_merge(self::$_DEFAULT_PLACEHOLDERS_VALUES, $replace);
		//
		$message = str_replace( $placeholders, $data, $template['message'] );
		//
		$data = str_replace( array_merge($placeholders, array('#{message}#')), array_merge($data, array($message)), $body_template );
		//
		return array( 'success' => true, 'data' => $data );
	}//fill

}//EmailTemplates

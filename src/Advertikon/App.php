<?php
/**
* Module application
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon;

use Advertikon\Exception\InvalidArgument;

/**
* Module application class
*/
class App {

	/**
	* @var Object Advertikon\Module
	*/
	static protected $_module;

	/**
	* @var integer TEXT_CATEGORY Category text type constant
	*/
	const TEXT_CATEGORY 	= 1;
	/**
	* @var integer TEXT_SUBCATEGORY Subcategory text type constant
	*/
	const TEXT_SUBCATEGORY 	= 2;


	/**
	* @var string PRELOAD_EVENT Event triggere before loading data from resource
	*/
	const PRELOAD_EVENT = 'preload';
	/**
	* @var string AFTERLOAD_EVENT Event triggere after loading data from resource 
	*/
	const AFTERLOAD_EVENT = 'afterload';
	/**
	* @var string PRESAVE_EVENT Event triggere before saving data to resource
	*/
	const PRESAVE_EVENT = 'presave';
	/**
	* @var string AFTERSAVE_EVENT Event triggere after saving data to resource 
	*/
	const AFTERSAVE_EVENT = 'aftersave';
	/**
	* @var string PREDELETE_EVENT Event triggere before deleting data from resource
	*/
	const PREDELETE_EVENT = 'predelete';
	/**
	* @var string AFTERDELETE_EVENT Event triggere after deleteing data from resource 
	*/
	const AFTERDELETE_EVENT = 'afterdelete';
	/**
	* @var string GET_SELECT_EVENT Event to populate select instance
	*/
	const GET_SELECT_EVENT = 'get_select_event';
	/**
	* @var string SELECT_DATA_EVENT Event to process select data
	*/
	const SELECT_DATA_EVENT = 'select_data_event';


	/**
	* Set module instanse
	*
	* @param Object $module Advertikon\Module
	* @throws Advertikon\Exception\Error If given not instance of Advertikon\Module
	*/
	static public function setModule( $module ) {
		self::checkArgument( 'object' , $module , __METHOD__ );
		self::$_module = $module;
	}

	/**
	* Get module instance
	*
	* @return Object Advertikon\Module
	*/
	static public function getModule() {
		return self::$_module;
	}

	/**
	* Transform string from camelCase to under_score
	*
	* @param string $camelCase CameleCased string
	* @return string under_scored string
	*/
	static public function underscore( $camelCase ) {
		return strtolower( preg_replace( '/(.)([A-Z])/m', '$1_$2' , $camelCase ) );
	}

	/**
	* Check argument type
	*
	* @param string $expType Expected argument type
	* @param mixed @value Argument value
	* @param string $method Method, which argument is checked
	* @param string $argumentNumber Argument number
	* @throws Advertikon\Exception\IvalidArgument On argument mismatch
	*/
	static public function checkArgument( $expType , $value , $method , $argumentNumber = 'first') {

		switch( strtolower( $expType ) ) {
			case 'string' : 
				if( is_string( $value ) ) {
					return true;
				}
			break;
			case 'scalar' : 
				if( is_scalar( $value ) ) {
					return true;
				}
			case 'object' : 
			if( is_object( $value ) ) {
				return true;
			}
			default :
				throw new InvalidArgument( sprintf( '%s: Invalid argument type to check against - %s' , __METHOD__ , $extType ) );
			break;
		}

		$getType = gettype( $value );
		throw new InvalidArgument( sprintf( '%s: Expects %s argument be type of %s, %s given istead' , $method, $argumentNumber , $expType , $gotType ) );
	}
}
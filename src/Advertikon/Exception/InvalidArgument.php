<?php
/**
* Invalid argument exception
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\Exception;

use Advertikon\Exception;

/**
* Invalid Argument exception class
*/
class InvalidArgument extends Exception{

	/**
	* Exceprion constructor
	*
	* @param string $msg Exceptio message
	* @param string $method Method name
	*/
	public function __construct( $msg , $method = null ) {
		if( $method ) {
			$msg = $method . ' : ' . $msg;
		}
		parent::__construct( $msg );
	}
}
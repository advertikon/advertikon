<?php
/**
* Form Label helper
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\View\Helper;

use Advertikon\App;
use Advertikon\Exception\InvalidArgument;

/**
* Form label helper
*/
class Label extends Fieldset {

	/**
	* @see Advertikon\View\Helper\Element::openTag()
	*/
	public function openTag() {
		$str = parent::openTag();
		if( $this->_element->getValue() && $this->_element->getOption( 'prepend' ) ) {
			$str .= self::newLine( $this->translate( $this->_element->getValue() ) );
		}
		return $str;
	}

	/**
	* @see Advertikon\View\Helper\Element
	*/
	public function closeTag() {
		$str = '';
		if( $this->_element->getValue() && $this->_element->getOption( 'append' ) ) {
			$str .= self::newLine( $this->translate( $this->_element->getValue() ) );
		}
		$str .= parent::closeTag();
		return $str;
	}

}
?>
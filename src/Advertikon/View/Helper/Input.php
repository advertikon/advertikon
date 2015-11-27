<?php
/**
* Form Input Helper
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\View\Helper;

use Advertikon\App;
use Advertikon\Exception\InvalidArgument;
use Advertikon\Form\Element\Select as FormSelect;
use Advertikon\Form\Element as FormElement;
/**
* Form INput Helper Class
*/
class Input extends Element {

	/**
	* Get specific form input helper and render it
	*
	* @return string
	*/
	public static function getElementHelper( $element ) {
		switch( $element->getAttribute( 'type' ) ) {
			case 'text' :
				return new Text( $element );
			case 'textarea' : 
				return new Textarea( $element );
			case 'submit' :
				return new Submit( $element );
			case 'button' :
				if( $element instanceof FormSelect ) {
					$groupName = $element->getOption( 'groupName' );
					if( $groupName ) {
						switch( $groupName ) {
							case FormElement::INPUT_SINGLE_DROPDOWN : 
								return new ButtonSingleDropdownInputGroup( $element );
							case FormElement::BUTTON_SINGLE_DROPDOWN :
								return new ButtonSingleDropdown( $element );
							default:
								throw new InvalidArgument( sprintf( '%s: Invalid button group name' ) );
						}
					}
					return new ButtonSingleDropdown( $element );
				}
				return new Button( $element );
			case 'select' :
				return new Select( $element );
			default :
				return new Text( $element );
			break; 
		}
	}

	/**
	* @see Advertikon\View\Helper\Element
	*/
	public function render() {
		$str = '';
		//make 
		if( $this->_element->getOption( 'group_id' ) ) {
			$this->_element->setAttribute( 'data-grouped' , '1' );
		}
		$str .= self::newLine( $this->openTag() );
		//if element is part of a group render error message on that group wrapper
		if( ! $this->_element->getOption( 'group_id' ) ) {
			$str .= $this->error();
		}
		return $str;
	}
}
?>
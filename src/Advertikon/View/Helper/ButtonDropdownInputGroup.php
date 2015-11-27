<?php
/**
* Form single dropdown button helper
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
* Form single dropdown button class
*/
class ButtonDropdownInputGroup extends Button {


	/**
	* @see Advertikon\View\Helper\Element::render()
	*/
	public function render() {
		$this->addClass( 'dropdown-toggle' );
		$this->_element->setAttributes( [
										'data-toggle'	=> 'dropdown',
										'aria-haspopup'	=> 'true',
										'aria-expanded'	=> 'false',
										//'tabindex'		=> '-1', 
										] );
		$str = self::newLine( '<div class="btn-group">' );
		self::$indCount++;
		$str .= parent::openTag();
		$caret = '';
		if( $label = $this->_element->getLabel() ) {
			$caret .= $this->translate( $label );
		}
		$caret .= '<span class="caret"></span>';
		$str .= self::newLine( $caret );
		$str .= parent::closeTag();
		$str .= self::newLine( '<ul class="dropdown-menu">' );
		self::$indCount++;
		foreach( $this->_element->getValueOptions() as $value => $text ) { 
			if( is_array( $text ) ) {
				if( isset( $text[ 'options' ] ) ) {
					$disabled = isset( $text[ 'disabled' ] ) ? (array)$text[ 'disabled' ] : [];
					foreach( $text[ 'options' ] as $subValue => $subText ) {
						$str .= sprintf( "<li %s><a href='#' data-value='%s'>%s</a></li>" ,
							in_array( $subValue , $disabled ) ? 'class="disabled"' : '',
							$this->escaper->escapeHtmlAttr( $subValue ),
							$this->translate( $subText )
						);
					}
				}
			}
			else {
				$str .= self::newLine( sprintf( "<li><a href='#' data-value='%s'>%s</a></li>" , 
					$this->escaper->escapeHtmlAttr( $value ),
					$this->translate( $text )
				  ) );
			}
		}
		self::$indCount--;
 		$str .= self::newLine( '</ul>' );
 		self::$indCount--;
 		$str .= self::newLine( '</div>' );//<-- .btn-group
 		return $str;
	}
}
?>
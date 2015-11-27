<?php
/**
* Form Select helper
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\View\Helper;

use Advertikon\App;
use Advertikon\Exception\InvalidArgument;
use Zend\Form\ElementInterface;
use Zend\Form\Element\Select as ZendSelect;

/**
* Form select helper
*/
class Select extends Fieldset {

	public function setElement( $element ) {
		if( ! $element instanceof ZendSelect ) {
			throw new InvalidArgument( sprintf( '%s: Element must be instance of Zend\Form\Element\Select, %s given instead' , __METHOD__ , get_class( $element ) ) );
		}
		$element->setLabel( '' );
		parent::setElement( $element );
		$str = '';
		$label = $this->_element->getLabel();
		if( $label ) {
			$str .= sprintf( '<option>%s</option>' , $this->escaper->escapeHtmlAttr( $label ) ); 
		}
		foreach( $element->getValueOptions() as $value =>  $text ) {
			if( is_array( $text ) ) {
				if( isset( $text[ 'options' ] ) ) {
					$label = $value;
					if( isset( $text[ 'label' ] ) ) {
						$label = $text[ 'label' ];
					}
					$str .= self::newLine( sprintf( '<optgroup label="%s">' , $this->escaper->escapeHtmlAttr( $label ) ) );
					self::$indCount++;
					if( isset( $text[ 'options' ] ) ) {
						foreach( $text[ 'options' ] as $subValue => $subText ) {
							$str .= self::newLine( sprintf( '<option value="%s">%s</option>' ,
								$this->escaper->escapeHtmlAttr( $subValue ) ,
								$this->translate( $subText )
							 ) );
						}
					}
					self::$indCount--;
					$str .= self::newLine( '</optgroup>' );
				}
			}
			else {
				$str .= self::newLine( sprintf( '<option value="%s">%s</option>' ,
					$this->escaper->escapeHtmlAttr( $value ) ,
					$this->translate( $text )
				) );
			}
		}
		$this->add( $str );
	}

	/**
	* @see Advertikon\View\Helper\Element
	*/
	public function init() {
		$this->addClass( 'form-control' );
	}

}
?>
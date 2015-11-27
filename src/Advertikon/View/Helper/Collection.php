<?php
/**
* Form Input Part view
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\View\Helper;

use Advertikon\App;
use Advertikon\Exception\InvalidArgument;
use Zend\Form\Element\Collection as ZendCollection;
use Zend\Form\FieldsetInterface;
use Zend\Form\ElementInterface;

/**
* Form element view class
*/
class Collection extends Fieldset {

	/**
	* @see Advertikon\View\Helper\Fieldset::parse()
	*/
	public function parse( $element , $helper = null ) {
		if( ! $element instanceof ZendCollection ) {
			throw new InvalidArgument( sprintf( '%s: Parsed element must instance of Zend\Form\Element\Collection, got %s element' , __METHOD__ , get_class( $element )  ) ); 
		}
		parent::parse( $element , $helper );
		$this->add( $this->renderTemplate() );
		return $this;
	}

	/**
	* Render collection template
	*
	* @return string
	*/
	public function renderTemplate() {

		$element = $this->_element->getTemplateElement();

        if ( $element instanceof FieldsetInterface ) {
            $helper = $this->getFieldsetHelper();
        }
        elseif ( $element instanceof ElementInterface ) {
            $helper = $this->getRowHelper();
        }
        else {
        	throw new InvalidArgument( sprintf( '%s: Template element should be instance of Zend\Form\Element, %s diven instead' , __METHOD__ , get_class( $element ) ) );
        }

        //print template in one line
        $withIndent = self::$withIndent;
        $indCount = self::$indCount;
        self::$withIndent = false;
        $str = sprintf( '<span data-template="%s"></span>' , $this->escaper->escapeHtmlAttr( $helper->parse( $element ) . '' ) );
        self::$withIndent = $withIndent;
        self::$indCount = $indCount;
        return $str;
	}

}
?>
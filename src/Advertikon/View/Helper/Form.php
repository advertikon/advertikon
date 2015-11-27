<?php
/**
* View Ad edit Form part
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\View\Helper;

use Advertikon\App;
use Advertikon\Exception\InvalidArgument;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\CollectionInputFIlter;
use Zend\Form\FormInterface;
use Zend\Form\FieldsetInterface;
use Zend\Form\ElementInterface;
use Zend\Form\Element\Collection as ZendCollection;

/**
* Edit Ad Form view class
*/
class Form extends Fieldset {

	/**
	* @see Advertikon\View\Helper\Element::init()
	*/
	public function init() {
		parent::init();
		$this->_element->setAttribute( 'data-error-icon' , $this->_errorIcon );
		$this->_element->setAttribute( 'data-success-icon' , $this->_successIcon );
		$this->_element->setAttribute( 'data-error-msg' , $this->_errorMsg );
		$this->_element->setAttribute( 'data-success-msg' , $this->_successMsg );
	}

	/**
	* Show form input filters structure
	*
	* @return string
	*/
	public function showInputFilters( $inputFilter = null , $indentCount = 0 , $name = 'root' ) {
		$indent = '---';
		$inputFilter = $inputFilter ?: $this->_element->getInputFilter();

		echo str_repeat( $indent , $indentCount ) . $name . '<br>';
		
		if( $inputFilter instanceof InputFilterInterface ) {
			if( $inputFilter instanceof CollectionInputFilter ) {
				$inputFilter = $inputFilter->getInputFilter();
			}
			foreach( $inputFilter->getInputs() as $name => $input ) {
				$indentCount++;
				$this->showInputFilters( $input , $indentCount , $name );
				$indentCount--;
			} 
		}
	}
}
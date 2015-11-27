<?php
/**
* Generic fieldset class
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\Form\Element;

use Advertikon\Form\Element;
use Zend\InputFilter\InputProviderInterface;

class InputProviderElement extends Element implements InputProviderInterface {

	/**
	* @var Array $_spec Input specification 
	*/
	protected $_spec = [ 'required' => false , 'allow_empty' => true ];

	/**
	* @see Zemd\InputFilter\InputProviderInterface::getInputSpecification()
	*/
	public function getInputSpecification() {
		return $this->_spec;
	}

	/**
	* Set input specification
	*
	* @param Array
	*/
	public function setInputSpecification( $spec = [] ) {
		$this->_spec = $spec;
	}

}
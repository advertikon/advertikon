<?php
/**
* Generic select element
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\Form\Element;

use Zend\Form\Element\Select as ZendSelect;
use Advertikon\Exception\InvalidArgument;
use Zend\InputFilter\InputProviderInterface;

class Select extends ZendSelect implements InputProviderInterface {

	/**
	* @see Zend\InputFilter\InputProviderInterface::getInputSpecification()
	*/
	public function getInputSpecification() {
		return [
			'name'			=> $this->getName(),
			'required'		=> false,
			'alow_empty'	=> true,
		];
	}

}
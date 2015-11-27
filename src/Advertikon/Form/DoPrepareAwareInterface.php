<?php
/**
* DoPrepare aware interface
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\Form;

use Zend\Form\Fieldset as ZendFieldset;
use Advertikon\Exception\InvalidArgument;
use Advertikon\App;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

interface DoPrepareAwareInterface {

	/**
	* Construct element name from parents names, if needed
	*/
	public function doPrepare();
}
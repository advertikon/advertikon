<?php
/**
* Generic fieldset class
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
use Advertikon\Form\DoPrepareAwareInterface;

/**
* Gerneric fieldset class
*/
class Fieldset extends ZendFieldset implements DoPrepareAwareInterface{

	/**
	* Class constructor
	*/
	public function __construct( $name = null , $options = null ) {
		parent::__construct( $name , $options ); 
		$this->attributes[ 'type' ] = 'fieldset';
	}

	/**
	* @see Advertikon\Form\DoPrepareAwareInterface::doPrepare()
	*/
	public function doPrepare() {
		foreach( $this->getIterator() as $name => $element ) {
			if( ! preg_match( '/[\]\[]/', $name ) ) {
				$element->setName( $this->getName() . '[' . $name . ']' );
			}
			if( $element instanceof DoPrepareAwareInterface ) {
				$element->doPrepare();
			} 
		}
	}
} 

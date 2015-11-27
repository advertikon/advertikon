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

use Zend\Form\Element\Collection as ZendCollection;
use Advertikon\Form;

class Collection extends ZendCollection {

	/**
	* @see Zend\Form\Element::__construct()
	*/
	public function __construct( $name = null , $options = [] ) { 
		parent::__construct( $name , $options );
		$this->attributes[ 'type' ] = 'fieldset';
	}

	/**
	* Prepare elements names
	*/
	public function doPrepare() {
		foreach( $this->getIterator() as $name => $element ) {
			$element->setName( $this->getName() . '[' . $element->getName() . ']' );
			if( $element instanceof Collection ) {
				$element->doPrepare();
			} 
		}
	}

}
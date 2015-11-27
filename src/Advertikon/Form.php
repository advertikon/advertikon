<?php
/**
* Generic Form class
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon;

use Zend\Form\Form as ZendForm;
use Advertikon\Exception\InvalidArgument;
use Advertikon\Fieldset;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Form\FieldsetInterface;
use Zend\Form\ElementInterface;
use Zend\Debug\Debug;
use Zend\Form\Element\Collection;
use Zend\InputFilter\CollectionInputFilter;
use Zend\Validator\StringLength;
use Zend\Filter\StringTrim;

/**
* Generic Form class
*/
class Form extends ZendForm {

	/**
	* @var string DATA_VALIDATE Attribute name for input validators list
	*/
	const DATA_VALIDATE = 'data-validate';

	/**
	* @var string DATA_VALIDATE Attribute name for input validators list
	*/
	const DATA_FORMAT = 'data-format';

	/**
	* @var string ATTR_SEP Arguments separator
	*/
	const ARG_SEP = ':';

	/**
	* Class constructor
	*/
	public function __construct( $name = null , $options = null ) {
		parent::__construct( $name , $options ); 
		$this->attributes[ 'type' ] = 'form';
	}

	/**
	* @see Zend\Form\Element:prepare()
	*/
	public function prepare() {
		parent::prepare();
		self::workOnElement( $this->getInputfilter() , $this );
	}

	/**
	* Add validators arrtibutes to input element (eg required) 
	*
	* @param Object $inputFilter Instance of Zend\InputFilter\InputFilter|Zend\InputFilter\Input
	* @param Object $element Instance of Zend\Form\Fieldset|Zend\Form\Element
	*/
	static function workOnElement( $inputFilter , $element ) {

		if( $inputFilter instanceof InputInterface ) {
			self::prepareinputFilter( $element , $inputFilter );
			self::addValidators( $element , $inputFilter );
			self::addFilters( $element , $inputFilter ); 
			self::rmAttributes( $element , $inputFilter );
			self::addAttributes( $element , $inputFilter );
		}
		else {

			if( $element instanceof Collection ) {
				//do nothing since we done it already for collection targetElement
				return;
			}

			if( $inputFilter instanceof CollectionInputFilter ) { 
				$inputFilter = $inputFilter->getInputfilter();
			}

			foreach( $inputFilter->getInputs() as $name => $input ) {
				if( $element->has( $name ) ) {
					self::workOnElement( $input , $element->get( $name ) );
				}
			}
		}
	}

	/**
	* Prepare input filter
	*
	* @param Object $element Element to add validators to
	* @param Object $inputFilter Backend input filter to fetch validators from
	*/
	static function prepareinputFilter( $element , $inputFilter ) {
		//remove Required and Not Allow Empty attributes from InputFilter
		if( in_array( $element->getAttribute( 'type' ) , [ 'button' , 'submit' , 'fieldset' ] ) ) {
			$inputFilter->setAllowEmpty( true )->setRequired( false );
		}
	}


	/**
	* Add frontend validators corresponding frontend ones
	*
	* @param Object $element Element to add validators to
	* @param Object $inputFilter Backend input filter to fetch validators from
	*/
	static function addValidators( $element , $inputFilter ) {
		return;
		$validators = [];

		if( ! $inputFilter->allowEmpty() ) {
			$validators[] = 'notEmpty';  
		}

		foreach( $inputFilter->getValidatorChain()->getValidators() as $validator ) {

			if( $validator[ 'instance' ] instanceof StringLength ) {
				$validators[] = 'strLength' . self::ARG_SEP . trim( $element->getAttribute( 'minlength' ) ) . self::ARG_SEP . trim( $element->getAttribute( 'maxlength' ) );
			}
		}

		if( $validators ) {
			$element->setAttribute( self::DATA_VALIDATE , implode( ' ' , $validators ) );
		}
	}

	/**
	* Add frontend filters corresponding frontend ones
	*
	* @param Object $element Element to add validators to
	* @param Object $inputFilter Backend input filter to fetch validators from
	*/
	static function addFilters( $element , $inputFilter ) {
		$filters = [];

		foreach( $inputFilter->getFilterChain()->getFilters() as $filter ) {

			if( $filter instanceof StringTrim ) {
				$filters[] = 'trim';
			}
		}

		if( $filters ) {
			$element->setAttribute( self::DATA_FORMAT , implode( ' ' , $filters ) );
		}
	}

	/**
	* Remove attributes from element
	*
	* @param Object $element Element which parameters to remove
	* @param Object $inputFilter Backend input filter to fetch validators from
	*/
	static function rmAttributes( $element , $inputFilter ) {
		//remove HTML5 validation, it messed up with js validators
		$toRemove = [ 'minlength' , 'required' , ];

		foreach( $toRemove as $attr ) { 
			$element->removeAttribute( $attr );
		}
	}

	/**
	* Add attributes to element
	*
	* @param Object $element Element to which add attributes
	* @param Object $inputFilter Backend input filter to fetch validators from
	*/
	static function addAttributes( $element , $inputFilter ) {
		
	}

	/**
	* Get inputFilter for specific element
	*
	* @param Object $elementToSearch Element for which inputFilter to search
	* @param Object $inputFilter Current inputFilter instance
	* @param Onject $currenElement Current element instance
	* @return Object
	*/
	public function getInputByElement( $elementToSearch , $inputFilter = null , $currentElement = null) {
		$inputFilter = $inputFilter ?: $this->getInputFIlter();
		$currentElement = $currentElement ?: $this;

		if( $inputFilter instanceof CollectionInputFilter ) {
			$inputFIlter = $inputFilter->getInputFilter();
		}

		if( $currentElement === $elementToSearch ) {
			return $inputFilter;
		}

		if( $inputFilter instanceof InputInterface ) {
			return null;
		}

		foreach( $inputFilter->getInputs() as $name => $input ) { 
			if( $currentElement->has( $name ) ) {
				if( $inputInSearch = $this->getInputByElement( $elementToSearch , $input , $currentElement->get( $name ) ) ) {
					return $inputInSearch;
				}
			}
		}
		return null;
	} 
}

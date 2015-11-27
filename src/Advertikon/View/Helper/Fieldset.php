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
use Advertikon\View\Helper\Label;
use Zend\Form\FieldsetInterface;
use Zend\Form\ElementInterface;
use Zend\Form\Element\Collection as ZendCollection;
use IteratorAggregate;
use Zend\Stdlib\PriorityList;

/**
* Form element view class
*/
class Fieldset extends Element implements IteratorAggregate {

	/**
	* @var string $_children Element subcontent
	*/
	protected $_children;

	/**
	* Class constructor
	*/
	public function __construct( $element = null ) {
		parent::__construct( $element );
		if( $element ) {
			if( ! $element->getAttribute( 'id' ) ) {
				$element->setAttribute( 'id' , $element->getAttribute( 'name' ) );
			}
			$element->removeAttribute( 'name' );
		}
		$this->_children = new PriorityList;
		$this->_children->isLIFO( false );
	}

	/**
	* @see Advertikon\View\Helper\Element::reset()
	*/
	public function _reset() {
		parent::_reset();
		$this->_children->clear();
	}

	/**
	* Get list of children
	*
	* @return Array
	*/
	public function getChildren() {
		return $this->_children;
	}

	/**
	* Populate element content
	*
	* @param string $element HTML representation or View Helper object
	* @param integer $priority Element priority in list
	* @throws InvalidArgument If passed argument not a string
	* @return self
	*/
	public function add( $element , $priority = null ) {
		if( ! $element instanceof Element && ! is_string( $element ) ) {
			throw new InvalidArgument( sprintf( '%s: Argument must be instance of "Advertikon\View\Helper\Element" or string, instance of %s given instead' , __METHOD__ , get_class( $element) ) );
		}
		if( $element instanceof Element && ! $element->hasElement() ) {
			//throw new InvalidArgument( sprintf( '%s: Form helper must has Form Element setted before adding to children collection'  , __METHOD__ ) );
		}

		if( is_string( $element ) ) {
			$name = mt_rand();
		}
		else {
			$name = $element->hasElement() && $element->getElement()->getName() ? $element->getElement()->getName() : mt_rand();
		}

		$this->_children->insert( $name , $element , $priority );
		if( ! $this->isExistsInDocument( $this ) ) {
			self::$document[] = $this;
		}
		if( ! is_string( $element ) ) {
			$element->setParent( $this );
		} 
		return $this;
	}

	/**
	* Render element children
	*
	* @return string
	*/
	protected function _renderChildren() {
		$str = '';
		foreach( $this->_children as $child ) {
			if( is_string( $child ) ) {
				$str .= $child;
			}
			else {
				$str .= $child->toString();
			}
		}
		return $str;
	}

	/**
	* @see Advertikon\View\Helper\Element
	*/
	public function openTag() { 
		$str = parent::openTag();
		if( $this->_element->getLabel() ) { 
			$str .= self::newLine( '<legend>' . $this->translate( $this->_element->getLabel() ) . '</legend>' );
		}
		return $str;
	}

	/**
	* @see Advertikon\View\Helper\Element::render()
	*/
	public function render() {
		$str = '';
		$str .= $this->openTag();
		$str .= $this->_renderChildren();
		$str .= $this->closeTag();
		return $str;
	}

	/**
	* Get row helper
	*
	* @return Object
	*/
	public function getRowHelper() {
		return new Row;
	}

	/**
	* Get fieldset helper
	*
	* @return Object
	*/
	public function getFieldsetHelper() {
		return new Fieldset;
	}

	/**
	* Get cllection helper
	*
	* @return Object
	*/
	public function getCollectionHelper() {
		return new Collection;
	}

	/**
	* Parse given element and populate helper
	*
	* @param Object $element Instance of Zend\Form\Element, to be parsed
	* @param Object|null $helper Parent helper element, it exists
	* @throws Advertikon\Exception\InvalidArgument On invalid object to be parsed
	* @return self
	*/
	public function parse( $element , $helper = null ) {
		if( ! $element instanceof FieldsetInterface ) {
			throw new InvalidArgument( sprintf( '%s: Parsed element must inplement Zend\Form\FieldsetInterface, got %s element' , __METHOD__ , get_class( $element )  ) ); 
		}
		$this->setElement( $element );
		foreach( $element->getIterator() as $name => $item ) {
			if( $item instanceof ZendCollection ) {
				$this->getCollectionHelper()->parse( $item , $this );
			}
			elseif( $item instanceof FieldsetInterface ) {
				$this->getFieldsetHelper()->parse( $item , $this );
			}
			elseif( $item instanceof ElementInterface ) {
				$this->getRowHelper()->parse( $item , $this );
			}
		}
		if( $helper ) {
			$helper->add( $this );
		}
		return $this;
	}

	/**
	* Show childrens of all inner elements after parsing
	*
	* @param Object|null $element Element to be looked at
	* @param Integer $indent Indent
	* @return string
	*/
	public function showChildren( $element = null  , $indent = 0 ) {

		$element = $element ?: $this;

		$indentStr = '|------';

		if( is_string( $element ) ) {
			echo str_repeat( $indentStr , $indent ). 'String' . '<br>';
		}
		else {
			echo str_repeat( $indentStr , $indent ). get_class( $element ) . ' - ' . get_class( $element->_element ) . '<br>';
		}

		if( $element instanceof Fieldset ) {
			foreach( $element->_children as $item ) {
				$indent++;
				$this->showChildren( $item , $indent );
				$indent--;
			}
		}
	}

	/**
	* Get iterator
	*/
	public function getIterator() {
        return $this->_children->getIterator();
    }

}
?>
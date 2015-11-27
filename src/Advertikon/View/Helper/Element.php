<?php
/**
* Form element part view
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Advertikon\App;
use Zend\Escaper\Escaper;
use Advertikon\Exception\InvalidArgument;
use Advertikon\Exception;
use Advertikon\Form\Element as FormElement;
use Zend\Form\Element as ZendElement;
use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Zend\Debug\Debug;
/**
* Form Element part class
*/
class Element extends AbstractHelper {

	/**
	* @var boolean $_canHasFeedBack Whether to set feedback icon upon input element
	*/
	protected $_canHasFeedBack = null;

	/**
	* @var boolean $_doTranslate Whether to translate form captions
	*/
	protected $_doTranslate = true;

	/**
	* @var Object $escaper Escaper instance
	*/
	public $escaper;

	/**
	* @var Array $_validTypes Valid Form control ttpes
	*/
	protected $_validTypes = [
		'text',
		'textarea',
		'fieldset',
		'button',
		'form',
		'label',
		'submit',
		'select',
	];

	/**
	*
	*/
	protected $_validAttributes = [
		'accept',			//Hint for expected file type in file upload controls
		'alt',				//Replacement text for use when images are not available
		'autocomplete',		//Hint for form autofill feature
		'autofocus',		//Automatically focus the form control when the page is loaded
		'checked',			//Whether the command or control is checked
		'dirname',			//Name of form field to use for sending the element's directionality in form submission
		'disabled',			//Whether the form control is disabled
		'form',				//Associates the control with a form element
		'formaction',		//URL to use for form submission
		'formenctype',		//Form data set encoding type to use for form submission
		'formmethod',		//HTTP method to use for form submission
		'formnovalidate',	//Bypass form control validation for form submission
		'formtarget',		//Browsing context for form submission
		'height',			//Vertical dimension
		'inputmode',		//Hint for selecting an input modality
		'list',				//List of autocomplete options
		'max',				//Maximum value
		'maxlength',		//Maximum length of value
		'min',				//Minimum value
		'minlength',		//Minimum length of value
		'multiple',			//Whether to allow multiple values
		'name',				//Name of form control to use for form submission and in the form.elements API
		'pattern',			//Pattern to be matched by the form control's value
		'placeholder',		//User-visible label to be placed within the form control
		'readonly',			//Whether to allow the value to be edited by the user
		'required',			//Whether the control is required for form submission
		'size',				//Size of the control
		'src',				//Address of the resource
		'step',				//Granularity to be matched by the form control's value
		'type',				//Type of form control
		'value',			//Value of the form control
		'width',			//Horizontal dimension
		'title', 
	];

	/**
	* @var Object $_element Instance of Zend\Form\Element, Element to be rendered
	*/
	protected $_element;

	/**
	* Whether element can has status (eg success)
	*/
	protected $_canHasStatus = null;

	/**
	* @var string $_errorIcon Feedback error icon
	*/
	protected $_errorIcon = 'glyphicon-warning-sign';

	/**
	* @var string $_successIcon Feedback success icon
	*/
	protected $_successIcon = 'glyphicon-ok';

	/**
	* @var string $_errorMsg Aria feedback errro massage
	*/
	protected $_errorMsg = '';

	/**
	* @var string $_successMsg Aria feedback success massage
	*/
	protected $_successMsg = '';

	/**
	* @var integer $_eol New line
	*/
	static $eol = "\n";

	/**
	* @var integet $_indent Indent symbol
	*/
	static $indent = "   ";

	/**
	* @var integer $_indCount Indent count
	*/
	static $indCount = 0;

	/**
	* @var $_withIndent Whether to formatt output with indents
	*/
	static $withIndent = true;

	/**
	* @var Object $_parent Parent object instance
	*/
	protected $_parent;

	/**
	* @var Array $document Document structure
	*/
	static $document = [];

	/**
	* @var integer $documentId Element ID within document
	*/
	public $documentId;

	/**
	* @var Array $_inlineCache Inline (inmemory) cache
	*/
	protected $_inlineCache = [];

	/**
	* @var integer $_classId Class identificator
	*/
	protected $_classId;

	/**
	* Class constructor
	*/
	public function __construct( $element = null ) {
		$this->escaper = new Escaper;
		if( $element ) {
			$this->setElement( $element );
		}
		$this->_classId = mt_rand();
	}

	/**
	* Get class identifier
	*
	* @return integer
	*/
	public function getClassId() {
		return $this->_classId;
	}

	
	/**
	* Walk document tree
	*
	* @param Calable $callback Callback to be called on each document element
	* @param Array|Object $document Document to walk on
	* @return boolean
	*/
	protected function _documentWalk( $callBack , $document = null ) {
		$document = $document ?: self::$document;
		if( $document instanceof Element ) {
			if( ! is_callable( $callBack ) ) {
				throw new InvalidArgument( sprintf( '%s: Passed invalid callback' ) );
			}
			if( ( $resp = call_user_func_array( $callBack, [ $document ] ) ) !== null ) {
				return $resp;
			}
		}
		if( $document instanceof Fieldset || is_array( $document ) ) {
			foreach( $document as $node ) {
				if( ( $resp = $this->_documentWalk( $callBack , $node ) ) !== null ) {
					return $resp;
				}
			}
		}
		return null;
	}

	/**
	* Check whether element already exists in document structure
	*
	* @param Object $element Element to search for
	* @return boolean
	*/
	public function isExistsInDocument( $element ) {
		return $this->_documentWalk( function( $node ) use( $element ) {
			if( $node === $element ) {
				return true;
			}
		} );
	}

	/**
	* Init the object
	*/
	public function init() {
		$this->_errorMsg = $this->translate( 'Error' );
		$this->_successMsg = $this->translate( 'Success' );
	}

	/**
	* Set element
	*
	* @param Object $element Zend\Form\Element instance
	* @throws Advertikon\Exception\InvalidArgument If element is not instance of Zend\Form\Element
	* @return self
	*/
	public function setElement( $element ) {
		if( ! is_a( $element , 'Zend\Form\ElementInterface' ) ) {
			throw new InvalidArgument( sprintf( '%s: Argument must inplement Zend\Form\ElementInterface, class %s given instead' , __METHOD__ , get_class( $element ) ) );
		}
		//$this->_reset();
		$this->_element = $element;
		$this->init();
		return $this;
	}

	/**
	* Whether object has Form Element installed
	*
	* @return boolean
	*/
	public function hasElement(){
		return $this->_element instanceof ZendElement;
	}

	/**
	* Get form element for current view helper
	*
	* @return Object|null
	*/
	public function getElement(){
		return $this->_element;
	}

	/**
	* Reset view helper element
	*/
	protected function _reset() {
		$this->_canHasFeedBack = null;
		$this->_canHasStatus = null;
		$this->_element = null;
		$this->_parent = null;
	}

	/**
	* Clone object
	*/
	public function __clone(){
		$this->_reset();
	}

	/**
	* Clone renderer
	*
	* @return Object
	*/
	public function getNew( $element = null ) {
		$new = clone $this;
		if( $element ) {
			$new->setElement( $element );
		}
		return $new;
	}

	/**
	* Assemble form control opening tag
	*
	* @param Object $input Form control element
	* @param Array $attr Additional attributes to be set on element
	* @return string
	*/
	protected function assembleOpenTag( $attr = [] ) {

		if( ! $this->_element->hasAttribute( 'id' ) ) {
			$this->_element->setAttribute( 'id' , $this->_element->getName() );
		}

		if( isset( $attr[ 'class' ] ) ) {
			$class = (array)$attr[ 'class' ];
			if( $this->_element->hasAttribute( 'class' ) ) {
				$class = array_merge( $class , explode( ' ' , $this->_element->getAttribute( 'class' ) ) );
			}
			$this->_element->setAttribute( 'class' , implode( ' ' , $class ) );
		}

		if( $this->_canHasFeedback() ) {
			$this->_element->setAttribute( 'aria-describedby' , ( $this->_element->getAttribute( 'id' ) ?: $this->_element->getName() ) . '-feedback' );
		}


		$type = $this->_getType();
		$tagName = $this->_getTagName( $type );
		if( $tagName == $type ) {
			$this->_element->removeAttribute( 'type' );
			$this->_element->setOption( 'tag' , $tagName );
		}
		$str = sprintf( '<%1$s %2$s' ,
						$tagName,
						$this->getAttributes()
						 );
		if( $tagName == $type ) {
			$str .= '>';
		}
		else {
			$str .= ' value="' . $this->escaper->escapeHtmlAttr( (string)$this->_element->getValue() ) . '">';
		}
		return $str;
	}

	/**
	* Get form control tag name depend on control type
	*
	* @param String $type Form control type
	* @return String
	*/
	protected function _getTagName() {
		$type = strtolower( $this->_element->getAttribute( 'type' ) ?: $this->_element->getOption( 'tag' ) );
		switch( $type ) {
			case 'button' :
				if( ! $this->_element->getOption( 'asButton' ) ) {
					return 'input';
				}
			case 'label' :
			case 'textarea' :
			case 'fieldset' :
			case 'form' :
			case 'select' :
				return $type;
			break;
			default:
				return 'input';
			break;
		}
	}

	/**
	* Get form control type
	*
	* @param Object $input
	* @return string
	* @throws Advertikon\Exception\InvalidArgument When Input in not instance of Zend\Form\Element
	*/
	protected function _getType() {
		$type = $this->_element->getAttribute( 'type' );
		if( ! $type || ! in_array( $type , $this->_validTypes ) ) {
			$type = 'text';
		}
		return $type;
	}

	/**
	* Whether form input can has status (eg success)
	*
	* @param Object $input Form input element
	* @return boolean
	*/
	protected function _canHasStatus() {

		if(  $this->_canHasStatus === null ) {
			try{
				switch( $this->_element->getAttribute( 'type' ) ?: $this->_element->getOption( 'tag' ) ) {
					case 'text' :
					case 'textarea' :
					case 'select' :
					break;
					default:
						throw new Exception;
				}
				if( $this->_element->getOption( 'canHasStatus' ) === false ) {
					throw new Exception;
				}
				if( ! $this->_element->getValue() && ! $this->_element->getMessages() ) {
					throw new Exception;
				}
				$this->_canHasStatus = true;
			}
			catch( Exception $e ) {
				$this->_canHasStatus = false;
			}
		}
		return $this->_canHasStatus;
	}

	/**
	* Whether input can has feedback
	*
	* @param Object $input Form input
	* @return boolean
	*/
	protected function _canHasFeedback() {
		if( $this->_canHasFeedBack === null ) {
			try {
				switch( $this->_element->getAttribute( 'type' ) ?: $this->_element->getOption( 'tag' ) ) {
					case 'text' :
					case 'textarea' :
					case 'select' :
					break;
					default :
						throw new Exception;
				}
				if( $this->_element->getOption( 'canHasFeedBack' ) === false ) {
					throw new Exception;
				}
				if( ! $this->_canHasStatus() ) {
					throw new Exception;
				}
				$this->_canHasFeedBack = true;
			}
			catch( Exception $e ) {
				$this->_canHasFeedBack = false;
			}
		}
		return $this->_canHasFeedBack;
	}

	/**
	* Translate text
	*
	* @param string $text Text to be translated
	* @return string
	*/
	public function translate( $text ) {
		if( ! is_string( $text ) ) {
			throw new InvalidArgument( sprintf( '%s: Argument must be string, %s given instead' , __METHOD__ , gettype( $text ) ) );
		}
		if( $this->_doTranslate && $this->_element->getOption( 'translate' ) !== false ) {
			$text = App::getModule()->translate( $text );
		}
		return $text;
	}

	/**
	* Get element attributes
	*
	* @param Object|Array $attrOrElem Form inpuut element or attributes array
	* @return string
	*/
	public function getAttributes() {
		$attr = [];
		foreach( $this->_element->getAttributes() as $name => $value ) {
			if( ! is_scalar( $value ) ) {
				$value = '';
			}
			$attr[ $name ] = (string)$value;
		}

		$attrStr = [];
		foreach( $attr as $name => $val ) {
			$attrStr[] = $name . '="' . $this->escaper->escapeHtmlAttr( $val ) . '"';
		}
		return implode( ' ' , $attrStr );
	}

	/**
	* Class stringifier
	*/
	public function __toString() {
		try{
			if( ! $this->hasElement() ) {
				throw new InvalidArgument( sprintf( '%s: View helper "%s" should have form element setted before rendering it' , __METHOD__ , get_class( $this )  )  );
			}
			return $this->render();
		}
		catch( Exception $e ) {
			return sprintf( 'Exception: %s, thrown in file %s, in line %s' , $e->getMessage() , $e->getFile() , $e->getLine() );
		}
	}

	public function toString( $element = null ) {
		if( $element ) {
			$this->setElement( $element ); 
		}
		return $this->__toString();
	}

	/**
	* Render element open tag
	*
	* @return string
	*/
	public function openTag() {
		$str = '';
		$str .= self::newLine( $this->assembleOpenTag() );
		self::$indCount++;
		return $str;
	}

	/**
	* Render element close tag
	*
	* @return string
	*/
	public function closeTag() {
		self::$indCount--;
		$str = self::newLine( sprintf( '</%s>' , ( $this->_element->getAttribute( 'type' ) ?: $this->_element->getOption( 'tag' ) ) ) );
		return $str;
	}

	/**
	* Wrap line in new line with indent
	*
	* @param string $string Input string
	* @return string
	*/
	static function newLine( $string ) {
		if( ! self::$withIndent ) {
			return $string;
		}
		return str_repeat( self::$indent , self::$indCount >= 0 ? self::$indCount : 0 ) . $string . self::$eol;
	}

	/**
	* Render element
	*
	* @return string
	*/
	public function render() {
		$str = '';
		$str .= $this->openTag();
		if( $this->_element->getValue() ) {
			$str .= $this->_element->getValue();
		}
		$str .= $this->closeTag();
		$str .= $this->error();
		return $str;
	}

	/**
	* Create label element for specific input
	*
	* @return Object 
	*/
	public function createLabel() {
		$label = new FormElement( 'label' );
		$label->setAttribute( 'type' , 'label' );
		$label->setValue( $this->_element->getLabel() );
		$attributes = [];
		foreach( $this->_element->getLabelAttributes() as $attr ) {
			if( $this->isValidAttribute( $attr ) ) {
				$attributes[] = $attr;
			}
		}
		if( ! $this->_isSwich() ) {
			$class = '';
			if( isset( $attributes[ 'class' ] ) ) {
				$class = $attributes[ 'class' ] . ' ';
			}
			$attributes[ 'class' ] = $class . 'control-label';

		}
		$label->setAttributes( $attributes );
		$label->setOptions( $this->_element->getLabelOptions() );
		if( ! $label->getOption( 'append' ) && ! $label->getOption( 'prepend' ) ) {
			$label->setOption( 'prepend' , true );
		}
		$label->setAttribute( 'for' , ( $this->_element->getAttribute( 'id' ) ?: $this->_element->getName() ) );
		return $label;
	}

	/**
	* Add class names to element
	*
	* @param string|array
	* @return self
	*/
	public function addClass( $class ) {
		if( is_string( $class ) ) {
			$class = explode( ' ' , $class );
		}
		if( ! is_array( $class ) ) {
			throw new InvalidArgument( sprintf( '%s: Class name must be string or array, %s given instead' , __METHOD__ , gettype( $class ) ) );
		}
		if( ! $this->_element ) {
			throw new InvalidArgument( sprintf( '%s: Element must be set prior to get access to attributes' , __METHOD__ ) );
		}
		$class = array_merge( explode( ' ' , $this->_element->getAttribute( 'class' ) ) , $class );
		$this->_element->setAttribute( 'class' , trim( implode( ' ' , $class ) ) );
		return $this;
	}

	/**
	* Check whether attribut is valid HTML attibute
	*
	* @param string $attr Attribute name to be checked
	* @return boolean
	*/
	public function isValidAttribute( $attr ) {
		if( in_array( $attr , $this->_validAttributes ) || substr( $attr , 0 , 5 ) === 'data-' ) {
			return true;
		}
		return false;
	}

	/**
	* Render error caption
	*
	* @param Object $input Form Input element
	* @return string
	*/
	public function error() {
		$str = '';
		if( $this->_canHasFeedback() ) {
			$class = [ 'glyphicon' , 'form-control-feedback' , ];
			if( $this->_element->getMessages() ) {
				$class[] = $this->_errorIcon;
				$feedbackText = $this->_errorMsg;
			}
			else {
				$class[] = $this->_successIcon;
				$feedbackText = $this->_successMsg;
			}
			$str .= self::newLine( sprintf( '<span class="%s" aria-hidden="true">' ,implode( ' ' , $class ) ) );

			$str .= self::newLine( sprintf( '</span><span id="%s-feedback" class="sr-only">(%s)</span>',
					( $this->_element->getAttribute( 'id' ) ?: $this->_element->getName() ),
					$feedbackText
				) );
			
		}
		if( $msg = $this->_element->getMessages() ) {
			foreach( $msg as $line ) {
				//need to translate empty field error message, since Zend do not translate it
				$str .= self::newLine( sprintf( '<span class="help-block">%s</span>' , $this->translate( $line ) ) );
			}
		}
		return $str;
	}

	/**
	* Whether input is radio or checkbox
	*
	* @param Object $input Form input
	* @return boolean
	*/
	protected function _isSwich() {
		switch( $this->_getType() ) {
			case 'radio' :
			case 'checkbox' :
				return true;
			default :
				return false;
		}
	}

	/**
	* Show element structure
	*
	* @return striing
	*/
	public function showElements( $element = null , $indentCount = 0 ) {
		$indent = '----';
		$element = $element ?: $this->_element;
		if( $element instanceof FieldsetInterface ) {
			echo str_repeat( $indent , $indentCount ) . '<b>' . ( $element->getName() ?: $element->getAttribute( 'id' ) ) . ':</b><br>';
		}
		else{
			echo str_repeat( $indent , $indentCount ) . ( $element->getName() ?: $element->getAttribute( 'id' ) ) . '<br>';
		}
		if( $element instanceof FieldsetInterface ) {
			foreach( $element->getIterator() as $child ) {
				$indentCount++;
				$this->showElements( $child , $indentCount );
				$indentCount--;
			}
		}
	}

	/**
	* Set parent element for elements in group (eg fieldset, row)
	*
	* @param Object $element Parent object
	* @return self
	*/
	public function setParent( $element ) {
		$this->_parent = $element;
		return $this;
	}

	/**
	* Get parent element
	*
	* @return Object|null
	*/
	public function getParent() {
		return $this->_parent;
	}


}
?>
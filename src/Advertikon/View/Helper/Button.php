<?php
/**
* Form button part view
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\View\Helper;

use Advertikon\App;
use Advertikon\Exception\InvalidArgument;

/**
* Form button part class
*/
class Button extends Input {

	/**
	* @see Advertikon\View\Helper\Element
	*/
	public function init() {
		$class = [ 'btn' , ];
		switch( $this->_element->getOption( 'type' ) ) {
			case 'primary' :
				$class[] = 'btn-primary';
			break;
			case 'success' :
				$class[] = 'btn-success';
			break;
			case 'info' :
				$class[] = 'btn-info';
			break;
			case 'warning' :
				$class[] = 'btn-warning';
			break;
			case 'danger' :
				$class[] = 'btn-danger';
			break;
			case 'link' :
				$class[] = 'btn-link';
			break;
			default:
				$class[] = 'btn-default';
		}
		$this->addClass( $class );
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


		$type = $this->_element->getAttribute( 'type' );
		if( $this->_element->getOption( 'asButton' ) ) {
			$tagName = 'button';
		}
		else {
			$tagName = 'input';
		}

		$this->_element->setOption( 'tag' , $tagName );
		$str = sprintf( '<%1$s %2$s' ,
						$tagName,
						$this->getAttributes()
						 );
		if( $tagName == 'button' ) {
			$str .= '>';
		}
		else {
			$str .= ' value="' . $this->escaper->escapeHtmlAttr( (string)$this->_element->getValue() ) . '">';
		}
		return $str;
	}
}
?>
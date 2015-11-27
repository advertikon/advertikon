<?php
/**
* Form Row helper
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\View\Helper;

use Advertikon\App;
use Advertikon\Exception\InvalidArgument;
use Zend\Form\ElementInterface;
use Zend\Form\Element\Select as FormSelect;
use Advertikon\Form\Element as FormElement;

/**
* Form label helper
*/
class Row extends Fieldset {

	/**
	* @var integer $_groupId Group ID for grouped elements (consists of two or three form inputs)
	*/
	protected $_groupId = null;

	/**
	* @var boolean $_nextInGroup Mark view helper element as next (second, third) in group
	*/
	protected $_nextInGroup = true;

	/**
	* Get group ID
	*
	* @return integer
	*/
	public function getGroupId() { 
		return $this->_groupId;
	}

	/**
	* Get group element for input (eg ROW element)
	*
	* @param integer $groupId Group ID
	* @return Onject|null
	*/
	public function getGroup( $groupId ) {
		$cacheName = 'get_group';
		if( ! isset( $this->_inlineCache[ $cacheName ] ) ) {
			$this->_inlineCache[ $cacheName ] = [];
		}

		if( isset( $this->_inlineCache[ $cacheName ][ $groupId ] ) ) {
			return $this->_inlineCache[ $cacheName ][ $groupId ];
		}

		$res = $this->_documentWalk( function( $node ) use( $groupId ) {
			if( ! $node instanceof Row ) {
				return null;
			}
			if( $node->getGroupId() == $groupId ) {
				return $node;
			}
		} );

		if( $res !== null ) {
			$this->_inlineCache[ $cacheName ][ $groupId ] = $res;
			return $res;
		}
		return null;
	}

	/**
	* @see Advertikon\View\Helper\Element::setElement()
	*/
	public function setElement( $element ) {
		$show = false;
		if( $show )echo '<b>Set Element: ' . $this->_classId . '</b><br>';
		if( $groupId = $element->getOption( 'group_id' ) ) {
			if( $show )echo 'Group iD: ' . $groupId . '<br>';
			/* Group is ROW Element. If gruop does not exist
				then element is the first in the group */
			$group = $this->getGroup( $groupId );
			if( ! $group ) {  
				$this->_groupId = $groupId;
				$group = $this;
				$this->_nextInGroup = false;
				if( $show )echo 'Group is new, ID: ' . $this->getGroupId() . '<br>';
			}
			else {
				if( $show )echo 'find group: ' . get_class( $group ) . ': "' . $group->getClassId() . '"<br>';
			}

			if( $element->getOption( 'isMainInGroup' ) ) {
				if( $show )echo 'Main in group<br>';
				if( $group === $this ) {
					if( $show )echo 'Group is this<br>';
					parent::setElement( $element );
					if( $show )echo 'Set Element on parent class<br>';
					if( $group->_shouldCreateLabel() ) {
						$label = $group->createLabel();
						if( $show )echo 'Create label<br>';
						$group->add( new Label( $label ) , 1 );
						if( $show )echo 'Add label to group<br>';
					}
					$group->add( Input::getElementHelper( $element ) );
					if( $show )echo 'Add input element to group<br>';
				}
				else {
					if( $show )echo 'Call set element on group<br>';
					$group->setElement( $element ); 
				}
			}
			else {
				$group->add( Input::getElementHelper( $element ) );
				if( $show )echo 'Add input element to group<br>';
			}
		}
		else {
			$this->_nextInGroup = false;
			parent::setElement( $element );
			if( $this->_shouldCreateLabel() ) {
				$label = $this->createLabel();
				$this->add( new Label( $label ) );
			}
			$this->add( Input::getElementHelper( $element ) );
		}
	}

	/**
	* @see Advertikon\View\Helper\Element::openTag()
	*/
	public function openTag(){

		$class = [ 'form-group' , ];

		if( $this->_canHasStatus() ) {
			if( $this->_element->getMessages() ) {
				$class[] = 'has-error';
			}
			else if( $this->_element->getValue() ){
				$class[] = 'has-success';
			}
		}

		if( $this->_canHasFeedback() ) {
			$class[] = 'has-feedback';
		}

		$str = self::newLine( sprintf( '<div class="%s">' , implode( ' ' , $class ) ) );
		self::$indCount++;
		if( $wrapper = $this->getWrapper() ) {
			$str .= self::newLine( $wrapper );
			self::$indCount++;
		}
		return $str;
	}

	/**
	* @see Advertikon\View\Helper\Element::closeTag()
	*/
	public function closeTag() {
		$str = '';
		if( $wrapper = $this->getWrapper() ) {
			self::$indCount--;
			$str .= $this->_wrapperClose( $wrapper ); 
		}
		if( $this->_element->getOption( 'group_id' ) ) { 
			$str .= $this->error();
		}
		self::$indCount--;
		$str .=  self::newLine( '</div>' );
		return $str;
	}

	/**
	* @see Advertikon\View\Helper\Fieldset::parse()
	*/
	public function parse( $element , $helper = null ) {
		if( ! $element instanceof ElementInterface ) {
			throw new InvalidArgument( sprintf( '%s: Parsed element must inplement Zend\Form\ElementInterface, got %s element' , __METHOD__ , get_class( $element )  ) ); 
		}
		$this->setElement( $element );
		if( $helper && ! $this->_nextInGroup ) { 
			if( false )echo 'Add row: "' . $this->_classId . '" to parent element<br>';
			$helper->add( $this );
		}
		return $this;
	}

	/**
	* Whether element can has label
	*
	* @return boolean
	*/
	protected function _shouldCreateLabel() {
		if( $this->_element instanceof FormSelect ) {
			if( $this->_element->getAttribute( 'type' ) == 'button' ) {
				return false;
			}
		}
		else if( $this->_element->getOption( 'groupName' ) == FormElement::INPUT_SINGLE_DROPDOWN ) {
			return false;
		}
		return true;
	}

	/**
	* Get rwapper for row content
	*
	* @return string|null
	*/
	public function getWrapper() {
		if( $this->_groupId ) {
			if( $mainGroupElement = $this->getMainGroupElement() ) {
				switch ( $mainGroupElement->getElement()->getOption( 'groupName' ) ) {
					case FormElement::INPUT_SINGLE_DROPDOWN :
						return '<div class="input-group">';
					break;
				}
			}
		}
	}

	/**
	* Get wrapper closing tag(s)
	*
	* @param string $wrapper Row content wrapper
	* @return string
	*/
	protected function _wrapperClose( $wrapper ) {
		preg_match_all( '#<([\S]+)#', $wrapper , $m );
		$close = '';
		for( $i = count( $m[ 1 ] ) - 1; $i >= 0; --$i ) {
			$close .= '</' .$m[ 1 ][ $i ] . '>';
		}
		return $close;
	}

	/**
	* Get main element for specific input group
	*
	* @return Object|null
	*/
	public function getMainGroupElement(){
		foreach( $this as $child ) {
			if( $child->getElement()->getOption( 'isMainInGroup' ) ) {
				return $child;
			}
		}
		return null;
	}
}
?>
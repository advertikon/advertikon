<?php
/**
* Form single dropdown button helper
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
* Form single dropdown button class
*/
class ButtonSingleDropdownInputGroup extends Button {


	/**
	* @see Advertikon\View\Helper\Element::render()
	*/
	public function render() {
		$this->addClass( 'dropdown-toggle' );
		$this->_element->setAttributes( [
										'aria-haspopup'	=> 'true',
										'aria-expanded'	=> 'false',
										//'tabindex'		=> '-1', 
										] );
		if( $this->_element->getOption( 'dropdown' ) == 'custom' ) {
			$this->_element->setAttribute( 'data-toggle' , 'custom' );
		}
		else {
			$this->_element->setAttribute( 'data-toggle' , 'dropdown' );
		}

		$str = self::newLine( '<div class="input-group-btn">' );
		self::$indCount++;
		$str .= parent::openTag();
		$caret = '';
		if( $label = $this->_element->getLabel() ) {
			$caret .= $this->translate( $label );
		}
		$caret .= '<span class="caret"></span>';
		$str .= self::newLine( $caret );
		$str .= parent::closeTag();
		$str .= self::newLine( '<div class="dropdown-menu">' );
		self::$indCount++;
		$count = 0;
		$accordionId = $this->_element->getAttribute( 'id' ) . '-accordion';
		$str .= self::newLine( sprintf( '<div id="%s" class="panel-group" role="tablist">' ,
			$accordionId
			 ) );
		self::$indCount++;
		foreach( $this->_element->getValueOptions() as $value => $text ) {

			$pannelHeadingId = $this->_element->getAttribute( 'id' ) . '-heading-' . $count;
			$pannelCollapseId = $this->_element->getAttribute( 'id' ) . '-panel-' . $count;

			$str .= self::newLine( '<div class="panel panel-defult">' );
			self::$indCount++;
			if( is_array( $text ) ) {
				if( isset( $text[ 'options' ] ) ) {
					$str .= self::newLine( sprintf( '<div id="%s" class="panel-heading" role="tab">' ,
						$pannelHeadingId
						 ) );
					self::$indCount++;
					$str .= self::newLine( '<h4 class="panel-title">' );
					self::$indCount++;
					$str .= self::newLine( sprintf( '<a data-toggle="collapse" data-parent="#%1$s" href="#%2$s" aria-expanded="false" aria-controls="%2$s">%3$s</a>',
						$accordionId,
						$pannelCollapseId,
						$this->translate( $value )
					) );
					self::$indCount--;
					$str .= self::newLine( '</h4>' ); 
					self::$indCount--;
					$str .= self::newLine( '</div>' );//<-- .panel-heading
					$str .= self::newLine( sprintf( '<div id="%s" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" aria-labelledby="%s" >' ,
						$pannelCollapseId,
						$pannelHeadingId
						 ) );
					self::$indCount++;
					$str .= self::newLine( '<ul class="list-group">' );
					self::$indCount++;

					$disabled = isset( $text[ 'disabled' ] ) ? (array)$text[ 'disabled' ] : [];
					foreach( $text[ 'options' ] as $subValue => $subText ) {
						$str .= self::newLine( sprintf( "<li class='list-group-item%s'><a href='#' data-value='%s'>%s</a></li>",
							in_array( $subValue , $disabled ) ? ' disabled' : '',
							$this->escaper->escapeHtmlAttr( $subValue ),
							$this->translate( $subText )
						) );
					}
					self::$indCount--;
					$str .= self::newLine( '</ul>' );
					self::$indCount--;
					$str .= self::newLine( '</div>' );//<- .pannel-collapse
				}
			}
			else {
					$str .= self::newLine( '<div class="panel-heading" role="tab">' );
					self::$indCount++;
					$str .= self::newLine( '<h4 class="panel-title">' );
					self::$indCount++;
					$str .= self::newLine( sprintf( '<a href="#%s" value="%s" >%s</a>' , 
						$this->_classId . '-' . $count,
						$this->escaper->escapeHtmlAttr( $value ),
						$this->translate( $text )
					) );
					self::$indCount--;
					$str .= self::newLine( '</h4>' ); 
					self::$indCount--;
					$str .= self::newLine( '</div>' );//<-- .panel-heading
			}
			self::$indCount--;
	 		$str .= self::newLine( '</div>' );//<- .panel

	 		$count++;
		}
 		self::$indCount--;
 		$str .= self::newLine( '</div>' );//<-- .panel-group
 		self::$indCount--;
 		$str .= self::newLine( '</div>' );//<-- .dropdown-menu
 		self::$indCount--;
 		$str .= self::newLine( '</div>' );//<-- .input-group-button
 		return $str;
	}
}
?>
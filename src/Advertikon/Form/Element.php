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

use Zend\Form\Element as ZendElement;

class Element extends ZendElement {

	/**
	* @var integer INPUT_SINGLE_DROPDOWN Input group with single dropdown button
	*/
	const INPUT_SINGLE_DROPDOWN = 1;

	/**
	* @var integer BUTTON_SINGLE_DROPDOWN Button group with single dropdown button
	*/
	const BUTTON_SINGLE_DROPDOWN = 2;

	/**
	* @var string ELEMENT_TYPE_DANGER  Elemennt type danger
	*/
	const ELEMENT_TYPE_DANGER = 'danger';

	/**
	* @var string ELEMENT_TYPE_WArNING  Elemennt type warning
	*/
	const ELEMENT_TYPE_WARNING = 'warning';

	/**
	* @var string ELEMENT_TYPE_PRIMARY  Elemennt type primary
	*/
	const ELEMENT_TYPE_PRIMARY = 'primary';

	/**
	* @var string ELEMENT_TYPE_PRIMARY  Elemennt type primary
	*/
	const ELEMENT_TYPE_DEFAULT = 'default';

	/**
	* @var string ELEMENT_TYPE_SUCCESS  Elemennt type success
	*/
	const ELEMENT_TYPE_SUCCESS = 'success';

	/**
	* @var string ELEMENT_TYPE_INFO  Elemennt type info
	*/
	const ELEMENT_TYPE_INFO = 'info';

	/**
	* @var string ELEMENT_TYPE_LINK  Elemennt type link
	*/
	const ELEMENT_TYPE_LINK = 'link';

}
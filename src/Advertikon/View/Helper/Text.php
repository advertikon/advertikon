<?php
/**
* Form Input Text Helper
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
* Form Input Text Helper Class
*/
class Text extends Input {

	/**
	* @see Advertikon\View\Helper\Element
	*/
	public function init() {
		$this->addClass( 'form-control' );
	}

}
?>
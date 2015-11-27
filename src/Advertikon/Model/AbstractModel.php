<?php
/**
* Abstract Model
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\Model;

use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

/**
* Abstract Model class
*/
abstract class AbstractModel{

	/**
	* @var Object ModelSet $_resource resource instance
	*/
	protected $_resource;

	/**
	* @var String ModelSet $_resourceNmae resource name
	*/
	protected $_resourceName;

	/**
	* @var Object $_data Model dataset
	*/
	protected $_data;

	/**
    * Get resource instance
    *
    * @return Object Resource instance
    */
    public function getResource() {
    	if( ! $this->_resource ) {
    		$name = $this->getResourceName();
    		$this->_resource = new $name;
    	}
    	return $this->_resource;
    }

    /**
    * Get resource name
    *
    * @return Object Resource instance
    */
    public function getResourceName() {
    	return $this->_resourceName;
    }

}
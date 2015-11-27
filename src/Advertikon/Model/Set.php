<?php
/**
* Abstract Model Set
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\Model;

use Advertikon\Resource;
use Zend\Stdlib\ArrayObject;
use Advertikon\Exception\InvalidArgument;
use Advertikon\App; 
use Iterator;

/**
* Model Set class
*/
abstract class Set extends AbstractModel implements Iterator{

	/**
	* @var String $_itemModel Single item model name
	*/
	protected $_itemModelName;

	/**
	* @var String $_itemModel Single item model instance
	*/
	protected $_itemModelInstance;

	/**
	* @var boolean $_pageable Whether Set is subject for pagination
	*/
	public $pageable = false;

	/**
	* ModelSet constructor
	*
	* @param Object $resource ModelSet resource. If present will be used instead of default
	*/
	public function __construct( $resource = null ) {
		if( $resource ) {
			if( $resource instanceof Resource ) {
				$this->_resource = $resource;
			}
			else {
				throw new InvalidArgument( 'Resource must be of Advertikon\Resource instance' , __METHOD__ );
			}
		}
		else {
			$this->_resource = $this->getResource();
		}
		$this->_resource->setModel( $this );
	}

	/**
	* Add filter parameters to modelSet data
	*
	* @param String|Array|Object Zend\Db\Qsl\Predicate\Predicate::addPredicates() arguments
	* @return Object $this
	*/
	public function filter( $filter ) {
		$this->_resource->addFilter( $filter );
		return $this;
	}

	/**
	* Populate modelSet dataset
	*
	* @return Object $this
	*/
	public function load() {
		$this->_data =  $this->_resource->load()->toArray();
		return $this;
	}

	/**
	* Iterator Interface. Get current model instance
	*
	* @return Object Instance of Advertikon\Model
	*/
	public function current() {
		$data = $this->_data->current();
		if( ! $data ) {
			return null;
		}
		return $this->getItemModel()->loadFromSet( $data );
	}
	
	/**
	* Iterator Interface. Get dataset current key
	*
	* @return integer
	*/
	public function key() {
		return $this->_data->key();
	}

	/**
	* Iterator interface. Make step
	*/
	public function next() {
		$this->_data->next();
	}

	/**
	* Iterator Interface. Revind dataset
	*/
	public function rewind() {
		$this->_data->rewind();
	}

	/**
	* Iterator Interface. Validate next dataset element
	*
	* @return boolean
	*/
	public function valid() {
		return $this->_data->valid();
	}

	/**
	* Get dataset row count
	*
	* @return integer
	*/
	public function count(){
		return $this->_data->count();
	}

	/**
    * Get item model instance
    *
    * @return Object
    */
    public function getItemModel() {
    	if( ! $this->_itemModelInstance ) {
    		$name = $this->getItemModelName();
    		$this->_itemModelInstance = new $name;
    	}
    	return $this->_itemModelInstance;
    }

    /**
    * Get item model name
    *
    * @return Object Resource instance
    */
    public function getItemModelName() {
    	return $this->_itemModelName;
    }

    /**
    * Make set pageable
    *
    * @throws Advertikon\Exception\InvalidArgument On invalid Argument
    */
    public function paginate() {
    	$this->pageable = true;
    	return $this;
    }
}
?>
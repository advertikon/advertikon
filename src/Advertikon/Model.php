<?php
/**
* Single Item Model
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon;

use Advertikon\Resource;
use Zend\Stdlib\ArrayObject;
use Advertikon\Exception\InvalidArgument;
use Advertikon\App;
use IteratorAggregate;
use ArrayIterator;
use Advertikon\Model\AbstractModel;

/**
* Single Item Model class
*/
abstract class Model extends AbstractModel implements IteratorAggregate {

	/**
	* @var Object $_data Model data container, instance of Zend\Stdlib\ArrayObject
	*/
	protected $_data;

	/**
	* @var Array $_canBeUnsetFields Object fields that can be uset with uns method
	*/
	protected $_canBeUnsetFields = array();

	/**
	* Model constructor
	*
	* @param Object|Array $param Request parameters
	* @param Object|null $resource Instance of Advertikon\Resource, if present will be used as model resource
	* @param Array $data Model data, If present will be used to populate model
	* @throws Advertikon\Exception\InvalidArgument On arguments inconsistency
	*/
	public function __construct( $param = array() , $resource = null , $data = null ) {

		if( $resource ) {
			if( ! $resource instanceof Resource ) {
				throw new InvalidArgument( 'Resource must be of Advertikon\Resource instance' , __METHOD__ );
			}
			$this->_resource = $resource;
		}
		else {
			$this->_resource = $this->getResource();
		}

		$this->_resource->setModel( $this );
		$this->_initData( $data , $param );
	}

	/**
	* Populate data container
	*
	* @param Array|null $data Data to populate container
	* @param Array|scalar|null $param Request parameters. Used to fetch data from DB when $data is empty.
	* If scalar data provided - used as prmary key value
	* @throws Advertikon\Exception\InvalidArgument On on unsupported argument type
	*/
	protected function _initData( $data , $param ) {
		if( $data ){
			if( is_array( $data ) ) {
				$data = new ArrayObject( $data );
			}

			if( is_a( $data , 'Zend\Stdlib\ArrayObject' ) ) {
				unset( $data[ $this->_resource->getPrimaryKey() ] );
				$this->_data = $data;
			}
			else {
				throw new InvalidArgument( 'Unsupported argument type for Model data initiation' , __METHOD__ );
			}
		}
		elseif( $param ) {

			if( is_array( $param ) || is_a( $param , 'Zend\Stdlib\ArrayObject' ) ) {

				if( is_array( $param ) ) {
					$param = new ArrayObject( $param );
				}

				if( ! $param->count() ) {
					$this->_data = new ArrayObject;
				}
				else {
					$this->_load( $param );
				}
			}
			//we have primary key value
			elseif( is_scalar( $param ) ) {
				$key = $this->_resource->getPrimaryKey();
				$arr = new ArrayObject;
				$arr[ $key ] = $param;
				$this->_load( $arr );
			}
			else {
				throw new InvalidArgument( 'Invalid format of query parameter' , __METHOD__ );
			}
		}
		else {
			$this->_data = new ArrayObject;
		}
	}

	/**
	* Populate model using resource
	*
	* @param Array|ArrayObject $param Request parameters
	* @return Object $this
	*/
	protected function _load( $param = array() ) {

		if( ! $param || ( ! is_array( $param ) && ( ! is_a( $param , 'Zend\Stdlib\ArrayObject' ) || ! $param->count() ) ) ) { 
			throw new InvalidArgument( 'To load data to model parameters must be not empty array or implements ArrayObject Interface' , __METHOD__ );
		}

		if( ! is_a( $param , 'ArrayObject' ) ) {
			$param = new ArrayObject( $param );
		}

		$data = $this->_resource->load( $param )->current();
		$data = $data ?: new ArrayObject;
		$this->_data = $data;
		return $this;
	}

	/**
	* Populate model data from resourceSet
	*
	* @param Array|ArrayObject $data Data to populate with
	* @throws Advertikon\Exception\InvalidArgument On invalid data format
	* @return Object $this
	*/
	public function loadFromSet( $data ) {

		if( is_array( $data ) ) {
			$data = new ArrayObject( $data );
		}

		if( ! is_a( $data , 'Zend\Stdlib\ArrayObject' ) && ! is_a( $data , 'ArrayObject' ) ) {
			throw new InvalidArgument( 'To populate model data object must implements ArrayObject interface' , __METHOD__ );
		}

		$this->_data = $data;
		return $this;
	}

	/**
	* Delete model contents at resource level
	*
	* @return Object $this
	*/
	public function delete() {
		return $this->_resource->delete();
	}

	/**
	* Save model contents at resource level
	*
	* @return Object $this
	*/
	public function save() {
		$this->_resource->save();
		return $this;
	}

	/**
	* Magic methods to handle set, get, uns
	*
	* @param string $name Value name
	* @param mixed|null $val Value
	* @throws Advertikon\Exception\InvalidArgument When value name is not a string
	* @throws Advertikon\Exception\InvalidArgument On attempt to manually set primary key
	* @return $this|mixed
	*/
	public function __call( $name , $val = null ) {

		$prefix = substr( $name , 0 , 3 );
		$key = App::underscore( substr( $name , 3 ) );

		if(  $prefix === 'get' ) {
			$this->_checkGetKey( $key );
			return isset( $this->_data[ $key ] ) ? $this->_data[ $key ] : null; 
		}
		else if( $prefix === 'set' ) {
			if( $this->_resource->getPrimaryKey() == $key ) {
				throw new InvalidArgument( 'Primary key can not be modified manually' );
			}
			$this->_checkSetKey( $key );
			$this->_data->offsetSet( $key , isset( $val[ 0 ] ) ? $val[ 0 ] : null );
			return $this;
		}
		else if( $prefix == 'uns' ) {
			$this->_checkUnsKey( $key );
			$this->_data->offsetUnset( $key );
			return $this;
		}
	}

	/**
	* Check correctness of method invoke
	*
	* @param string $key Method name
	*/
	protected function _checkSetKey( &$key ) {

	}

	/**
	* Check correctness of method invoke
	*
	* @param string $key Method name
	*/
	protected function _checkGetKey( &$key ) {

	}

	/**
	* Check correctness of method invoke
	*
	* @param string $key Method name
	*/
	protected function _checkUnsKey( &$key ) {
		$k = strtolower( $key );
		if( ! in_array( $k , $this->_resource->getFields() ) || in_array( $k , $this->_canBeUnsetFields ) ) {
			return;
		}
		throw new InvalidArgument( sprintF( 'Unset value ("%s") is forbidden for model "%s"' , $key , get_class( $this ) ) );
	}

	/**
	* Data container setter
	*
	* @param string $name Value name
	* @param mixed $value Value
	* @throws Advertikon\Exception\InvalidArgument When value name is not a string
	* @throws Advertikon\Exception\InvalidArgument On attempt to manually set primary key
	* @return $this
	*/
	public function set( $name , $value ) {

		if( gettype( $name ) != 'string' ) {
			throw new InvalidArgument( 'Property name need to be specified to get access to model property' , __METHOD__ );
		}

		if( $this->_resource->getPrimaryKey() == $name ) {
				throw new InvalidArgument( 'Primary key can not be modified manually' );
		}
		$this->_checkSetKey( $name );
		$this->_data->offsetSet( $name , $value );
		return $this;
	}

	/**
	* Data container getter
	*
	* @param string $name Value name
	* @throws Advertikon\Exception\InvalidArgument When value name is not a string
	* @return mixed
	*/
	public function get( $name ) {
		if( gettype( $name ) != 'string' ) {
			throw new InvalidArgument( 'Property name need to be specified to get access to model property' , __METHOD__ );
		}
		$this->_checkGetKey( $name );
		return $this->_data->offsetGet( $name );
	}

	/**
	* Data container unsetter
	*
	* @param string $name Value name
	* @throws Advertikon\Exception\InvalidArgument When value name is not a string
	* @return $this
	*/
	public function uns( $name ) {
		if( gettype( $name ) != 'string' ) {
			throw new InvalidArgument( 'Property name need to be specified to get access to model property' , __METHOD__ );
		}
		$this->_checkUnsKey( $name );
		return $this->_data->offsetUnset( $name );
	}

	/**
	* Get model data
	*
	* @return Array
	*/
	public function getData() {
		return $this->_data->getArrayCopy();
	}

	/**
	* Get IteratorAgrregate
	*
	* @return Object
	*/
	public function getIterator() {
        return new ArrayIterator( $this->_data );
    }
}
?>
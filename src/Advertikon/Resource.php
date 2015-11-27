<?php
/**
* Resource model
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon;

use Advertikon\Resource\ResourceInterface;
use Advertikon\Model;
use Advertikon\Exception\InvalidArgument;
use Zend\Eventmanager\Event;
use Zend\Stdlib\ArrayObject;
use Zend\Paginator\Paginator;

/**
* Resource model class
*/
abstract class Resource implements ResourceInterface{

	/**
	* Resource constructor
	*/
	public function __construct() {
		//parent::__construct();
		$this->_callbacks[] = App::getModule()->app()->getEventManager()->attach( App::PRESAVE_EVENT , array( $this , 'clearBeforeSave' ) , 100 );
		$this->id = uniqid();
	}

	/**
	* @var Object $_model Model that resource bound to
	*/
	protected $_model;

	/**
	* @var Array $_fields Resouce fields list
	*/
	protected $_fields = array();

	/**
	* @var mixed $_primaryKey Primary key name
	*/
	protected $_primaryKey = 'id';

	/**
	* @var Object $_evt Zend\Eventmanager\Event
	*/
	protected $_evt;

	/**
	* @var mixed $_params Request params
	*/
	protected $_param;

	/**
	* @var Array event listeners handlers
	*/
	protected $_callbacks = array();

	/**
	* @var string $id Resource ID
	*/
	public $id;

	/**
	* @var Object $_paginator Instance of Zend\Paginator\Paginator
	*/
	protected $_paginator;

	/**
	* @var mixed $_pagiatorAdapterData Data to path to Paginator Adapter constructor
	*/
	protected $_paginatorAdapterData;

	/**
	* Load data from resource
	*
	* @param Array $param Query parameters
	* @return Array
	*/
	protected function _load( $param ) {
		$this->_param = $param;
		return array();
	}

	/**
	* Save data to resource
	*
	* @param Array $param Query parameters
	* @return Array
	*/
	protected function _save() {

	}

	/**
	* Delete data from resource
	*
	* @param Array $param Query parameters
	* @return Array
	*/
	protected function _delete() {

	}

	/**
	* Get event instance
	*
	* @return Object Zend\Eventmanager\Event
	*/
	protected function _getEvent() {
		if( ! $this->_evt ) {
			$this->_evt = new Event;
			$this->_evt->setTarget( $this );
		}
		return $this->_evt;
	}

	/**
	* @see Advertikon\Resource\ResourceInterface::load()
	*/
	public function load( $param = null ){
		$param = $param ?: new ArrayObject;
		$this->_getEvent()->setParam( 'params' , $param );
		App::getModule()->app()->getEventManager()->trigger( App::PRELOAD_EVENT , $this->_getEvent() );
		$data = $this->_load( $param );
		$this->_getEvent()->setParam( 'data' , $data );
		App::getModule()->app()->getEventManager()->trigger( App::AFTERLOAD_EVENT , $this->_getEvent() );
		return $data;
	} 

	/**
	* @see Advertikon\Resource\ResourceInterface::save()
	*/
	public function save(){
		$this->_getEvent()->setParam( 'model' , $this->_model );
		App::getModule()->app()->getEventManager()->trigger( App::PRESAVE_EVENT , $this->_getEvent() );
		$this->_save();
		$this->_getEvent()->setParam( 'model' , $this->_model );
		App::getModule()->app()->getEventManager()->trigger( App::AFTERSAVE_EVENT , $this->_getEvent() );
		return $this;
	}

	/**
	* @see Advertikon\Resource\ResourceInterface::delete()
	*/
	public function delete(){
		$this->_getEvent()->setParam( 'model' , $this->_model );
		App::getModule()->app()->getEventManager()->trigger( App::PREDELETE_EVENT , $this->_getEvent() );
		$affectedRows = $this->_delete();
		$this->_getEvent()->setParam( 'model' , $this->_model );
		App::getModule()->app()->getEventManager()->trigger( App::AFTERDELETE_EVENT , $this->_getEvent() );
		return $affectedRows;
	}

	/**
	* Set model instance
	*
	* @param Object $model Instance of Advertikon\Model
	* @throws Advertikon\Exception\InvalidArument If $model not instance of Advertikon\Model
	*/
	public function setModel( $model ) {
		$this->_model = $model;
	}

	/**
	* Filter result fields depend on permitted fields
	*
	* @param Object $result
	*/
	public function clearBeforeSave( $evt ) {

		if( $evt->getTarget() !== $this ) {
			return;
		}

		$model = $evt->getParam( 'model' );

		foreach( $model as $key => $val  ) {
			if( $this->_fields && ! in_array( $key , $this->_fields ) ) {
				$model->uns(  $key );
			}
		}
		foreach( $this->_fields as $field ) {
			$val = $model->get( $field );
			if( ( is_null( $val ) || $val === '' ) && $field != $this->_primaryKey ) {
				throw new InvalidArgument( sprintf( 'Missing field "%s" for "%s"' , $field , get_class( $this ) ) , __METHOD__ );
			}
		}
	}

	/**
	* Check fields consistancy
	*
	* @param Array $data Resource data
	* @param boolean $fill Flag to fill missing fields with empty string. Otherwise throw exception
	* @throws Advertikon\Exception\InvalidArgument On missing fields
	*/
	/*protected function _formatData( & $data , $fill = false ) {
		foreach( $this->_fields as $name ) {
			if( ! isset( $data[ $name ] ) ) {
				if( $fill ) {
					$data[ $name ] = '';
				}
				else {
					throw new InvalidArgument( sprintf( 'Missing valie "$name"' ) );
				}
			}
		}
	}*/

	/**
	* Primary key getter
	*
	* @return string
	*/
	public function getPrimaryKey(){
		return $this->_primaryKey;
	}

	/**
	* Get resource model fields
	*
	* @return Array
	*/
	public function getFields() {
		return $this->_fields;
	}

	/**
    * Get paginator Adapter instance
    *
    * @return Object
    */
    protected function _getPaginatorAdapter(){
    	return new ArrayAdapter( $this->_paginatorAdapterData );
    }

    /**
    * Get Paginator instance
    *
    * @return Object Instance of Zend\Paginator\Paginator
    */
    public function getPaginator(){
    	if( ! $this->_paginator ) {
    		$this->_paginator = new Paginator( $this->_getPaginatorAdapter() );
    	}
    	return $this->_paginator;
    }
	
	/**
	* @see Advertikon\Model\Set::paginate()
    */
    public function paginate() {

    }
}
?>
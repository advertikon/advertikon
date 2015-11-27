<?php
/**
* Resource DB model
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\Resource;

use Zend\Db\Adapter\Adapter;
use Advertikon\App;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Advertikon\Resource;
use Zend\Paginator\Adapter\DbSelect;

/**
* Resource DB model class
*/
abstract class Db extends Resource{

	/**
	* @var Object Zend\Db\Adapter\Adapter
	*/
	static protected $_adapter;

	/**
	* @var Object Zend\Db\Sql\Sql
	*/
	protected $_sql;

	/**
	* @var Object Zend\Sql\Select
	*/
	protected $_select;

	/**
	* @var String $_table Main table name
	*/
	protected $_table;

	/**
	* @var string $_sql_print SQL query creation sequence
	*/
	protected $_sql_print = '';

	/**
	* @var Object $_predicate Predicate instance
	*/
	protected $_predicate;

	/**
	* Resource constructor
	*/
	public function __construct(){
		parent::__construct();
		if( ! self::$_adapter ) {
			self::$_adapter = self::getAdapter();
		}
		$this->_sql = new Sql( self::$_adapter );
		$this->_callbacks[] = App::getModule()->app()->getEventManager()->attach( App::GET_SELECT_EVENT , array( $this , 'dbGetSelect' ) , 100 );
	}

	/**
	* Get adapter instanse.
	*
	* @return Object Singletone of Zend\Db\Adapter\Adapter
	*/
	static public function getAdapter(){
		if( ! self::$_adapter ) {
			self::$_adapter = new Adapter( App::getModule()->config( 'db' ) );
		}
		return self::$_adapter;
	}

	/**
	* Set adapter instance
	*
	* @param Object Adapter istance
	*/
	static public function setAdapter( $adapter ) {
		self::$_adapter = $adapter;
	}

	/**
	* Event handler for select populating event
	*
	* @param Object $select Zend\Db\Sql\Select
	*/
	public function dbGetSelect( $evt ) {

		if( $evt->getTarget() !== $this  ) {
			return;
		}

		$key = $this->_primaryKey;
		$val = $this->_param[ $key ];
		$select = $evt->getParam( 'select' );
		$select->from( $this->_table )
				->columns( array( Select::SQL_STAR ) );

		$this->sqlPtintAdd( '[DB]' );
		//echo  $this->_sql->buildSqlString( $select ) . '<br><br>';
	}

	/**
	* @see Advertikon\Resource::load()
	*/
	public function _load( $param ){
		$this->_param = $param;
		$this->_select = $this->getSelect();
		$this->_getEvent()->setParam( 'select' , $this->_select );
		App::getModule()->app()->getEventManager()->trigger( App::GET_SELECT_EVENT , $this->_getEvent() );
		if( isset( $this->_model->pageable ) && $this->_model->pageable ) {
			$this->paginate();
		}
		$selectString = $this->_sql->buildSqlString( $this->_select );
		//echo PHP_EOL . '[DB]' . $selectString . PHP_EOL;
		$result = self::$_adapter->query( $selectString , Adapter::QUERY_MODE_EXECUTE );
		return $result;
	} 

	/**
	* @see Advertikon\Resource::save()
	*/
	public function _save(){

		$data = $this->_model->getData();

		//UPDATE
		if( $this->_model->get( $this->_primaryKey ) ) {
			$update = $this->_sql->update( $this->_table );
			$update->where( array( $this->_primaryKey => $data[ $this->_primaryKey ] ) );
			unset( $data[ $this->_primaryKey ] );
			$update->set( $data );
			$queryString = $this->_sql->buildSqlString( $update );
			self::$_adapter->query( $queryString , Adapter::QUERY_MODE_EXECUTE );
		}
		//INSERT
		else {
			$insert = $this->_sql->insert( $this->_table );
			$insert->values( $data );
			$queryString = $this->_sql->buildSqlString( $insert );
			$result = self::$_adapter->query( $queryString , Adapter::QUERY_MODE_EXECUTE );
			if( method_exists( $result , 'getGeneratedValue' ) ) {
				$data[ $this->_primaryKey ] = $result->getGeneratedValue();
				$this->_model->loadFromSet( $data );
			}
		}
	}

	/**
	* @see Advertikon\Resource::delete()
	*/
	public function _delete(){
		//model data does not exist in DB
		if( ! $this->_model->get( $this->_primaryKey ) ) {
			return $this;
		}
		$delete = $this->_sql->delete( $this->_table );
		$delete->where( array( $this->_primaryKey => $this->_model->get( $this->_primaryKey ) ) );
		$queryString = $this->_sql->buildSqlString( $delete );
		$result = self::$_adapter->query( $queryString , Adapter::QUERY_MODE_EXECUTE );
		if( method_exists( $result , 'getAffectedRows' ) ) {
			return $result->getAffectedRows();
		}
	}

	/**
	* Add sql print element to sql print sequence
	*
	* @param string $print
	*/
	public function sqlPrintAdd( $print ) {
		$this->_sql_print .= $print;
	}

	/**
	* Get sql print sequence
	*
	* @return string
	*/
	public function sqlPrintGet() {
		return $this->_sql_print;
	}

	/**
	* Populate predicate ( for Set Resource only )
	*
	* @param mixed $filter Zend\Db\Sql\Predicater\Predicate::addPredicates() arguments
	*/
	public function addFilter( $filter ) {
		$this->_predicate->addPredicates( $filter );
	}

	/**
    * @see Advertikon\Resource::_getPaginatorAdapter()
    */
    protected function _getPaginatorAdapter(){
    	return new DbSelect( $this->getSelect() , self::$_adapter );
    }

    /**
	* @see Advertikon\Model\Set::paginate()
    */
    public function paginate() {
    	$limit = $this->getPaginator()->getItemCountPerPage();
    	$offset = $this->getPaginator()->getAbsoluteItemNumber( 0 );
    	$this->getSelect()->limit( $limit );
    	$this->getSelect()->offset( --$offset );
    }

    /**
    * Get SQL Select singlrton instance
    *
    * @return Object
    */
    public function getSelect(){
    	if( ! $this->_select ) {
    		$this->_select = $this->_sql->select();
    	}
    	return $this->_select;
    }
}
?>
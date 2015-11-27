<?php
/**
* Resource model interface
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\Resource;

/**
* Resource model interface
*/
interface ResourceInterface{

	/**
	* Populate model with data from resource
	*
	* @param Object $param Parameters to fetch data upon, instance of ArrayObject
	* @return Array
	*/
	public function load( $param );

	/**
	* Update/save associated with resource data
	*
	* @return Object $this
	*/
	public function save();

	/**
	* Delete associate dwith resource data
	*
	* @return Object $this
	*/
	public function delete();
}
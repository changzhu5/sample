<?php


class Model extends Object
{
	/**
	 * The container for the model is fetched data.
	 * @var array
	 * @access protected
	 */
	protected $data = array();
	
	/**
	 * SQ_Lite Object
	 * @var SQ_Lite Object
	 * @access protected
	 */
	protected $cache_lite = null;
	/**
	 * the last insert record id
	 */
	public $last_insert_id = "";
	/**
	 * Primary key
	 */
	protected $primary_key = null;
	public function __construct(){
		$cacheDir = Configure::read('base') . 'tmp/';
	}
}
?>
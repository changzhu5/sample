<?php
class Context extends Object{
	private static $inst;
	private $configuration = array();
	
	private function __construct(){
		
	}
	public static function getInstance(){
		if(is_null(self::$inst)){
			self::$inst = new Context();
		}
		return self::$inst;
	}
	/**
	 * Fetch value
	 * @param String $key
	 * @return Mix
	 */
	public static function fetch($key){
		$inst = self::getInstance();
		if(isset($inst->configuration[$key])){
			return $inst->configuration[$key];
		}
		return false;
	}
	/**
	 * Set value
	 * @param String $key
	 * @param Mix $value
	 * @return void
	 */
	public static function drop($key,$value){
		$inst = self::getInstance();
		$inst->configuration[$key] = $value;
	}
}
?>
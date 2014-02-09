<?php
class Configure extends Object
{
	private static $inst;
	private $configuration = array();
	
	private function __construct()
	{
		
	}
	public static function getInstance()
	{
		if(is_null(self::$inst))
		{
			self::$inst = new Configure();
		}
		return self::$inst;
	}
	public static function read($key,$class='global')
	{
		$inst = self::getInstance();
		if(isset($inst->configuration[$class][$key]))
		{
			return $inst->configuration[$class][$key];
		}
		return false;
	}
	public static function write($key,$value,$class='global')
	{
		$inst = self::getInstance();
		$inst->configuration[$class][$key] = $value;
	}
}
?>
<?php
class Component extends Object
{
	protected $name;
	protected $controller;
	
	function __construct($name,$controller)
	{
		$this->name = $name;
		$this->controller = $controller;
	}
	public function startup($option=null){}
}
?>
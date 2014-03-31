<?php

abstract class Object{
	/**
	 * object to string conversion.
	 * @return string the name this class
	 * @access public
	 */
	public function toString(){
		$class = get_class($this);
		return $class;
	}
	/**
	 * calls a controller's method from any location
	 * @param string $url eg.controller/action/param1/param2/...
	 * @param array $options some $key=>$value that determine controller's properties
	 * @return boolean true or false or mix data
	 * @access public
	 */
	public function requestAction($controller,$action,$app=null,$params=null){
		$dispatcher = Context::fetch('dispatcher');
		$request = Context::fetch('request');
		if(!$app){
			$app = $request->getAppName();
		}
		$option = array(
			'app'=>$app,
			'controller'=>$controller,
			'action'=>$action,
			'params'=>$params,
			'base'=>$dispatcher->getAppRoot($app)
		);
		$request = New Request($option);
		return $dispatcher->runAction($request);
	}
	/**
	 * calls a method on this object with given parameters
	 * @param string $name name of method to call
	 * @param array $params parameters list to use when call $method
	 * @return mixed the result of the $method
	 * @access public
	 */
	public function dispatchMethod($name,$params = array()){
		if(empty($params)){
			return $this->{$name}();
		}
		return call_user_func_array(array(&$this, $name), $params);
	}

	/**
	 * error handler
	 * @param string $action the action of error controller
	 * @param array $params 
	 */
	public function appError($message){
		$dispatcher = Context::fetch("dispatcher");
		$request = $dispatcher->getRequest();
		
		require_once CORE_PATH.'error.php';
		$error = new Error($request);
		$error->report($message);
		exit();
	}
	public function setMsg($message,$key){
		$_SESSION[$key] = $message;
	}
	public function getMsg($key){
		if(isset($_SESSION[$key]) && $_SESSION[$key]){
			$v = $_SESSION[$key];
			$_SESSION[$key] = null;
			return $v;
		}
		return null;
	}
	public function get($key){
		$reflection = new ReflectionClass(get_class($this));
		if($reflection->hasProperty($key)){
			return $reflection->getProperty($key);
		}
		return false;
	}
}
?>
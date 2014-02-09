<?php
require_once CORE_MODEL_PATH . 'delegator.php';
require_once 'service.php';

class Request extends Object{
	/**
	 * Requested url
	 */
	private $url = null;
	/**
	 * Requested app name
	 */
	private $app;
	/**
	 * Requested controller
	 */
	private $controller;
	/**
	 * Requested action
	 */
	private $action;
	/**
	 * Requested params
	 */
	private $params = null;
	/**
	 * $_POST data
	 */
	private $post;
	/**
	 * $_GET data
	 */
	private $get;
	/**
	 * App Base url
	 */
	private $base;
	/**
	 * Delegator object
	 */
	private $delegator = null;
	/**
	 * Http headers
	 */
	private $headers = null;
	/**
	 * Session Id
	 */
	private $sessionId = null;
	
	public function __construct($params=null){
		if($params){
			$this->app = $params['app'];
			$this->controller = $params['controller'];
			$this->action = $params['action'];
			if(isset($params['form'])){
				$this->post = $params['form'];
			}
			if(isset($params['pass']['url'])){
				$this->url = $params['pass']['url'];
			}
			unset($params['pass']['url']);
			if(isset($params['pass'])){
				$this->get = $params['pass'];
			}
			if(isset($params['base'])){
				$this->base = $params['base'];
			}
			if(isset($params['params'])){
				$this->params = $params['params'];	
			}
		}
		$this->delegator = Delegator::getInstance($this);
	}
	/**
	 * Get delegator
	 * @return Delegator
	 */
	public function getDelegator(){
		return $this->delegator;
	}
	public function getParams(){
		return $this->params;
	}
	public function getHeaders(){
		if($this->headers){
			return $this->headers;
		}
		foreach ($_SERVER as $name => $value) { 
           if (substr($name, 0, 5) == 'HTTP_') { 
               $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))); 
               $headers[$name] = $value; 
           } else if ($name == "CONTENT_TYPE") { 
               $headers["Content-Type"] = $value; 
           } else if ($name == "CONTENT_LENGTH") { 
               $headers["Content-Length"] = $value; 
           } 
       }
		$this->headers = $headers; 
       return $headers; 
	}
	public function getSessionId(){
		if($this->sessionId){
			return $this->sessionId;
		}
		return $_COOKIE['PHPSESSID'];
	}
	public function getPost(){
		return $this->post;
	}
	public function getAppRoot(){
		return $this->base;
	}
	public function getAppName(){
		return $this->app;
	}
	public function getControllerName(){
		return $this->controller;
	}
	public function getActionName(){
		return $this->action;
	}
	public function getService(){
		return Service::getInstance($this);
	}
	public function isAjax(){
		$headers = $this->getHeaders();
		if(isset($headers['X-Requested-With']) && $headers['X-Requested-With']){
			return true;
		}
		return false;
	}
}
?>
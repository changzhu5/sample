<?php
class Service extends Object{
	private static $insts = array();
	/**
	 * Service name
	 * @var String
	 */
	private $serviceName = null;
	/**
	 * If require authorization
	 * @var Boolean
	 */
	private $auth = null;
	/**
	 * If service is enabled
	 * @var Boolean
	 */
	private $active = null;
	/**
	 * service description
	 * @var String
	 */
	private $description = null;
	/**
	 * Service inputs
	 * @var Array
	 */
	private $input = array();
	/**
	 * Service outputs
	 * @var Array
	 */
	private $output = array();
	private $request = null;
	/**
	 * Passed parameters
	 * @var Array
	 */
	private $params = null;
	/**
	 * Get Service Object
	 * @param Request $request
	 * @param String $key key to get service list for current app
	 * @param String $serviceName service name
	 * @return Service
	 */
	private function __construct($request,$key,$serviceName){
		$this->serviceName = $serviceName;
		$this->request = $request;
		$services = xcache_get($key);
		$serviceInfo = $services[$serviceName];
		$this->auth = $serviceInfo['auth'];
		$this->active = $serviceInfo['active'];
		$this->description = $serviceInfo['description'];
		$this->input = $serviceInfo['input'];
		$this->output = $serviceInfo['output'];
	}
	public static function getInstance($request){
		$serviceName = $request->getControllerName() . "." . $request->getActionName();
		$serviceName = trim($serviceName,".");
		$arr = explode(".",$serviceName);
		if(count($arr) != 2){
			$obj = new Object();
			$obj->appError("Invalid service name:" . $serviceName);
		}
		$key = "services_" . $request->getAppName();
		if(!xcache_isset($key)){
			$dispatcher = Context::fetch("dispatcher");
			$file = $request->getAppRoot() . "services.xml";
			$dispatcher->loadServices($file,$key);
		}
		$services = xcache_get($key);
		if(!isset($services[$serviceName])){
			return null;
		}
		if(!isset(self::$insts[$serviceName])){
			self::$insts[$serviceName] = new Service($request,$key,$serviceName);
		}
		return self::$insts[$serviceName];
	}
	/**
	 * Execute service
	 * @param Array $params
	 * @return Array
	 */
	public function execute($params=null){
		if(!$params){
			$params = $_POST;
		}
		$this->setParams($params);
		if($this->auth){
			if(!$this->validateAuth()){
				$this->appError('userLogin is null');
			}
		}
		if(!$this->validateInput()){
			$this->appError('invalid input for event:' . $this->serviceName);
		}
		$dispatcher = Context::fetch("dispatcher");
		$results = $dispatcher->runAction($this->request);
		if(!isAssoc($results)){
			$this->appError("Output is not assoc array");
		}
		if(!$this->validateOutput($results)){
			$this->appError("Invalid output");
		}
		$results['code'] = 200;
		return $results;
	}
	/**
	 * Set parameters from external
	 * @param Array $params
	 * @return void
	 */
	public function setParams($params){
		$this->params = $params;
	}
	/**
	 * Validate parameters
	 * @return Boolean
	 */
	private function validateInput(){
		return true;
	}
	/**
	 * Validate output
	 * @return Boolean
	 */
	private function validateOutput($results){
		return true;
	}
	/**
	 * Validate auth
	 * @return Boolean
	 */
	private function validateAuth(){
		return true;
	}
}
?>
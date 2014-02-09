<?php
/**
 * The entrance to the system.
 * charge the whole system's procedual
 */
require_once CORE_CONTROLLER_PATH.'app_controller.php';
require_once CORE_PATH.'factory.php';
require_once CORE_PATH.'request.php';
require_once CORE_PATH.'ajax.php';
class Dispatcher extends Object{
	/**
	 * The current requested url
	 * @var string eg. appName/controller/action/param1/param2...
	 * @access private
	 */
	private $url = null;
	/**
	 * the params with this request,it'll pass to controller::$params
	 * @var array
	 * @access public
	 */
	public $params = array();
	/**
	 * the pointer of this object
	 * @staticvar Dispatcher
	 * @access private
	 */
	private static $inst = null;
	private static $controllers = array();
	/**
	 * Current request
	 */
	private $request = null;
	/**
	 * Initialize cache object
	 * @return void
	 * @access private
	 */
	private function __construct(){
		
	}
	/**
	 * get the instance of this object
	 * @return Cache
	 * @access public
	 * @static
	 */
	public static function getInstance(){
		if(is_null(self::$inst))
		{
			self::$inst = new Dispatcher();
		}
		return self::$inst;
	}
	/**
	 * dispatch url,get Controller object and invoke controller then return result 
	 * @param string $url eg. appName/controller/action/param1/param2/...
	 * @return string static html output
	 * @access public
	 * @static 
	 */
	public static function dispatch($url = null){
		if(is_null($url)){
			if(!empty($_GET['url']))
				$url = $_GET['url'];
		}
		$dispatcher = Dispatcher::getInstance();
		Context::drop("dispatcher",$dispatcher);
		$dispatcher->params = array();
		$dispatcher->url = $url;
		$flag = $dispatcher->parseURL();
		$request = new Request($dispatcher->params);
		$dispatcher->request = $request;
		Context::drop("request",$request);
		Context::drop("delegator",Delegator::getInstance($request));
		if($request->isAjax()){
			$ajax = Ajax::getInstance();
			return $ajax->execute($request);
		}
		/*
		if($service = $request->getService()){
			return json_encode($service->execute());
		}
		*/
		if(!$flag){
			$dispatcher->appError("Invalid URL");
		}
		$controller = $dispatcher->getController();
		if($controller->limit == 'public'){
			$html = $dispatcher->invoke($controller);
			return $html;
		}
		else{
			$dispatcher->appError('This controller is protected');
		}
	}
	/**
	 * parse url
	 * @return array
	 * @access private
	 */
	private function parseURL(){
		$default_app = Configure::read('app');
		$str_len = strlen($this->url);
		if(substr($this->url,$str_len-1,1) == '/')
		{
			$this->url = substr($this->url,0,$str_len-1);
		}
		$arr = explode('/',$this->url);
		if(!empty($arr[0])){
			$app = $this->params['app'] = $arr[0];
		}
		else{
			$app = $this->params['app'] = $default_app;
		}
		
		if(Configure::read('single-mode')){	
			$app = $default_app;
			array_unshift($arr,$app);
		}
		$this->checkAdminRoute($app);
		$this->readConfig($app);
		if(!empty($arr[1])){
			$this->params['controller'] = $arr[1];
		}
		else{
			$default_controller = Configure::read('controller');
			$this->params['controller'] = $default_controller;
		}
		if(!empty($arr[2])){
			if(preg_match('/(\w+)\.html/',$arr[2],$action)){
				$this->params['action'] = $action[1];
			}
			else{
				$this->params['action'] = $arr[2];
			}	
		}
		else{
			$default_action = Configure::read('action');
			$this->params['action'] = $default_action;
		}
		if(count($arr)>2){
			if(!empty($this->params['params'])){
				$this->params['params'] = array();
			}
			for($i=3;$i<count($arr);$i++){
				$this->params['params'][]=$arr[$i];
			}
		}
		if(!$this->adminRoute){
			$this->params['form'] = $_POST;
			$this->params['pass'] = $_GET;
			$this->params['base'] = APP_PATH . $app . DS;
		}
		else{
			$this->params['base'] = CORE_APP_PATH . $app . DS;
		}
		return true;
	}
	/**
	 * Check if this app is in the lib
	 * @param String $app app name
	 * @return boolean
	 */
	private function checkAdminRoute($app){
		$file = CORE_APP_PATH . $app . DS;
		if(file_exists($file)){
			$this->adminRoute = true;
		}
		else{
			$file = APP_PATH . $app . DS;
			if(file_exists($file)){
				$this->adminRoute = false;
			}
			else{
				$this->appError('cannot find app:'.$app);
			}
		}
		return true;
	}
	/**
	 * Read App Config file
	 * @param $app String app name
	 */
	public function readConfig($app){
		if($this->adminRoute){
			$file = CORE_APP_PATH . $app . DS . 'config.php';
		}
		else{
			$file = APP_PATH . $app . DS . 'config.php';
		}
		if(file_exists($file)){
			include_once $file;
		}
	}
	/**
	 * get specific controller object
	 * @param Request $request
	 * @return Controller
	 * @access public
	 */
	public function getController($request=null){
		if($request){
			$app = $request->getAppName();
			$name = $request->getControllerName();
			$actionName = $request->getActionName();
		}
		else{
			$app = $this->params['app'];
			$name = $this->params['controller'];
		}
		if(isset(self::$controllers[$app][$name])){
			$c = self::$controllers[$app][$name];
			if(isset($actionName)){
				$c->action = $actionName;
			}
			return $c;
		}
		$filename = $name.'.php';
		if($this->adminRoute){
			$filepath = CORE_APP_PATH . $app . DS .'controllers' . DS . $filename;
		}
		else{
			$filepath = APP_PATH . $app . DS . 'controllers' . DS . $filename;
		}			
		if(file_exists($filepath)){
			include_once $filepath;
		}
		else{
			$params = array('name'=>$name,'file'=>$filename,'app'=>$app);
			$this->appError('missingController',$params);
		}
		$classname = name_to_class($name).'Controller';
		if(class_exists($classname)){
			if(!$request){
				$request = $this->request;
			}
			$controller = new $classname($request);
		}
		else{
			$params = array('classname'=>$classname,'file'=>$filename);
			$this->appError('missingClass',$params);
		}
		self::$controllers[$app][$name] = $controller;
		return $controller;
	}
	/**
	 * Get current request
	 */
	public function getRequest(){
		if(!$this->request){
			return new Request();
		}
		return $this->request;
	}
	/**
	 * invoke controller's action control controller's procedual
	 * @param Controller &$controller
	 * @return mix array or string
	 * @access private
	 */
	private function invoke(&$controller){
		$controller->init();
		$action = $controller->action;
		$reflection = new ReflectionClass(get_class($controller));
		if(!$reflection->hasMethod($action)){
			$classname = get_class($controller);
			$params = array('method'=>$action,'class'=>$classname);
			$this->appError('missingAction',$params);
		}
		$method = $reflection->getMethod($action);
		$parameter_num = $method->getNumberOfParameters();
		$params = $this->matchParameters($parameter_num);
		$flag = $controller->beforeAction();
		if(is_bool($flag) && $flag){
			$datas = $controller->dispatchMethod($action,$params);
			
			if($datas)
			{
				return $datas;	
			}
			else{
				return $controller->afterAction();
			}
		}
		else{
			return $flag;
		}
	}
	/**
	 * Match parameters. Only match for the first or the last parameter could be a array
	 * @param int $require_num The number of parameters which invoked function required
	 * @return array matched parameters
	 */
	private function matchParameters($require_num)
	{
		if(isset($this->params['params']))
		{
			$input_params = $this->params['params'];
			$params = array();
			$temp = array();
			$len_input_params = count($input_params);
			if($len_input_params > $require_num)
			{	
				for($i=0;$i<$require_num;$i++)
				{
					$params[] = $input_params[$i];
				}
				for($j = $require_num; $j < $len_input_params;$j++){
					$temp[] = $input_params[$j];
				}
				if($temp){
					$params[] = $temp;
				}
			}
			else 
			{
				$params = $input_params;
			}
			return $params;
		}
	}
	/**
	 * Get apache document root
	 * @return String
	 */
	public function getWebRoot($appName = null){
		if($appName){
			$root = str_replace(DS, "/", $this->getAppRoot($appName));
		}
		else{
			$root = str_replace(DS, "/", WEBROOT_PATH);
		}
		$f = substr($root,strlen($_SERVER['DOCUMENT_ROOT']));
		return Configure::read('siteurl') . $f;
	}
	/**
	 * run function in a controller
	 * @param Request $request
	 * @return Array or String or Object
	 */
	public function runAction($request){
		$oldRequest = Context::fetch('request');
		$delegator = Context::fetch('delegator');
		$controller = $this->getController($request);
		if($controller->limit == 'public' || $controller->limit == 'protected'){
			Dispatcher::getInstance()->readConfig($request->getAppName());
			$delegator->setRequest($request);
			$html = $controller->dispatchMethod($request->getActionName(),$request->getParams());
			$delegator->setRequest($oldRequest);
			return $html;
		}
		else{
			$this->appError('Controller:' . $controller->name . ' is protected');
		}
	}
	/**
	 * Get app root path
	 * @param String $appName
	 * @return String
	 */
	public function getAppRoot($appName){
		$filepath = CORE_APP_PATH . $appName . DS;
		if(file_exists($filepath)){
			return $filepath;
		}
		$filepath = APP_PATH . $appName . DS;
		if(file_exists($filepath)){
			return $filepath;
		}
		return null;	
	}
	/**
	 * Load services
	 * @param String $file file path
	 * @return void
	 */
	public function loadServices($file,$key){
		if(!file_exists($file)){
			return null;
		}
		$services = simplexml_load_file($file);
		if(!$services){
			$this->appError('invalid xml file:' . $file);
		}
		$len = $services->count();
		if($len <= 0){
			return null;
		}
		$myServices = array();
		for($i=0;$i<$len;$i++){
			$service = $services->service[$i];
			$serviceName = (String)$service['name'];
			
			$auth = (String)$service['auth'];
			$active = (String)$service['active'];
			$description = (String)$service->description;
			
			$inputs = null;
			$outputs = null;
			
			$input = $service->input;
			$inputParams = $input->param;
			$inputLen = $inputParams->count();
			if($inputLen > 0){
				for($j=0;$j<$inputLen;$j++){
					$param = $inputParams[$j];
					$paramName = (String)$param['name'];
					$paramType = (String)$param['type'];
					$paramRequired = (String)$param['required'];
					$inputs[$paramName] = array('type'=>$paramType,'required'=>$paramRequired);
				}
			}
			
			$output = $service->output;
			$outputParams = $output->param;
			$outputLen = $outputParams->count();
			if($outputLen > 0){
				for($j=0;$j<$outputLen;$j++){
					$param = $outputParams[$j];
					$paramName = (String)$param['name'];
					$paramType = (String)$param['type'];
					$paramRequired = (String)$param['required'];
					$outputs[$paramName] = array('type'=>$paramType,'required'=>$paramRequired);
				}
			}
			
			$myServices[$serviceName] = array(
				'auth'=>$auth,
				'active'=>$active,
				'description'=>$description,
				'input'=>$inputs,
				'output'=>$outputs
			);
		}
		xcache_set($key,$myServices,24*3600);
	}
}
?>
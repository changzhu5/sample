<?php
class Route extends Object{
	/**
	 * js resource handler
	 */
	private $js = 'admin/js';
	/**
	 * css resource handler
	 */
	private $css = 'admin/css';
	/**
	 * image resource handler
	 */
	private $img = 'admin/img';

	public function go($url){
		$params = $this->parse($url);
		if(!$params){
			return false;
		}
		if($this->{$params['type']}){
			$dispatcher = Dispatcher::getInstance();
			$controller = $dispatcher->getController($params['controller'],$params['path']);
			$dispatcher->params = $controller->params = $params;
			$dispatcher->invoke($controller);
		}
		return false;
	}
	private function parse($url){
		$params = null;
		$str_len = strlen($url);
		if(substr($url,$str_len-1,1) == '/'){
			$url = substr($this->url,0,$str_len-1);
		}
		$arr = explode('/',$url);
		$len = count($arr);
		if(!empty($arr[0])){
			$params['type'] = $arr[0];
		}
		else{
			return false;
		}
		$handler = $this->$params['type'];
		$arr2 = explode('/',$handler);
		$h_len = count($arr2);
		if($h_len > 2){
			$params['controller'] = $arr2[1];
			$params['action'] = $arr2[2];
			$params['path'] = CORE_CONTROLLER_PATH . $arr2[0] . DS ;
		}
		else{
			$params['controller'] = $arr2[0];
			$params['action'] = $arr2[1];
			$params['path'] = CORE_CONTROLLER_PATH;
		}
		$path = '';
		for($j=1;$j<count($arr);$j++){
			$path .= $arr[$j] . '/';
		}
		$path = rtrim($path,'/');
		$params['params'] = array($path);
		$params['form'] = $_POST;
		$params['pass'] = $_GET;
		return $params;
	}
}
?>
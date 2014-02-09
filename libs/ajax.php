<?php

class Ajax extends Object{
	private static $inst=null;

	private function __construct(){
		
	}
	public static function getInstance(){
		if(is_null(self::$inst)){
			self::$inst = new Ajax();
		}
		return self::$inst;
	}
	/**
	 * Ajax handler
	 * @param Request $request
	 * @return String
	 */
	public function execute($request){
		$headers = $request->getHeaders();
		if(!isset($headers['Responsetype'])){
			$this->appError('ajax call needs response type');
		}
		$responseType = $headers['Responsetype'];
		if(!in_array($responseType,array('json','xml','text'))){
			$this->appError('undefined response type');
		}
		
		$service = $request->getService();
		if($service){
			$data = $service->execute();
		}
		else{
			$dispatcher = Context::fetch('dispatcher');
			$data = $dispatcher->runAction($request);
		}
		return $this->filter($data,$responseType);
	}
	/**
	 * Filter data according to request type
	 * @param mix $data
	 * @param String $responseType [text,json]
	 * @return mix
	 */
	private function filter($data,$responseType){
		if($responseType == 'text'){
			$type = gettype($data);
			switch($type){
				case 'object':
					return $data->toString();
				case 'array':
					return json_encode($data);
				case 'resource':
					$this->appError('can not return resource');
				default:
					return (string)$data;
			}
		}
		if($responseType == 'json'){
			$type = gettype($data);
			if($type != 'array'){
				$this->appError('the response value is not array');
			}
			return json_encode($data);
		}
	}
}
?>
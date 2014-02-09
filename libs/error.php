<?php
/**
 * This is framework's level error.
 */
class Error extends Controller{

	public $layout = "error";
	/**
	 * error log path
	 */
	private $log;
	
	public $pageTitle = "Error";
	
	public function __construct($request){
		parent::__construct($request);
		$this->name = "errors";
		$this->log = CORE_PATH . 'log.txt';
		$this->viewPath = WEBROOT_PATH;
	}
	
	public function log($params = array('message'=>'')){
		Configure::write('debug',0);
		$this->ifRender = 0;
		$date = date("F j, Y, h:m:s");
		$message = $params['message'] . "|" . $date . "\r\n";
		$f = fopen($this->log,'a+');
		fwrite($f,$message);
		fclose($f);
	}
	public function report($message){
		if(Configure::read("logError")){
			$this->logError($message);
		}
		if($this->request->isAjax()){
			header('HTTP/1.1 500 ' . $message);
			exit();
		}
		$this->action = "report";
		$this->assign("message",$message);
		echo $this->render();
		exit();
	}
	private function logError($message){
		
	}
}
?>
<?php
class Resource extends Object{
	/**
	 * The current requested url
	 * @var string eg./controller/action/param1/param2...
	 * @access private
	 */
	private $url = null;
	private $app = null;
	private $type = null;
	private $file = null;
	/**
	 * the pointer of this object
	 * @staticvar Dispatcher
	 * @access private
	 */
	private static $inst = null;
	private $mime_types = array(
                "pdf"=>"application/pdf"
                ,"zip"=>"application/zip"
                ,"docx"=>"application/msword"
                ,"doc"=>"application/msword"
                ,"xls"=>"application/vnd.ms-excel"
                ,"ppt"=>"application/vnd.ms-powerpoint"
                ,"gif"=>"image/gif"
                ,"png"=>"image/png"
                ,"jpeg"=>"image/jpg"
                ,"jpg"=>"image/jpg"
                ,"mp3"=>"audio/mpeg"
                ,"wav"=>"audio/x-wav"
                ,"mpeg"=>"video/mpeg"
                ,"mpg"=>"video/mpeg"
                ,"mpe"=>"video/mpeg"
                ,"mov"=>"video/quicktime"
                ,"avi"=>"video/x-msvideo"
                ,"3gp"=>"video/3gpp"
                ,"css"=>"text/css"
                ,"jsc"=>"application/javascript"
                ,"js"=>"application/javascript"
                ,"htm"=>"text/html"
                ,"html"=>"text/html"
                ,"ico"=>"image/x-icon"
        );
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
			self::$inst = new Resource();
		}
		return self::$inst;
	}
	/**
	 * dispatch url,get Controller object and invoke controller then return result 
	 * @param string $url eg.controller/action/param1/param2/...
	 * @return string static html output
	 * @access public
	 * @static 
	 */
	public function get($url=null){
		if(is_null($url)){
			if(!empty($_GET['url']))
				$url = $_GET['url'];
		}
		$this->url = trim($url);
		if($filepath = $this->parse()){
			if(file_exists($filepath)){
				readfile($filepath);
				exit();
			}
			else{
				header("HTTP/1.0 404 Not Found");
				exit();
			}
		}
		return false;
	}
	private function parse(){
		$arr = explode("/",$this->url);
		$len = count($arr);
		$fileName = $arr[$len-1];
		if(!strpos($fileName,".")){
			return false;
		}
		$arr2 = explode(".",$fileName);
		$ext = $arr2[count($arr2)-1];
		if(!array_key_exists($ext, $this->mime_types)){
			return false;
		}
		header("Content-type:" . $this->mime_types[$ext]);
		
		if($len == 1){
			return WEBROOT_PATH . $fileName;
		}
		else if($len == 2){
			return WEBROOT_PATH . $arr[0] . DS . $fileName;
		}
		else{
			$app = $arr[0];
			$type = $arr[1];
			$file = "";
			for($i=2;$i<$len;$i++){
				$file .= DS . $arr[$i];
			}
			$filepath = CORE_APP_PATH . $app . DS . "views" . DS . $type  . $file;
			if(file_exists($filepath)){
				readfile($filepath);
				exit();
			}
			else{
				$filepath = APP_PATH . $app . DS . "views" . DS . $type  . $file;
				if(file_exists($filepath)){
					readfile($filepath);
					exit();
				}
			}
		}
		$filepath = WEBROOT_PATH . str_replace("/", DS, $this->url);
		if(file_exists($filepath)){
			readfile($filepath);
			exit();
		}
		else{
			header("HTTP/1.0 404 Not Found");
			exit();
		}
	}
}
?>
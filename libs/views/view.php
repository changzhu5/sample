<?php
require_once 'render.php';
class View extends Object {
	/**
	 * Define layout that view will embbed in layout
	 * @var string
	 * @access private
	 */
	private $layout = null;
	/**
	 * Define page title
	 * @var string
	 * @access private
	 */
	private $pageTitle = '';
	/**
	 * Define data passed from Controller::assign()
	 * @var array
	 * @access private
	 */
	private $vars = array();
	/**
	 * define three kinds of mode that view object works at.
	 * @var int
	 * 0:default,view object will render the template file and return to 
	 * the controller which deal it in afterRender() function.
	 * 1:view object will render the template file and store as .html as buffer then return buffer
	 * 2:view object will directly output the .html buffer without rendering the template file.
	 * 3:view object will render the template file and store it in html without output to the browser
	 * @access private
	 */
	private $mode = 0;
	/**
	 * Define result that rendered with view template.This result will emb to layout
	 * @var string html streaming
	 * @access private
	 */
	private $contents_for_layout;
	/**
	 * Define final html output
	 * @var string html streaming
	 * @access private
	 */
	private $output;
	/**
	 * the pointer of this object
	 * @staticvar View
	 * @access private
	 */
	private static $inst = null;
	public $ext = null;
	public $app;
	public $controller;
	public $action;
	/**
	 * Initialize view object
	 * @return void
	 * @access private
	 */
	private function __construct(){
		
	}
	/**
	 * get instance of this class
	 * @return object $inst property of this class
	 * @access public
	 * @static  
	 */
	public static function getInstance(){
		$request = Context::fetch('request');
		if(is_null(self::$inst)){
			$c = self::$inst = new View();
			
		}
		$c = self::$inst;
		$c->app = $request->getAppName();
		$c->controller = $request->getControllerName();
		$c->action = $request->getActionName();
		return $c;
	}
	/**
	 * Init properties of view object include viewHelper
	 * @param &Controller an quote of Controller object
	 * @return true or false if success return true
	 * @access public
	 * @static
	 */
	public static function init(Controller &$controller){
		$inst = self::getInstance();
		$inst->layout = $controller->layout;
		$inst->pageTitle = $controller->pageTitle;
		$inst->vars = $controller->vars;
		$inst->mode = $controller->mode;
		$inst->ext = $controller->ext;
		$inst->viewPath = $controller->viewPath;
		if(!empty($controller->helpers))
		{
			foreach($controller->helpers as $helper)
			{
				require_once CORE_VIEW_PATH .'helpers' . DS . $helper . '.php';
				$inst->$helper = new $helper();
			}
		}
		return true;
	}
	/**
	 * Render the template file with viewHelper($this->html->link())
	 * @param string $path absolute template file path
	 * @return mix html streaming boolean
	 * @access public
	 * @static
	 */
	public static function render($path){
		$inst = self::getInstance();
	
		switch($inst->mode){
			case 2:
				$html_name = template_to_html_path($path);
				$filepath = $inst->viewPath.'htmls'.DS.$html_name;
				return file_get_contents($filepath);
			case 0:
				return $inst->_render($path);
				break;
			case 1:
				$inst->_generate($path);
				break;
			case 3:
				return $inst->_generate($path,true);
		}
	}
	/**
	 * Render element
	 * @param $element string the name of element
	 * @param $vars array $key=>$value
	 * @return html streaming
	 * @access public
	 */
	public function renderElement($element,$vars=null){
		ob_start();
		if($vars){
			foreach($vars as $key=>$value){
				$$key = $value;
			}	
		}
		$file = $this->viewPath.'elements'.DS.$element.$this->ext;
		include_once $file;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	/**
	 * Render view
	 * @param string $path absolute template file path
	 * @return html streaming
	 * @access private
	 */
	private function _render($path){
		if(!empty($this->vars)){
			foreach($this->vars as $key=>$value)
			{
				$$key = $value;
			}
		}
		if(file_exists($path)){
			if(Configure::read('render')){
				$file_name = $this->controller . '_' . $this->action . '.php';
				$render = new Render($file_name,$path);
				$path = $render->render();
			}
			if($path){
				ob_start();
				include_once $path;
				$this->contents_for_layout = ob_get_contents();
			}
		}
		else
		{
			$arr = explode(DS,$path);
			$file = $arr[count($arr)-1];
			$params = array('file'=>$file);
			$this->appError('missingView',$params);
		}
		ob_end_clean();
		if(!is_null($this->layout)){
			ob_start();
			$path = $this->viewPath.'layouts'.DS.$this->layout.$this->ext;
			if(!file_exists($path)){
				$path = WEBROOT_PATH . 'layouts' . DS . $this->layout . $this->ext;
			}
			$contents_for_layout = $this->contents_for_layout;
			$pageTitle = $this->pageTitle;
			include $path;
			$this->output = ob_get_contents();
			ob_end_clean();
		}
		else{
			$this->output = $this->contents_for_layout;
		}
		return $this->output;
		
	}
	/**
	 * Generate static html file without output
	 * @param string $path absolute template file path
	 * @return void
	 * @access private
	 */
	private function _generate($path,$ifReturn = false){
		$cachePath = $this->viewPath.'htmls'.DS.template_to_html_path($path);
		$output = $this->_render($path);
		file_put_contents($cachePath,$output);
		if($ifReturn)
		{
			return $output;
		}
	}
	/**
	 * Assign varibles to the View
	 * @param string $key
	 * @param mix $value
	 */
	public function assign($key,$value){
		$this->vars[$key] = $value;
	}
	/**
	 * Render Widget
	 * @param string $name widget unique name
	 * @param Mix widget's content
	 * @param boolen $ajax  if current request is ajax
	 * @return string html
	 */
	public function renderWidget($name,$data=null,$ajax=false){
		$data['base'] = Configure::read('base');
		$flag = Configure::read('development');
		if($data){
			foreach($data as $key=>$value){
				$$key = $value;
			}
		}
		/*
		$widget = CORE_VIEW_PATH . 'widgets' . DS . $name . $this->ext;
		if(!file_exists($widget)){
			$this->appError('systemError',array('message'=>'system did not find the widget : ' . $name));
		}
		ob_start();
		include_once $widget;
		$html = ob_get_contents();
		ob_end_clean();
		*/
		$css = CORE_VIEW_PATH . 'css' . DS . $name . '.css';
		if($flag && file_exists($css)){
			$type = '<style type="text/css">/*Widget:'.$name.' Start */';
			$type .= file_get_contents($css);
			$type .='/*Widget:'.$name . ' End */</style>' ;
			$html = $type . $html;
		}
		$js = CORE_VIEW_PATH . 'js' . DS . $name . '.js';
		if($flag && file_exists($js)){
			$type = '<script type="text/javascript">/*Widget:' . $name . ' Start */';
			$type .= file_get_contents($js);
			$type .='/*Widget:'.$name . ' End */</script>' ;
			$html = $html . $type;
		}
		return $html;
	}
	public function renderContents($file,$data=null){
		if($data){
			foreach($data as $key=>$value){
				$$key = $value;
			}
		}
		if(!file_exists($file)){
			$this->appError('systemError',array('message'=>'system did not find the file'));
		}
		ob_start();
		include_once $file;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
?>
<?php
/**
 * A controller is used to manage the logic for a part of your application.Controllers are used to
 * manage the logic for several models.Your application��s controllers are classes that extended
 * the Controller class which is a standard system library in /libs/.Controllers can include any
 * number of methods which are usually referred to as actions.Actions are controller methods
 * used to display views.An action is  single method of a controller.System��s dispatcher calls
 * actions when a coming request matches a URL to controller��s action.
 */
require_once CORE_MODEL_PATH . 'app_model.php';
require_once CORE_VIEW_PATH . 'view.php';

class Controller extends Object{
	/**
	 * Root path
	 */
	public $base;
	/**
	 * App Model Path
	 * @var string
	 * @access public
	 */
	public $modelPath;
	/**
	 * current action name
	 */
	public $action;
	/**
	 * View Path
	 */
	public $viewPath;
	/**
	 * the name of current controller
	 * @var string eg.if class name is PostController,$name is Post
	 * @access public
	 */
	public $name;
	/**
	 * define components which controller will use
	 * @var array
	 * @access public
	 */
	public $components = array();
	/**
	 * define model
	 * @var array
	 * @access public
	 */
	public $model = null;
	/**
	 * define viewHelpers which view object will use
	 * @var array
	 * @access public
	 */
	public $helpers = array('html');
	/**
	 * define layout of view
	 * @var string
	 * @access public
	 */
	public $layout = 'default';
	/**
	 * for temp storage of layout
	 */
	protected $temp_layout;
	/**
	 * define page title
	 * @var string
	 * @access public
	 */
	public $pageTitle;
	/**
	 * define extension name of template file
	 * @var string
	 * @access public
	 */
	public $ext = '.html';
	/**
	 * This variable is used to provide access to information about current request
	 * @var array
	 * @access public
	 */
	public $params = array();
	/**
	 * This stores $key=>$value which will pass to the view object
	 * @var array
	 * @access public
	 */
	public $vars = array();
	/**
	 * define three kinds of mode that view object works at.
	 * @var int
	 * 0:default,view object will render the template file and return the static .html file to 
	 * the controller which deal it in afterRender() function.
	 * 1:view object will render the template file and store as .html as buffer
	 * 2:view object will directly output the .html buffer without rendering the template file.
	 * 3:view object will render the template file without output
	 * @access public
	 */
	public $mode = 0;
	/**
	 * determine whether render the template
	 * @var boolean
	 * @access public
	 */
	public $ifRender = 1;
	/**
	 * define actions which will be cached.$action=>$expiretime,means after $expiretime,the action will
	 * expire
	 * @var array array('action1'=>'expiretime1','action2'=>'expiretime2')
	 * @access public
	 */
	public $cacheActions = array();
	/**
	 * Holds a link to Component and Model manager
	 * @var Component
	 * @access protected
	 */
	protected $component = null;
	/**
	 * The result of html output
	 * @var string
	 * @access public
	 */
	public $output;
	/**
	 * Models
	 */
	public $models = array();
	protected $cache;
	protected $dataPath = null;
	/**
	 * If open to end-users
	 * @var String
	 */
	public $limit = "public";
	/**
	 * Initialize $name $component proerties
	 * @param string $name the name of controller
	 * @return void
	 * @access public
	 */
	public function __construct($request){
		$this->request = $request;
		$app = $this->app = $request->getAppName();
		$name = $this->name = $request->getControllerName();
		$this->action = $request->getActionName();
		$base = $this->base = $request->getAppRoot();
		$this->modelPath = $base . 'models' . DS;
		$this->viewPath = $base . 'views' . DS;
		$this->dataPath  = $base . 'data' . DS;
		if($base){
			$this->cache = Cache::getInstance($app,$name,$base . 'data' . DS);
		}
	}
	/**
	 * assign variables to Controller::$vars property
	 * @param string $key
	 * @param mix $value
	 * @return void
	 * @access public
	 */
	public function assign($key,$value){	
		$this->vars[$key] = $value;
	}
	/**
	 * render the action's referred view template and controll view procudal.
	 * @param $path string eg.controller/action
	 * @return string html output
	 * @access public
	 */
	public function render($path = null){
		if(is_null($path)){
			$path = $this->viewPath . $this->name.DS.$this->action.$this->ext;
		}
		else{
			$path = $this->viewPath . $path . $this->ext;
		}
		if($this->beforeRender()){
			
			View::init($this);	
			$this->output = View::render($path);
			$this->afterRender();
			return $this->output;
			 
		}
		
		return null;
		 
	}
	/**
	 * Load models
	 */
	private function loadModels(){
		if($this->models){
			foreach($this->models as $model){
				if(!$this->get($model)){
					$path = $this->modelPath . $model . '_model.php';
					if(!file_exists($path)){
						$this->appError('model:'.$model . ' does not exist');
					}
					require_once $path;
					$class = ucfirst($model) . 'Model';
					$this->$model = new $class($this->request);
				}
			}
		}
		$this->model = new AppModel($this->request);
	}
	/**
	 * Render Widget
	 * @param string $name widget unique name
	 * @param Mix widget's content
	 * @return string html
	 */
	public function getWidget($name,$data=null){
		if(!$app){
			$app = $this->app;
		}
		$view = View::getInstance();
		$view->ext = $this->ext;
		return $view->renderWidget($name,$data);
	}
	/**
	 * render an element
	 * @param $name string the name of element
	 */
	public function renderElement($name,$layout=null){
		$path = $this->viewPath . 'elements' . DS . $name . $this->ext;
		$this->layout = $layout;
		return $this->render($path,false);
	}
	/**
	 * In this function controller will initialize components and models and check the cache object
	 * @return boolean true or false
	 * @access public
	 */
	public function beforeAction(){
		$charset = Configure::read('charset');
		header("Content-Type:text/html;charset=$charset");
		$action = $this->action;
		if(isset($this->cacheActions[$action])){
			$status = $this->cache->check($this->name,$action,$this->cacheActions[$action]);
			switch($status){
				case 'non-expired':
					$this->mode = 2;
					return $this->render();
					break;
				case 'expired':
					$this->mode = 3;
					break;
				case 'unset':
					$now = time();
					$this->cache->setMap($this->name,$action,$now);
					$this->mode = 3;
					break;
			}
		}
		return true;
	}
	/**
	 * This function will be invoked after action's render
	 * @return boolean true or false
	 * @access public
	 */
	public function afterAction(){
		if($this->ifRender){
			return $this->render();
		}
	}
	/**
	 * This function will be called after action
	 * @return boolean true or false
	 * @access public
	 */
	public function beforeRender(){
		$dispatcher = Context::fetch('dispatcher');
		$this->assign('base', Configure::read('base'));
		$this->assign('title',$this->pageTitle);
		$this->assign('appBase',$dispatcher->getWebRoot($this->app));
		$this->temp_layout = $this->layout;
		return true;
	}
	/**
	 * This function will be called after render
	 * @return boolean true or false
	 * @access protected
	 */
	protected function afterRender(){
		$this->layout = $this->temp_layout;
		$this->mode = 0;
		$this->ifRender = 1;
	}
	/**
	 * From one controller redirect to other controller
	 * @param string $url eg.controller/action/param1/param2/...
	 * @return void
	 * @access public
	 */
	public function redirect($url,$flag=true){	
		$this->ifRender = 0;
		$url = getPermalink($url);
		header('Location:' . $url);
		if($flag){
			exit();
		}
	}
	/**
	 * Initialize controller's model and components
	 */
	public function init(){
		$this->loadModels();
		if(!empty($this->components)){
			foreach($this->components as $name=>$option){
				$obj = Factory::getObject('component',$name,$this);
				$obj->startup($option);
				$this->$name = $obj;
			}
		}
	}
}
?>
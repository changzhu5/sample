<?php 
require_once CORE_MODEL_PATH . 'entity.php';
require_once CORE_MODEL_PATH . 'model.php';
class AppModel extends Model{
	/**
	 * @var Request
	 */
	protected $request = null;
	/**
	 * Array of Model objects
	 * @var Assoc Array
	 */
	protected $models = array();
	public function __construct($request){
		$this->setRequest($request);
	}
	public function setRequest($request){
		$this->request = $request;
	}
	/**
	 * Get Entity Object
	 * @param String $name entity name
	 * @return Entity Object
	 */
	public function getEntity($name){
		$arr = explode(".",$name);
		if($arr[0] == $name){
			$appRoot = $this->request->getAppRoot();
		}
		else{
			$appName = $arr[0];
			$name = $arr[1];
			$dispatcher = Context::fetch('dispatcher');
			$appRoot = $dispatcher->getAppRoot($appName);
		}
		$path = $appRoot . 'models' . DS . 'entities' . DS . $name . ".php";
		if(!file_exists(($path))){
			$this->appError("error on getting entity");
		}
		require_once $path;
		$class = ucfirst($name);
		return new $class();
	}
	/**
	 * Call function in the model
	 */
	public function callService($app,$modelName,$action,$params=null){
		
	}
	/**
	 * Get all entity object
	 * @return Entity Objects
	 */
	public function getAllEntities(){
		$appRoot = $this->request->getAppRoot();
		$path = $appRoot . 'models' . DS . 'entities' . DS;
		$files = scandir($path);
		$len = count($files);
		if($len > 2){
			$entities = null;
			for($i=2;$i<$len;$i++){
				$filepath = $path . $files[$i];
				$path_parts = pathinfo($filepath);
				$name = $path_parts['filename'];
				$className = ucfirst($name);
				require_once $filepath;
				$entities[] = new $className();
			}
			return $entities;
		}
		return null;
	}
	/**
	 * Uninstall entities
	 * @return void
	 */
	public function uninstall(){
		$entities = $this->getAllEntities();
		if($entities){
			foreach($entities as $entity){
				$entity->uninstall();
			}
		}
	}
	/**
	 * Install entities
	 * @return void
	 */
	public function setup(){
		$app = $this->request->getAppName();
		$entities = $this->getAllEntities();
		$flag = true;
		if($entities){
			foreach($entities as $entity){
				if(!$entity->install($app)){
					$flag = false;
				}
			}
		}
		return $flag;
	}
	/**
	 * Get model object
	 * @param String $appName
	 * @param String $modelName
	 * @return Model
	 */
	public function getModel($appName,$modelName){
		if(isset($this->models[$appName][$modelName])){
			return $this->models[$appName][$modelName];
		}
		$dispatcher = Context::fetch('dispatcher');
		$appRoot = $dispatcher->getAppRoot($appName);
		if(!$appRoot){
			$this->appError('app:' .$appName . ' does not exist');
		}
		$file = $appRoot . 'models' . DS . $modelName . '_model.php';
		if(!file_exists($file)){
			$this->appError('model:'.$modelName . ' does not exist');
		}
		require_once $file;
		$className = ucfirst($modelName) . 'Model';
		$modelObj = new $className($this->request);
		$this->models[$appName][$modelName] = $modelObj;
		return $modelObj;
	}
}
?>
<?php
class Factory extends Object{
	/**
	 * The records of instantiation.Format:
	 * array('model'=>array(model1=>obj,model2=>obj),
	 *		 'component'=>array(com1=>obj,com2=>obj)
	 * 		)
	 * @var array
	 * @access private
	 */
	private $records = array();
	private static $inst=null;
	/**
	 * Instantiate Model
	 * @param string $name name of model
	 * @return Model
	 * @access private
	 */
	private function initModel($name){
		require_once APP_MODEL_PATH.$name.'_model.php';
		$classname = $name.'Model';
		$obj = new $classname($name);
		$this->records['model'][$name] = $obj;
		return $obj;
	}
	/**
	 * Instantiate Component
	 * @param string $name name of component
	 * @return Component
	 * @access private
	 */
	private function initComponent($name,$controller){	
		$inst = self::getInstance();
		$path = COMPONENT_PATH.$name.'.php';
		require_once($path);
		$obj = new $name($name,$controller);
		$this->records['component'][$name] = $obj;
		return $obj;
	}
	public static function getInstance(){
		if(is_null(self::$inst)){
			self::$inst = new Factory();
		}
		return self::$inst;
	}
	/**
	 * Get object
	 *
	 * @param string $type model or component
	 * @param string $name
	 * @return Object
	 * @access public
	 * @static 
	 */
	public static function getObject($type,$name,$controller=null){
		$inst = self::getInstance();
		if(isset($inst->records[$type][$name]))
		{
			return $inst->records[$type][$name];
		}
		$method = 'init'.$type;
		switch($type)
		{
			case 'model':
				return $inst->{$method}($name);
			case 'component':
				return $inst->{$method}($name,$controller);
		}
	}
	/**
	 * Load file
	 * @param String $appName
	 * @param String relative path
	 * @return void
	 */
	public static function import($appName,$path){
		$dispatcher = Dispatcher::getInstance();
		$appRoot = $dispatcher->getAppRoot($appName);
		if($appRoot){
			$file = $appRoot . $path;
			if(file_exists($file)){
				require_once $file;
			}
		}
	} 
}
?>
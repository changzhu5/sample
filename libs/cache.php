<?php
/**
 * The cache mechanism will use file based system.Cache object
 * will be called as such conditions:
 * The requested controller will call the cache object according
 * to its cacheActions property in its method beforeAction().
 * Db object will cache sql query.
 * database driver will cache table information.
 */
class Cache extends Object{
	/**
	 * The name of cache object
	 * @var string
	 */
	private $name;
	/**
	 * The records of controller's action and its expiretime.
	 * The records of model's action and flag that data was updated or not
	 * @var array 
	 */
	private $map = array('Controller'=>null);
	/**
	 * Cache variables
	 * @var array
	 */
	private $vars = array();
	/**
	 * Define cache path and file name
	 * @var string
	 */
	private $cachePath ;
	/**
	 * the collection of cache object
	 * @staticvar Cache
	 */
	private static $inst = array();
	/**
	 * Initialize the Cache object
	 * @param string $cachePath absolute cache path
	 */
	private function __construct($app,$name,$cachePath){
		$this->app = $app;
		$this->name = $name;
		$this->cachePath = $cachePath;
	}
	/**
	 * get the instance of cache object according to object's name
	 * @return Cache
	 * @static
	 */
	public static function getInstance($app,$name,$path){
		if(!isset(self::$inst[$app][$name])){
			$cachePath = $path . $name . '.txt'; 
			if(!file_exists($cachePath)){
				touch($cachePath);
				$cache_object = new Cache($app,$name,$cachePath);
				self::$inst[$app][$name] = $cache_object;
				file_put_contents($cachePath,serialize($cache_object));
			}
			else{
				$str = file_get_contents($cachePath);
				self::$inst[$app][$name] = unserialize($str);
			}
			self::$inst[$app][$name]->setCachePath($cachePath);
		}
		return self::$inst[$app][$name];
	}
	/**
	 * check cache whether controller's action is expired
	 * @param string $class the current class's name Controller::$name
	 * @param string $name the action's name
	 * @param int $time the action's expiretime
	 * @return boolean if expired return true;
	 * @access public
	 * @static
	 */
	public function check($class,$name,$time){
		$mktime = $this->map['Controller'][$class][$name];
		if(empty($mktime))
			return 'unset';
		$now = time();
		if($now - $mktime > $time)
			return 'expired';
		else
			return 'non-expired';
		
	}
	/**
	 * reset value of controller's action time or model's method flag which stands for if current method's
	 * data is updated
	 * @param string $class the current class's name Controller::$name
	 * @param string $name the action's name
	 * @param mix $time expiretime
	 * @return boolean true or false
	 * @access public
	 */
	public function setMap($class,$name,$time){
		$this->map['Controller'][$class][$name] = $time;
		return $this->saveCache();
	}
	/**
	 * Stores variables to the cache object
	 *
	 * @param string $key
	 * @param mix $value
	 * @param string $class
	 */
	public function setVar($key,$value,$class=null){
		if(is_null($class))
			$this->vars[$key] = $value;
		else
			$this->vars[$class][$key] = $value;
		return $this->saveCache();
	}
	/**
	 * Gets variable in the cache object
	 * @param string $key
	 * @param string $class
	 * @return mix
	 */
	public function getVar($key,$class=null){
		if(is_null($class)){
			if(isset($this->vars[$key]))
				return $this->vars[$key];
		}
		else{
			if(isset($this->vars[$class][$key]))
				return $this->vars[$class][$key];
		}
		return null;
	}
	/**
	 * Clear variable in the cache object
	 *
	 * @param string $key
	 * @param string $class
	 */
	public function clearVar($key,$class=null){
		if(!is_null($class))
			unset($this->vars[$class][$key]);
		else
			unset($this->vars[$key]);
		$this->saveCache();
	}
	public function getCachePath(){
		return $this->cachePath;
	}
	public function setCachePath($cachePath){
		$this->cachePath = $cachePath;
	}
	private function saveCache(){
		$str = serialize(self::$inst[$this->app][$this->name]);
		file_put_contents($this->cachePath,$str);
	}
}
?>
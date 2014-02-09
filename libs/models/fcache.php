<?php
require_once CORE_MODEL_PATH . 'record.php';
require_once CORE_MODEL_PATH . 'cache_interface.php';
class Fcache extends Object implements CacheInterface{
	private static $inst;
	/**
	 * Default time to live
	 */
	private $ttl = 600;
	private $cachePath;
	private $cache = array();
	private $ext = '.txt';
	private function __construct(){
		
	}
	public static function getInstance(){
		if(is_null(self::$inst)){
			self::$inst = new Fcache();
			self::$inst->setCachePath(CORE_MODEL_PATH . 'cache' . DS);
		}
		return self::$inst;
	}
	/**
	 * Set cache base
	 * @param String $path absolute path
	 * @return void
	 */
	private function setCachePath($path){
		$this->cachePath = $path;
	}
	/**
	 * Read cache from file
	 * @param String entity name
	 * @return void
	 */
	private function loadCache($entityName){
		$file = $this->cachePath . $entityName . $this->ext;
		if(!file_exists($file)){
			touch($file);
			$this->cache[$entityName] = array();
			return array();
		}
		if(!isset($this->cache[$entityName])){
			$this->cache[$entityName] = objectToArray(json_decode(file_get_contents($file)));
		}
	}
	/**
	 * Write data to the file
	 * @param String entity name
	 * @return void
	 */
	private function writeCache($entityName){
		$file = $this->cachePath . $entityName . $this->ext;
		if(!file_exists($file)){
			touch($file);
			$this->cache[$entityName] = array();
		}
		if(isset($this->cache[$entityName])){
			return file_put_contents($file, json_encode($this->cache[$entityName]));
		}
	}
	/**
	 * Cache Record Object
	 * @param Record $record
	 * @param Int $ttl
	 * @return void
	 */
	public function cacheRecord($record,$ttl=null){
		if(!$ttl){
			$ttl = $this->ttl;
		}
		$entity = $record->getEntity();
		$entityName = $entity->getName();
		$id = $record->getId();
		$fields = $record->getFields();
		$data = $record->getData();
		$this->loadCache($entityName);
		$this->cache[$entityName][$id] = array('fields'=>$fields,'data'=>$data,'expire'=>time() + $ttl);
		$this->writeCache($entityName);
		
	}
	/**
	 * Read record by id
	 * @param String $entityName
	 * @param String or Int $id
	 * @return Record Object
	 */
	public function readRecord($entityName,$id){
		$this->loadCache($entityName);
		if(!isset($this->cache[$entityName][$id])){
			return null;
		}
		if($this->cache[$entityName][$id]['expire'] < time()){
			unset($this->cache[$entityName][$id]);
			$this->writeCache($entityName);
			return null;
		}
		$delegator = Context::fetch("delegator");
		$entity = $delegator->findEntity($entityName);
		return new Record($entity,$this->cache[$entityName][$id]['fields'],$this->cache[$entityName][$id]['data']);
	}
	/**
	 * Remove record
	 * @param Record $record
	 * @return void
	 */
	public function clearRecord($record){
		$entity = $record->getEntity();
		$entityName = $entity->getName();
		$id = $record->getId();
		$this->loadCache($entityName);
		if(isset($this->cache[$entityName][$id])){
			unset($this->cache[$entityName][$id]);
			$this->writeCache($entityName);
		}
	}
	/**
	 * Remove record by id
	 * @param String $entityName
	 * @param String or Int $id
	 * @return void
	 */
	public function clearRecordById($entityName,$id){
		$this->loadCache($entityName);
		if(isset($this->cache[$entityName][$id])){
			unset($this->cache[$entityName][$id]);
			$this->writeCache($entityName);
		}
	}
	
}
?>
<?php
require_once CORE_MODEL_PATH . 'record.php';
require_once CORE_MODEL_PATH . 'cache_interface.php';
class Xcache extends Object implements CacheInterface{
	private static $inst;
	/**
	 * Default time to live
	 */
	private $ttl = 600;
	private function __construct(){
		
	}
	public static function getInstance(){
		if(is_null(self::$inst)){
			self::$inst = new Xcache();
		}
		return self::$inst;
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
		$id = $record->getId();
		$fields = $record->getFields();
		$data = $record->getData();
		$cache = array('fields'=>$fields,'data'=>$data);
		$key = $entity->getName() . "_" . $id;
		xcache_set($key,json_encode($cache),$this->ttl);
	}
	/**
	 * Read record by id
	 * @param String $entityName
	 * @param String or Int $id
	 * @return Record Object
	 */
	public function readRecord($entityName,$id){
		$key = $entityName . "_" . $id;
		$val = xcache_get($key);
		if(!$val){
			return null;
		}
		$cache = json_decode($val);
		$delegator = Context::fetch("delegator");
		$entity = $delegator->findEntity($entityName);
		return new Record($entity,$cache->fields,$cache->data);
	}
	/**
	 * Remove record
	 * @param Record $record
	 * @return void
	 */
	public function clearRecord($record){
		$entity = $record->getEntity();
		$id = $record->getId();
		$key = $entity->getName() . "_" . $id;
		xcache_unset($key);
	}
	/**
	 * Remove record by id
	 * @param String $entityName
	 * @param String or Int $id
	 * @return void
	 */
	public function clearRecordById($entityName,$id){
		$key = $entityName . "_" . $id;
		xcache_unset($key);
	}
	
}
?>
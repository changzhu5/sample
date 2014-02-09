<?php
/**
 * Generic Value Class
 */
require_once CORE_MODEL_PATH . 'xcache.php';
class Record extends Object{
	/**
	 * The link to database object
	 * @var MySQL Object
	 * @access protected
	 */
	protected $db = null;
	/**
	 * the last insert record id
	 */
	protected $id = "";
	/**
	 * Fields
	 */
	private $fields = array();
	/**
	 * Entity Object
	 */
	private $entity = null;
	/**
	 * Data
	 * @var indexed array
	 */
	private $data = array();
	/**
	 * xcache object
	 */
	private $xcache = null;
	public function __construct($entity,$fields=null,$data=null){
		$delegator = Context::fetch("delegator");
		$this->db = $delegator->getDb();
		$cacheMethod = Configure::read('cache-method');
		require_once CORE_MODEL_PATH . $cacheMethod . '.php';
		$cacheMethod = ucfirst($cacheMethod);
		$this->xcache = $cacheMethod::getInstance();
		$this->entity = $entity;
		if(!$fields){
			$this->fields = $entity->getFields();
		}
		else{
			$this->fields = $fields;
		}
		if(!$data){
			$this->data = $entity->getDefaultData($fields);
		}
		else{
			$this->data = $data;
		}
		$key = array_search($entity->getPrimaryKey(), $this->fields);
		if(is_bool($key) && !$key){
			return;
		}
		$id = $this->data[$key];
		$id = trim($id,"'");
		$this->id = $id;
	}
	/**
	 * Get record columns
	 * @return Indexed Array
	 */
	public function getFields(){
		return $this->fields;
	}
	/**
	 * Get the value of single cell
	 * @param String $field
	 * @return String
	 */
	public function getValue($field){
		if(!in_array($field,$this->fields)){
			return null;
		}
		$key = array_search($field, $this->fields);
		$value = trim($this->data[$key],"'");
		
		return $value;
	}
	/**
	 * Get record data
	 * @return Indexed Array
	 */
	public function getData(){
		return $this->data;
	}
	/**
	 * Set cell value
	 * @param String $field
	 * @param String or Int $value
	 */
	public function setValue($field,$value){
		if(!in_array($field,$this->fields)){
			$this->appError("Field $field does not exist in the table " . $this->entity->getName());
		}
		$key = array_search($field, $this->fields);
		$this->data[$key] = $value;
	}
	/**
	 * Add new record to database
	 * @return boolean
	 */
	public function create(){
		$sql = $this->entity->getSql();
		if(!$this->entity->validate($fields,$data)){
			$this->appError("Invalid data exist for fields:" . json_encode($fields));
		}
		$queryStr = $sql->getInsert(array(
			"table"=>$this->entity->getName(),
			"fields"=>$this->fields,
			"values"=>$this->data
		));
		if($this->db->query($queryStr)){
			$this->id = $this->db->getLastInsertId();
			if($this->entity->ifCache()){
				$this->xcache->cacheRecord($this);
			}
			return true;
		}
		return false;
	}
	/**
	 * Get primary value
	 * @return String or int
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * Get Entity Object
	 * @return Entity Object
	 */
	public function getEntity(){
		return $this->entity;
	}
	/**
	 * Update record in the table
	 * @return boolean
	 */
	public function save(){
		$sql = $this->entity->getSql();
		$option = array('table'=>$this->entity->getName());
		foreach($this->fields as $key=> $field){
			$v = $this->data[$key];
			$option['set'][$field] =$v;
		}
		$option['conditions'] = $this->entity->getPrimaryKey() . "=" . mysql_escape_string($this->id);
		$queryStr = $sql->getUpdate($option);
		if($this->db->query($queryStr)){
			if($this->entity->ifCache()){
				$this->xcache->cacheRecord($this);
			}
			return true;
		}
		return false;
	}
	/**
	 * Get related record
	 * @param String $entityName
	 * @return Record Object
	 */
	public function getRelatedOne($entityName){
		if(!$relation = $this->entity->checkBelongsTo($entityName)){
			return false;
		}
		$foreignKey = $relation['foreignKey'];
		$from = $relation['from'];
		$request = Context::fetch("request");
		$delegator = $request->getDelegator();
		$foreignEntity = $delegator->findEntity($from);
		if($foreignEntity->ifCache()){
			$record = $this->xcache->readRecord($entityName,$this->getValue($foreignKey));
			if($record){
				return $record;
			}
		}
		$record = $delegator->findByPK($foreignEntity->getName(),$this->getValue($foreignKey));
		if($foreignEntity->ifCache()){
			$this->xcache->cacheRecord($record);
		}
		return $record;
	}
	public function getRelatedMany($entityName){
		if(!$relation = $this->entity->checkHasMany($entityName) && !$relation = $this->entity->checkManyToMany($entityName)){
			return false;
		}
		$foreignKey = $relation['foreignKey'];
		$to = $relation['to'];
		$request = Context::fetch("request");
		$delegator = $request->getDelegator();
		$foreignEntity = $delegator->findEntity($to);
		$records = $delegator->find($entityName,array($foreignKey,'eq',$this->getId()));
		return $records;
	}
	/**
	 * Get related records
	 * @param String $entityName
	 * @param Indexed Array $orderby
	 * @param Indexed Array $limit
	 * @return Record Objects
	 */
	public function getRelatedMulti($entityName,$orderby=null,$limit=null){
		if(!$relation = $this->entity->checkHasMany($entityName)){
			return false;
		}
		$foreignKey = $relation['foreignKey'];
		$request = Context::fetch("request");
		$delegator = $request->getDelegator();
		$foreignEntity = $delegator->findEntity($entityName);
		$records = $delegator->find($entityName,array($foreignKey,'eq',$this->getId()),$orderby,$limit);
		return $records;
	}
	/**
	 * Delete records
	 * @return boolean
	 */
	public function remove(){
		$sql = $this->entity->getSql();
		$option = array('table'=>$this->entity->getName(),'conditions'=>$sql->where(array($this->entity->getPrimaryKey(),'eq',$this->getId())));
		$query = $sql->getDelete($option);
		if($this->db->query($query)){
			$this->xcache->clearRecord($this);
			return true;
		}
		return false;
	}
	/**
	 * From record to array
	 * @return Assoc Array
	 */
	public function toArray(){
		$arr = null;
		foreach($this->fields as $k=>$v){
			$arr[$v] = $this->data[$k];
		}
		return $arr;
	}
	/**
	 * From record to json
	 * @return String
	 */
	public function toJson(){
		$arr = $this->toArray();
		return json_encode($arr);
	}
}
?>
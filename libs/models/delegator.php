<?php
/**
 * Generic Delegator
 */
require_once CORE_MODEL_PATH . 'record.php';
require_once CORE_MODEL_PATH . 'app_model.php';
require_once CORE_MODEL_PATH . 'mysql.php';

class Delegator extends Object{
	/**
	 * The link to database object
	 * @var MySQL Object
	 * @access protected
	 */
	protected $db = null;
	/**
	 * the last insert record id
	 */
	public $last_insert_id = "";
	/**
	 * Object holder
	 */
	private static $inst;
	/**
	 * Current request object
	 */
	private $request = null;
	/**
	 * xcache object
	 */
	private $xcache = null;
	/**
	 * Model Object
	 */
	 private $model = null;
	/**
	 * Private constructor
	 */
	private function __construct(){
		$cacheDir = Configure::read('base') . 'tmp/';
		$cacheMethod = Configure::read('cache-method');
		require_once CORE_MODEL_PATH . $cacheMethod . '.php';
		$cacheMethod = ucfirst($cacheMethod);
		$this->xcache = $cacheMethod::getInstance();
	}
	/**
	 * Get object instance
	 */
	public static function getInstance($request){
		if(is_null(self::$inst)){
			self::$inst = new Delegator();
			self::$inst->setRequest($request);
		}
		return self::$inst;
	}
	public function setRequest($req){
		$this->request = $req;
		$this->db = Mysql::getInstance($req->getAppName());
		if(self::$inst->model){
			self::$inst->model->setRequest($req);
		}
		else{
			self::$inst->model = new AppModel($req);
		}
	}
	/**
	 * Create record
	 * @param String $entityName entity name
	 * @param Array $fields indexed
	 * @param Array $data indexed
	 * @return Record Object
	 */
	public function create($entityName, $fields=null, $data=null){
		$entity = $this->model->getEntity($entityName);
		$entityFields = $entity->getFields();
		if(!$fields){
			$fields = $entityFields;
		}
		else{
			foreach($fields as $field){
				if(!in_array($field,$entityFields)){
					$this->appError("Field $field does not exist in the table " . $entity->getName());
				}
			}
		}
		if(!$data){
			$data = $entity->getDefaultData($fields);
		}
		$record = new Record($entity,$fields,$data);
		if($record->create()){
			return $record;
		}
		return false;
	}
	/**
	 * Find one record
	 * @param String $entityName entity name
	 * @param Int or String $id 
	 * @return Record Object
	 */
	public function findOne($entityName,$wheres){
		$entity = $this->model->getEntity($entityName);
		$sql = $entity->getSql();
		$option = array('table'=>$entity->getName());
		$option['fields'] = $entity->getFields();
		if($entity->ifCache()){
			$option['hints'] = "SQL_CACHE";
		}
		else{
			$option['hints'] = "SQL_NO_CACHE";
		}
		$option['conditions'] = $sql->whereAnd($wheres);

		$query = $sql->getSelect($option);
		$data = $this->db->select($query,"row");
		if(!$data){
			return null;
		}
		
		$record = new Record($entity,$option['fields'],$data[0]);
		return $record;
	}
	/**
	 * Find records
	 * @param String $entityName entity name
	 * @param Array $where assoc
	 * @param Array $order assoc
	 * @param Int $limit number
	 * @return Array of Record Object
	 */
	public function find($entityName,$where=null,$order=null,$limit=null){
		$entity = $this->model->getEntity($entityName);
		$sql = $entity->getSql();
		$option = array('table'=>$entity->getName());
		$option['fields'] = $entity->getFields();
		if($entity->ifCache()){
			$option['hints'] = "SQL_CACHE";
		}
		else{
			$option['hints'] = "SQL_NO_CACHE";
		}
		if($where){
			$option['conditions'] = $sql->where($where);
		}
		if($order){
			$option['order'] = $sql->orderBy($order);
		}
		if($limit){
			$option['limit'] = $sql->limit($limit);
		}
		$query = $sql->getSelect($option);
		$data = $this->db->select($query,"row");
		if(!$data){
			return null;
		}
		$records = null;
		foreach($data as $d){
			$record = new Record($entity,$option['fields'],$d);
			$records[] = $record;
		}
		return $records;
	}
	/**
	 * Find records by condition list
	 * @param String $entityName entity name
	 * @param Array $wheres indexed array of where options
	 * @return Array of Record Object
	 */
	public function findByAnd($entityName,$wheres){
		$entity = $this->model->getEntity($entityName);
		$sql = $entity->getSql();
		$option = array('table'=>$entity->getName(),'fields'=>$entity->getFields());
		if($entity->ifCache()){
			$option['hints'] = "SQL_CACHE";
		}
		else{
			$option['hints'] = "SQL_NO_CACHE";
		}
		$conditions = "";
		foreach($wheres as $where){
			$c = $sql->where($where);
			$conditions .= $c . " AND ";
		}
		$conditions = rtrim($conditions," AND ");
		$option['conditions'] = $conditions;
		$query = $sql->getSelect($option);
		$data = $this->db->select($query,"row");
		if(!$data){
			return null;
		}
		$records = null;
		foreach($data as $d){
			$records[] = new Record($entity,$option['fields'],$d);
		}
		return $records;
	}
	/**
	 * Find records by condition list
	 * @param String $entityName entity name
	 * @param Array $wheres indexed array of where options
	 * @return Array of Record Object
	 */
	public function findByOr($entityName,$wheres){
		$entity = $this->model->getEntity($entityName);
		$sql = $entity->getSql();
		$option = array('table'=>$entity->getName(),'fields'=>$entity->getFields());
		if($entity->ifCache()){
			$option['hints'] = "SQL_CACHE";
		}
		else{
			$option['hints'] = "SQL_NO_CACHE";
		}
		$conditions = "";
		foreach($wheres as $where){
			$c = $sql->where($where);
			$conditions .= $c . " OR ";
		}
		$conditions = rtrim($conditions," OR ");
		$option['conditions'] = $conditions;
		$query = $sql->getSelect($option);
		$data = $this->db->select($query,"row");
		if(!$data){
			return null;
		}
		$records = null;
		foreach($data as $d){
			$records[] = new Record($entity,$option['fields'],$d);
		}
		return $records;
	}
	/**
	 * Find a record
	 * @param String $entityName entity name
	 * @param String or Int $value
	 * @return Record Object
	 */
	public function findByPK($entityName,$value){
		$entity = $this->model->getEntity($entityName);
		if($entity->ifCache()){
			$record = $this->xcache->readRecord($entityName,$value);
			if($record){
				return $record;
			}
		}
		$sql = $entity->getSql();
		$option = array('table'=>$entity->getName(),'fields'=>$entity->getFields(),'conditions'=>$sql->where(array($entity->getPrimaryKey(),'eq',$value)));
		$query = $sql->getSelect($option);
		$data = $this->db->select($query,'row');
		if(!$data){
			return null;
		}
		$record =  new Record($entity,$option['fields'],$data[0]);
		if($entity->ifCache()){
			$this->xcache->cacheRecord($record);
		}
		return $record;
	}
	/**
	 * Find Entity Object
	 * @param String $entityName entity name
	 * @return Entity Object
	 */
	public function findEntity($entityName){
		return $this->model->getEntity($entityName);
	}
	/**
	 * Remove records by Ids
	 * @param String $entityName entity name
	 * @param Array $ids indexed array
	 * @return boolean
	 */
	public function remove($entityName,$ids){
		$entity = $this->model->getEntity($entityName);
		if($entity->ifCache()){
			foreach($ids as $id){
				$this->xcache->clearRecordById($entity->getName(),$id);
			}
		}
		$sql = $entity->getSql();
		$option = array(
			'table'=>$entity->getName(),
			'conditions'=>$sql->where($entity->getPrimaryKey(),'in',$ids)
		);
		$query = $sql->getDelete($option);
		return $this->db->query($query);
	}
	/**
	 * Conduct a transaction
	 * @param Indexed Array $options
	 * @return Boolean
	 */
	public function transact($options){
		$this->db->start();
		foreach($options as $option){
			$record = $option['record'];
			$action = $option['action'];
			switch($action){
				case 'create':
					if(!$record->create()){
						$this->db->rollback();
						return false;
					}
					break;
				case 'save':
					if(!$record->save()){
						$this->db->rollback();
						return false;
					}
					break;
				case 'remove':
					if($record->remove()){
						$this->db->rollback();
						return false;
					}
					break;
			}
		}
		$this->db->commit();
		return true;
	}
	/**
	 * Get xcache Object
	 * @return Cache Object
	 */
	 public function getCache(){
	 	return $this->xcache;
	 }
	 /**
	  * Get db object
	  * @return Mysql Object
	  */
	  public function getDb(){
	  	return $this->db;
	  }
	  
}
?>
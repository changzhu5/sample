<?php
require_once CORE_MODEL_PATH . 'mysql.php';
require_once CORE_MODEL_PATH . 'sql.php';
abstract class Entity extends Object{
	/**
	 * entity name
	 */
	protected $name = null;
	/**
	 * fields definition
	 */
	protected $fields = array();
	/**
	 * primary key
	 */
	protected $primaryKey = "id";
	/**
	 * Storage Engine
	 */
	protected $engine = "MyISAM";
	/**
	 * Index
	 */
	protected $index = array();
	/**
	 * belongs to relation
	 */
	protected $belongsTo = array();
	/**
	 * Many to many relation
	 */
	protected $manyToMany = array();
	/**
	 * One to many
	 */
	protected $hasMany = array();
	/**
	 * One to one
	 */
	protected $hasOne = array();
	/**
	 * If cahce this table
	 */
	protected $cache = true;
	
	public function __construct(){
		$this->sql = new Sql();
		$className = $this->toString();
		$this->name = strtolower($className);
	}
	/**
	 * Get table fields
	 * @return indexed array
	 */
	public function getFields(){
		$fields = null;
		foreach($this->fields as $f=>$info){
			$fields[] = $f;
		}
		return $fields;
	}
	/**
	 * Get table fields and info
	 * @return assoc array
	 */
	public function getFieldsInfo(){
		return $this->fields;
	}
	/**
	 * Get table primary key
	 * @return string
	 */
	public function getPrimaryKey(){
		return $this->primaryKey;
	}
	/**
	 * Get foreign key
	 * @param String $entityName
	 */
	public function getForeignKey($entityName){
		if(!$this->belongsTo){
			return null;
		}
		foreach($this->belongsTo as $b){
			if($b['from'] == $entityName){
				return $b['foreignKey'];
			}
		}
		return null;
	}
	/**
	 * Validate data
	 * @param Indexed Array $fields
	 * @param Indexed Array $data
	 * @return Boolean
	 */
	public function validate($fields,$data){
		$flag = true;
		if($fields){
			foreach($fields as $index=>$field){
				if(!array_key_exists($field, $this->fields)){
					$this->appError($field . ' is not exist in entity:' . $this->name);
				}
				$data = trim($data[$index],"'");
				$fieldDef = $this->fields[$field];
				$func = 'validate' . ucfirst($fieldDef['type']);
				$flag = $this->$func($data);
			}
		}
		return $flag;
	}
	/**
	 * Validate email
	 */
	protected function validateEmail($d){
		return true;
	}
	/**
	 * Validate ip
	 */
	protected function validateIp($d){
		return true;
	}
	/**
	 * Validate integer
	 */
	protected function validateInt($d){
		if(!is_integer($d)){
			return false;
		}	
		return true;
	}
	/**
	 * Validate string
	 */
	protected function validateChar($d){
		if(!is_string($d)){
			return false;
		}
		return true;
	}
	/**
	 * Validate date
	 */
	protected function validateDate($d){
		return true;
	}
	/**
	 * Validate time
	 */
	protected function validateTime($d){
		return true;
	}
	/**
	 * Validate timestamp
	 */
	protected function validateTimestamp($d){
		if(!is_integer($d)){
			return false;
		}
		if($d < 0){
			return false;
		}
		return true;	
	}
	/**
	 * Install table
	 * @param string $app app name
	 * @return boolean
	 */
	public function install($app){
		$str = "CREATE TABLE IF NOT EXISTS " . $this->name . "(";
		foreach($this->fields as $name=>$option){
			$str .= $this->getFieldDef($name,$option) . ",";
		}
		if(is_array($this->primaryKey)){
			$str .= "PRIMARY KEY(" . implode(",", $this->primaryKey) . "),";
		}
		else{
			$str .= "PRIMARY KEY(" . $this->primaryKey . "),";
		}
		if($this->index){
			foreach($this->index as $type=>$index){
				$str .= "INDEX " . $index['name'] . "(" . implode(",",$index['fields']) . ") USING " . $type . ",";
			}
		}
		$str = rtrim($str,',') . ")";
		$str .= "ENGINE=" . $this->engine . " CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$mysql = Mysql::getInstance($app);
		return $mysql->query($str);
	}
	/**
	 * Get field definition
	 * @param String $name field name
	 * @param Array $option field option
	 * @return String
	 */
	private function getFieldDef($name,$option){
		$str = $name;
		switch($option['type']){
			case "int":
				$str .= " INT";
				break;
			case "timestamp":
				$str .= " TIMESTAMP";
				break;
			case "text":
				$str .= " TEXT";
				break; 
			default:
				$str .= " CHAR";
				break;
		}
		if($option['length']){
			$str .= "(" . $option["length"] . ")";
		}
		switch($option["default"]){
			case "null":
				$str .= " NULL";
				break;
			case "auto":
				if($option["type"] == "int"){
					$str .= " AUTO_INCREMENT";
				}
				else if($option["type"] == "timestamp"){
					$str .= " DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
				}
				break;
			default:
				$str .= " DEFAULT " . $option["default"];
		}
		return $str;
	}
	/**
	 * Get entity name
	 * @return String
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * Get SQL Object
	 * @return Sql Object
	 */
	public function getSql(){
		return $this->sql;
	}
	/**
	 * Get default record
	 * @return indexed array
	 */
	public function getDefaultData($fields=null){
		if(!$fields){
			$fields = $this->getFields();
		}
		$data = null;
		foreach($fields as $field){
			$option = $this->fields[$field];
			if(!is_bool(strpos("int",$option['type'])) && $option['default'] == 'auto'){
				$data[] = 'null';
				
			}
			else if($option['type'] == 'timestamp' && $option['default'] == 'auto'){
				$data[] = time();
			}
			else{
				$data[] = "'" . $option['default'] . "'";
			}
		}
		return $data;
	}
	/**
	 * Check belongs to relationship
	 * @param String $entityName entity name
	 * @return boolean
	 */
	public function checkBelongsTo($entityName){
		if(!$this->belongsTo){
			return false;
		}
		foreach($this->belongsTo as $bt){
			if($bt['from'] == $entityName){
				return $bt;
			}
		}
		return false;
	}
	/**
	 * Check has many relationship
	 * @param String $entityName entity name
	 * @return boolean
	 */
	public function checkHasMany($entityName){
		if(!$this->hasMany){
			return false;
		}
		foreach($this->hasMany as $hm){
			if($hm['to'] == $entityName){
				return $hm;
			}
		}
		return false;
	}
	public function checkManyToMany($entityName){
		if(!$this->manyToMany){
			return false;
		}
		foreach($this->manyToMany as $mtm){
			if($mtm['to'] == $entityName){
				return $mtm;
			}
		}
		return false;
	}
	/**
	 * Delete table
	 * @return boolean
	 */
	public function uninstall(){
		$sql = "DROP TABLE " . $this->name;
		$mysql = Mysql::getInstance();
		return $mysql->query($sql);
	}
	/**
	 * Check if cache records of this entity
	 * @return boolean
	 */
	public function ifCache(){
		return $this->cache;
	}
}
?>
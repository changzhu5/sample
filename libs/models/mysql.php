<?php
class Mysql extends Object{
	private $insert_id;
	private $affected_rows;
 	private $table_info = array();
 	private $alias = array();
 	private static $conn=array();
 	private $link = null;
	protected $cache;
 	
	private function __construct($app){
		$hostname = Configure::read('hostname',$app);
		$user = Configure::read('user',$app);
		$pwd = Configure::read('pwd',$app);
		$dbname = Configure::read('dbname',$app);
		if(!$hostname){
			$hostname = Configure::read('hostname');
		}
		if(!$user){
			$user = Configure::read('user');
		}
		if(!$pwd){
			$pwd = Configure::read('pwd');
		}
		if(!$dbname){
			$dbname = Configure::read('dbname');
		}
		$charset = Configure::read('charset');

		$this->link = mysql_pconnect($hostname,$user,$pwd);
		if(!$this->link){
			$this->appError(mysql_error());
		}
		mysql_select_db($dbname,$this->link);
		mysql_query("set names utf8;",$this->link);
		$this->alias = array('low'=>'LOW_PRIORITY','high'=>'HIGH_PRIORITY','delay'=>'DELAYED','all'=>'ALL','no-repeat'=>'DISTINCT','no-cache'=>'SQL_NO_CACHE','cache'=>'SQL_CACHE');
		
	}
	public static function getInstance($app)
	{
		if(!isset(self::$conn[$app]))
		{
			self::$conn[$app] = new Mysql($app);
		}
		return self::$conn[$app];
	}
	
	public function select($sql,$type="assoc",$modifer=null,$priority=null)
	{
		$arr = array();
		$params = func_get_args();
		for($i=1;$i<func_num_args();$i++)
		{
			if($params[$i] != 'default'){
				if(isset($this->alias[$params[$i]])){
					$str = $this->alias[$params[$i]];
					$sql = addToStr($sql,'SELECT'," $str ");
				}
					
			}
		}
		$res = mysql_query($sql,$this->link);
		if(!$res)
			return false;
		if($type == "assoc"){
			while($row = mysql_fetch_assoc($res)){
				$arr[] = $row;
			}
		}
		else if($type == "row"){
			while($row = mysql_fetch_row($res)){
				$arr[] = $row;
			}
		}
		return $arr;
	}
	/**
	 * Insert data to the table,the data can also from .txt file or .xml file
	 * @param string $sql SQL command
	 * @param string $modifer options:LOW_PRIORITY,HIGH_PRIORITY,DELAYED
	 * @param string $filepath absolute client path
	 * @param string $type options:txt,xml
	 * @param array $params options the load operation will need
	 * @return boolean if operation is succeful return true
	 */
	public function insert($sql,$modifer,$filepath=null,$type=null,$params=array())
	{
		if(!is_null($filepath))
		{
			$sql = $this->loadInFile($filepath,$type,$params);
		}
		if($modifer != 'default')
		{
			$sql = addToStr($sql,'INSERT'," $modifer ");
		}
		if(mysql_query($sql,$this->link))
		{
			$this->insertId = mysql_insert_id(self::$conn);
			$this->affected_rows = mysql_affected_rows(self::$conn);
			return true;
		}
		return false;
	}
	public function update($sql,$priority)
	{
		if($priority != 'default')
		{
			$str = $this->alias[$priority];
			$sql = addToStr($sql,'UPDATE'," $str ");
		}
		return mysql_query($sql,$this->link);
	}
	public function delete($sql,$modifier,$priority,$tables=array())
	{
		if(!empty($tables) && count($tables)>1)
		{
			$str = '';
			$num = count($tables);
			$params = func_get_args();
			for($i=0;$i<$num;$i++)
			{
				if($i == $num-1)
				{
					$str .= $tables[$i];
					break;
				}
				$str .= $tables[$i].',';
			}
			$sql = addToStr($sql,'DELETE'," $str ");
			$str = "";
			for($i=1;$i<$num;$i++)
			{
				$str .= " INNER JOIN $tables[$i] ON $tables[$i].{$tables[0]}_id={$tables[0]}.id";
			}
			$sql = addToStr($sql,"FROM $tables[0]",$str);
			$sql = str_replace('WHERE id',"WHERE {$tables[0]}.id",$sql);
		}
		for($i=1;$i<func_num_args()-1;$i++)
		{
			if($params[$i] != 'default')
			{
				$str = $this->alias[$params[$i]];
				$sql = addToStr($sql,'DELETE'," $str ");	
			}
		}
		return mysql_query($sql,$this->link);
	}
	/**
	 * Gengerate SQL command for insert data from client file.
	 * @param string $filepath absolute client file path
	 * @param string $type file type otpions:.txt or .xml
	 * @param array $params available options are that:
	 * $params['priority'] = low_pripority or concurrent
	 * $params['duplicate'] = replace or ignore
	 * $params['table'] = tablename
	 * $params['fields'] = array('terminated'=>val1,'enclosed'=>val2,'escaped'=>val3)
	 * $params['lines'] = array('starting'=>val1,'terminated'=>val2)
	 * $params['ignore'] = line_number
	 * @return unknown
	 */
	private function loadInFile($filepath,$type,$params)
	{
		$command = 'LOAD ' . strtoupper($type);
		if(isset($params['priority']))
		{
			$command .= ' ' . strtoupper($params['priority']);
		}
		$command .=" LOCAL INFILE $filepath";
		if(isset($params['duplicate']))
		{ 
			$command .= ' ' . strtoupper($params['duplicate']);
		}
		$command .= " INTO TABLE {$params['table']}";
		if(isset($params['fields']))
		{
			$command .= " FIELDS ";
			foreach($params['fields'] as $key=>$value)
			{
				$command.= strtoupper($key) . " BY $value "; 
			}
		}
		$command .= " FIELDS TERMINATED BY '\t' ENCLOSED BY '' ESCAPED BY '\\' ";
		if(isset($params['lines']))
		{
			$command .="LINES ";
			foreach($params['lines'] as $key=>$value)
			{
				$command.= strtoupper($key) . " BY $value "; 
			}
		}
		$command .= "LINES TERMINATED BY '\n' STARTING BY ''";
		if(isset($params['ignore']))
		{
			$command .= " IGNORE {$params['ignore']} LINES";
		}
		return $command;
	}
	public function query($sql)
	{
		$result = mysql_query($sql,$this->link);
		if(!is_bool($result)){
			$arr = array();
			if($result){
				while($row = mysql_fetch_assoc($result))
					$arr[] = $row;
				return $arr;
			}
			else return false;
		}
		return $result;
	}
	/**
	 * Get table infomation about fields and type
	 * @param string $tablename
	 * @return refer to $table_info property
	 * @access public
	 */
	public function getTableInfo($tablename)
	{
		$info = $this->cache->getVar($tablename,'table_info');
		if(!is_null($info))
			return $info;
		$sql = "DESCRIBE $tablename";
		$res = mysql_query($sql,$this->link);
		if($res)
		{
			while($row = mysql_fetch_assoc($res))
			{
				$field = array();
				if($row['Key'] == 'PRI')
				{
					$info['primary_key'][] = $row['Field'];
				}
				if($row['Key'] == 'MUL')
				{
					$info['foreign_key'] = $row['Field'];
				}
				if(preg_match("/([a-zA-Z]+)\(([0-9]{2,3})\)/",$row['Type'],$arr))
				{
					$field['type'] = $arr[1];
					$field['length'] = $arr[2];
				}
				else 
				{
					$field['type'] = $row['Type'];
					$field['length'] = 0;
				}
				$info['fields'][$row['Field']] = $field;
			}
		}
		$this->cache->setVar($tablename,$info,'table_info');
		return $info;
	}
	public function getLastInsertId(){
		return $this->insertId;
	}
	/**
	 * Start a transaction
	 */
	public function start(){
		mysql_query('SET autocommit = 0;',$this->link);
    	mysql_query('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE;',$this->link);
    	mysql_query('START TRANSACTION;',$this->link);
	}
	/**
	 * Rollback a transaction
	 */
	public function rollback(){
		mysql_query('ROLLBACK;',$this->link);
	}
	/**
	 * Commit a transaction
	 */
	public function commit(){
		mysql_query('COMMIT;',$this->link);
	}
}
?>
<?php
class Sql extends Object{
	protected function escape($str){
		return "'" . mysql_escape_string($str) . "'";
	}
	/**
	 * Get select sql command
	 * @param Assoc Array $options
	 * @return String
	 */
	public function getSelect($options){
		$str = 'SELECT';
		if(isset($options['hints']) && $options['hints'])
			$str .= ' ' . $options['hints'];
		if(!$options['fields'])
			$fields = '*';
		else 
			$fields = implode(',',$options['fields']);
		$str .= ' ' . $fields;
		$str .= ' FROM ' . $options['table'];
		if(isset($options['conditions']) && $options['conditions'])
			$str .= ' WHERE ' . $options['conditions'];
		if(isset($options['order']) && $options['order'])
			$str .= ' ORDER BY ' . $options['order'];
		if(isset($options['limit']) && $options['limit'])
			$str .= ' LIMIT ' . $options['limit'];
		if(isset($options['group']) && $options['group'])
			$str .= ' GROUP BY ' . $options['group'];
		return $str.';';
	}
	/**
	 * Get where condition command
	 * @param Indexed Array $option
	 * @return String
	 */
	public function where($option){
		if(is_string($option)){
			return $option;
		}
		$operator = $option[1];
		switch($operator){
			case 'eq':
				$where = $option[0] . "=" . $this->escape($option[2]);
				break;
			case 'gt':
				$where = $option[0] . ">" . (int)$option[2];
				break;
			case 'gtOrEq':
				$where = $option[0] . ">=" . (int)$option[2];
				break;
			case 'lt' :
				$where = $option[0] . "<" . (int)$option[2];
				break;
			case 'ltOrEq':
				$where = $option[0] . "<=" . (int)$option[2];
				break;
			case 'in':
				$arr = null;
				foreach($option[2] as $o){
					$arr[] = "'" . $o . "'";
				}
				$where = $option[0] . " IN(" . implode(",",$arr) . ")";
				break;
			case 'like':
				$where = $option[0] . " LIKE '%" . $option[2] . "%'";
				break;
			case 'isNull':
				$where = $option[0] . " IS NULL";
				break;
			case 'isNotNull':
				$where = $option[0] . " IS NOT NULL";
				break;
			case 'between':
				$where = $option[0] . " BETWEEN " . (int)$option[2] . " AND " . (int)$option[3];
				break;
			case 'notBetween':
				$where =  $option[0] . " NOT BETWEEN " . (int)$option[2] . " AND " . (int)$option[3];
				break;
			case 'notIn':
				$arr = null;
				foreach($option[2] as $o){
					$arr[] = $this->escape($o);
				}
				$where = $option[0] . " NOT IN(" . implode(",",$arr) . ")"; 
				break;
		}
		return $where;
	}
	public function whereOr($options){
		$sql = "";
		foreach($options as $option){
			$sql .= $this->where($option) . " OR ";
		}
		return rtrim($sql," OR ");
	}
	public function whereAnd($options){
		$sql = "";
		foreach($options as $option){
			$sql .= $this->where($option) . " AND ";
		}
		return rtrim($sql," AND ");
	}
	/**
	 * Get order by SQL
	 * @param Indexed Array $option
	 * @return String
	 */
	public function orderBy($option){
		$str = "";
		$len = count($option);
		for($i=0,$j=$i*2;$j<$len;++$i,$j=$i*2){
			$str .= $option[$j] . " " . $option[$j+1] . ",";
		}
		$str = rtrim($str,',');
		return $str;
	}
	/**
	 * Get limit SQL
	 * @param Indexed Array $option
	 * @return String
	 */
	public function limit($option){
		$len = count($option);
		if($len == 1){
			return (int)$option[0];
		}
		else{
			return (int)$option[0] . "," . (int)$option[1];
		}
	}
	/**
	 * Get INSERT SQL
	 * @param Assoc Array $options
	 * @return String
	 */
	public function getInsert($options){
		foreach($options['values'] as &$value){
			$value = $this->escape($value);
		}
		$str = 'INSERT';
		if(isset($options['hints']))
			 $str .= ' ' . $options['hints'];
		$str .= ' INTO ' . $options['table'];
		if(isset($options['fields']))
			$str .= '(' . implode(',',$options['fields']) . ')';
		$str .= ' ' . 'VALUES(' . implode(',',$options['values']) . ');';
		return $str;
	}
	/**
	 * Get DELETE SQL
	 * @param Assoc Array $options
	 * @return String
	 */
	public function getDelete($options){
		$str = 'DELETE';
		if(isset($options['hints']))
			$str .= ' ' . $options['hints'];
		$str .= ' FROM';
		$str .= ' ' . $options['table'];
		if(isset($options['conditions']))
			$str .= ' WHERE ' . $options['conditions'];
		return $str . ';';
	}
	/**
	 * Get UPDATE SQL
	 * @param Assoc Array $options
	 * @return String
	 */
	public function getUpdate($options){
		$str = 'UPDATE';
		if(isset($options['hints']))
			$str .= ' ' . $options['hints'];
		$str .= ' ' . $options['table'] . ' SET ';
		foreach($options['set'] as $k=>$v){
			$str .= "$k=" . $this->escape($v).",";
		}
		$str = substr($str,0,strlen($str)-1);
		$str .= ' WHERE ' . $options['conditions'] . ';';
		return $str;
	}
}
?>
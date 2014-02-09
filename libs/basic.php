<?php
/**
 * Equal to ucfirst()
 * @param String $name
 * @return String
 */
function name_to_class($name){
	$first_chr = substr($name,0,1);
	$rest_chr = substr($name,1);
	return strtoupper($first_chr).$rest_chr;
}
/**
 * Made SQL command fields part
 * @param Array $fields eg. array('model1.field1','model1.field2','model2.field1');
 * @return String eg. model1.field1,model1.field2,model2.field from 
 */
function sql_fields(Array $fields){
	$str = '';
	for($i=0,$j=count($fields);$i<$j;$i++){
		if($i == $j-1){
			$str .=$fields[$i].' ';
			break;
		}
		$str .=$fields[$i].',';
	}
	return $str;
}

/**
 * Delete value from array
 * @param Array $arr
 * @param Mix $key index or key
 * @return Array
 */
function array_delete(Array $arr,$key){
	$temp = array();
	if(!is_array($arr)){
		return false;
	}
	foreach($arr as $k=>$v){
		if($k!=$key){
			$temp[$k] = $arr[$k];
		}
	}
	return $temp;
}
/**
 * Convert template file path to html file path
 * @param String $path eg./app/view/controller/action.html
 * @return String controller_action.html
 */
function template_to_html_path($path){
	$arr = explode(DS,$path);
	$len = count($arr);
	$controller = $arr[$len-2];
	$action = $arr[$len-1];
	$html_path = $controller . '_' . $action;
	return $html_path;
}
/**
 * Add a string1 after a string2 which is contained in string3 
 * @param String $str3
 * @param String $str2
 * @param String $str1
 * @return String
 */
function addToStr($str3,$str2,$str1){
	$str3 = trim($str3);
	$str2_pos = strpos($str3,$str2);
	$str_flg = $str2_pos + strlen($str2);
	$str_left = substr($str3,0,$str2_pos);
	$str_right = substr($str3,$str_flg);
	return $str_left . $str2 . $str1 . $str_right;
}
/**
 * Copy array to array
 * @param Assoc Array $old
 * @param Assoc Array $new
 * @return Assoc Array
 */
function copyArray(&$old,$new){
	if(is_array($new)){
		foreach($new as $key=>$value){
			$old[$key] = $value;
		} 
	}
}
/**
 * remove alpha and space in a string
 * @param String $str
 * @return String
 */
function removeAlphaAndSpace($str){
	$str = preg_replace('/[^\x{4E00}-\x{9FA5}]/u','',$str);
	return $str;
}
/**
 * Get the key of an array
 * @param Mix $value
 * @param Array $arr
 * @return String
 */
function getKeyInArray($value,$arr){
	foreach($arr as $key=>$v){
		if($v == $value)
			return $key;
	}
}
/**
 * Convert associative array to string
 * @param Assoc Array $assoc_array
 * @return String
 */
function array_to_string($assoc_array){
	$str = '';
	foreach($assoc_array as $key=>$value){
		$str .= "$key=$value;";
	}
	return $str;
}
/**
 * Convert string to assoc array
 * @param String $assoc_str
 * @return Assoc Array
 */
function string_to_array($assoc_str){
	$arr = array();
	$out = explode(';',$assoc_str);
	$out_len = count($out);
	for($i=0;$i<$out_len-1;$i++){
		$in = explode('=',$out[$i]);
		$arr[$in[0]] = $in[1];
	}
	return $arr;
}
/**
 * Delete value from array
 * @param Mix $value
 * @param array $arr
 * @return array
 */
function array_delete_value($value,$arr){
	$new_arr = array();
	foreach($arr as $v){
		if($v != $value){
			$new_arr[] = $v;
		}
	}
	return $new_arr;
}
/**
 * How much time passed until now
 * @param Int $time past time
 * @param Int $now current time
 * @return Array
 */
function timePass($time,$now=null){
	$str = '';
	if(is_null($now))
		$now = time();
	$pass_seconds = $now - $time;
	$arr = mod($pass_seconds,60);
	$second = $arr[1];
	$minute = $arr[0];
	if($minute > 60){
		$arr = mod($minute,60);
		$minute = $arr[1];
		$hour = $arr[0];
		if($hour > 24){
			$arr = mod($hour,24);
			$hour = $arr[1];
			$day = $arr[0];
		}
	}
	$res = null;
	if(isset($day)){
		$res['day'] = $day;
	}
	if(isset($hour)){
		$res['hour'] = $hour;
	}
	if(isset($minute)){
		$res['minute'] = $minute;
	}
	return $res;
}
/**
 * Get tomorrow timestamp
 * @return Int
 */
function getTomorrow(){
	$t = mktime(0,0,0,date('n'),date('j')+1,date('Y'));
	return $t;
}
/**
 * Check if current timestamp is today
 * @param Int $itme
 * @return Boolean
 */
function checkToday($time){
	if(date('Ymd') == date('Ymd',$time)){
		return true;
	}
	return false;
}
/**
 * Get days in a month
 * @param Int $month
 * @param Int $year
 * @return Int
 */
function getDaysInMonth($month,$year){
	$num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
	return $num;
}
/**
 * Get week of a day
 * @param Int $day
 * @param Int $month
 * @param Int $year
 * @return Int
 */
function getDay($day,$month,$year){
	$time = mktime(0,0,0,$month,$day,$year);
	return date('w',$time);
}
/**
 * Check if array is associve
 * @param Array $arr
 * @return Boolean
 */
function isAssoc($arr){
    return array_keys($arr) !== range(0, count($arr) - 1);
}
/**
 * Get current requested URI
 */
function getCurrentUrl(){
	$url = '';
	$url .= $_SERVER['SCRIPT_URI'];
	if($_SERVER['argv']){
		$url .= '?'.$_SERVER['argv'][0];
	}
	return $url; 
}
/**
 * Get permalink
 * @param string $url eg. app/controller/action
 * @return string
 */
function getPermalink($url){
	return siteUrl() . '/' . $url;
}
/**
 * Get site url
 * @return string
 */
function siteUrl(){
	$docRoot =  $_SERVER['DOCUMENT_ROOT'];
  	$root = str_replace("\\", "/", ROOT);
  	$sub = substr($root,strlen($docRoot));
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
  	$url = $protocol . $_SERVER['HTTP_HOST'] . $sub;
	return $url;
}
/**
 * Convert Std Object to Array
 */
function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
			$d = get_object_vars($d);
			
	}
 
		if (is_array($d)) {
			/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}
?>
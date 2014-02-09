<?php 
class Time extends Object{
	public function format($day,$month,$year,$time_block){
		$arr = explode('-',$time_block);
		$start = $this->getTimeByMinute($arr[0]);
		$end = $this->getTimeByMinute($arr[1]);
		return $start . '-' . $end . ' ' . $month . '/' . $day . '/' . $year;
	}
	private function getTimeByMinute($t){
		$mod = $t % 60;
		$i = $t/60;
		$flag = 0;
		if($i > 12){
			$i -= 12;
			$flag = 1;
		}
		if($mod < 10){
			$str = $i + ':' + '0' + $mod;
		}
		else{
			$str = $i + ':' + $mod;
		}
		if($flag){
			return $str .= ' pm';
		}
		else{
			return $str .= ' am';
		}
	}
}
?>
<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class date_msg {
	//返回时间信息	
	static function get() {
		$timenow = time();
		$str='';
		$morentime1 = strtotime('1:00');		
		$morentime2 = strtotime('6:00');
		$morentime3 = strtotime('12:00');
		$morentime4 = strtotime('13:00');
		$morentime5 = strtotime('18:00');
		$morentime6 = strtotime('23:00');
		if ($timenow < $morentime1) {
			$str = '凌晨了，请注意休息！';
		} elseif ($timenow < $morentime2) {
			$str = '早上好！';
		} elseif ($timenow < $morentime3) {
			$str = '上午好！';
		} elseif ($timenow < $morentime4) {
			$str = '中午好！';
		} elseif ($timenow < $morentime5) {
			$str = '下午好！';
		} elseif ($timenow < $morentime6) {
			$str = '晚上好！';
		} else{
			$str = '深夜了，请注意休息！';
		}
		return $str;
	}
}
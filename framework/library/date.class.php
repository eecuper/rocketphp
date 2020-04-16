<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
define('CLS_DATE_FILR_DIR',str_replace('\\','/',dirname(__FILE__)).'/Date');
class date {
	static function format_time($time = 0,$type = 1,$font = '-'){
		if(empty($time)) return '';
		if(!preg_match('/^\d+$/',$time)) return $time;
		$time = intval($time);
		if($type == 1){
			return date('Y'.$font.'m'.$font.'d H:i:s',$time);	
		}
		if($type == 2){
			return date('Y'.$font.'m'.$font.'d',$time);	
		}
		if($type == 3){
			return date('H:i:s',$time);	
		}
	}
	//返回时间信息	
	static function get_timemessage(){
		require_cache(CLS_DATE_FILR_DIR.'/msg.php');
		return date_msg::get();
	}
	//计算日期间隔
	public static function date_diff($time, $elaps = "d") {
		require_cache(CLS_DATE_FILR_DIR.'/diff.php');
		return date_diff::date($time,$elaps);
    }
	//计算时间间隔
	public static function time_diff($time ,$start = 1,$offset = 5,$now = 0) {
		require_cache(CLS_DATE_FILR_DIR.'/diff.php');
		return date_diff::time($time ,$start,$offset,$now);
    }
	public static function week($time = ''){
		$week	=	array("日","一","二","三","四","五","六");
		return $week[date('w',$time?$time:time())];
	}
}
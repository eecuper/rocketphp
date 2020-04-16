<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
define('CLS_STRING_FILR_DIR',str_replace('\\','/',dirname(__FILE__)).'/String');
class string {
	//字符串截取
	static function cut_str($str, $length , $dot = '...', $start = 0, $charset="utf-8" ){
		require_cache(CLS_STRING_FILR_DIR.'/cut.php');
		return string_cut::str($str, $length , $dot, $start, $charset);
	}
	//切割html
	static function cut_html($sourcestr, $cutlength = 20, $lenstr = '', $allow = '') {
		require_cache(CLS_STRING_FILR_DIR.'/cut.php');
		return string_cut::html($sourcestr, $cutlength, $lenstr, $allow);
	}
	//输出json字符串,支持安卓,ios的全字符串返回,result结果,info信息,data数据
	static function ajax_out($result = 1,$info = '',$data = ''){
		require_cache(CLS_STRING_FILR_DIR.'/ajax.php');
		string_ajax::out($result,$info,$data);
	}
	static function get_json($array,$cn_encode = true){
		require_cache(CLS_STRING_FILR_DIR.'/ajax.php');
		$array = string_ajax::str_val($array);
		if(!$cn_encode){
			if(defined('JSON_UNESCAPED_UNICODE')){
				return json_encode($array, JSON_UNESCAPED_UNICODE);
			}else{
				$str = json_encode($array);
				$str = preg_replace_callback("#\\\u([0-9a-f]{4})#i", function($matchs){
					return mb_convert_encoding(pack('H*', $matchs[1]), 'UTF-8', 'UTF-16');
				}, $str);
				return $str;
			}
		}else{
			return json_encode($array);
		}
	}
	//字符串抽取,小包含
	static function my_substr($str='',$start = '',$end = ''){
		require_cache(CLS_STRING_FILR_DIR.'/substr.php');
		return string_substr::my_substr($str,$start,$end);
	}
	//字符串抽取,大包含
	static function my_subrstr($str,$start = '',$end = ''){
		require_cache(CLS_STRING_FILR_DIR.'/substr.php');
		return string_substr::my_subrstr($str,$start,$end);
	}
	//获得随机字符串
	static function random($length, $numeric = 0) {
		require_cache(CLS_STRING_FILR_DIR.'/password.php');
		return string_password::random($length, $numeric);
	}
	static function password($pwd){
		require_cache(CLS_STRING_FILR_DIR.'/password.php');
		return string_password::password($pwd);
	}
	static function money_to_upper($num){
		require_cache(CLS_STRING_FILR_DIR.'/money.php');
		return string_money::make($num);
	}
	/*
	长度最好小于8个
	$a = alpha_id($_GET['k'],false,6,'ccddd');
	echo $a.'<br>';
	$b = alpha_id($a,true,6,'ccddd');
	echo $b.'<br>';
	*/
	static function alphaid_encode($in,$key = null, $pad_up = 8){
		require_cache(CLS_STRING_FILR_DIR.'/alpha_id.php');
		return string_alpha_id::make($in, false, $pad_up, $key);
	}
	static function alphaid_decode($in,$key = null,$pad_up = 8){
		require_cache(CLS_STRING_FILR_DIR.'/alpha_id.php');
		return string_alpha_id::make($in, true, $pad_up, $key);
	}
}
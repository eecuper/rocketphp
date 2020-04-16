<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class validate{
	//长度大于
	public static function len_gt($str,$val){
		$str = trim($str);
		if (function_exists('mb_strlen')){
			return (mb_strlen($str,'utf-8') > $val);
		}
		return strlen($str) > $val; 
	}
	//长度小于
	public static function len_lt($str, $val){
		$str = trim($str);
		if (function_exists('mb_strlen')){
			return mb_strlen($str,'utf-8') < $val;
		}
		return strlen($str) < $val;
	}
	//长度在指定之间
	public static function len_between($value,$min,$max){
		return self::len_gt($value,$min-1) && self::len_lt($value,$max+1);
	}
	//纯字母
	public static function is_letter($str){
		return  preg_match("/^([a-z])+$/i", $str);
	}
	//数字和字母
	public static function is_letter_num($str)	{
		return  preg_match("/^([a-z0-9])+$/i", $str);
	}
	//字母数字下划线
	public static function is_letter_num_line($str){
		return preg_match("/^([a-z0-9_])+$/i", $str);
	}
	//正整数
	public static function is_pint($str){
		return preg_match("/^\d+$/",$str) && intval($str) > 0;
	}
	//只有汉字
	public static function is_chinese($value){
		return preg_match("/^[\x7f-\xff]+$/",$value);
	}
	//是否为email
	public static function is_email($str){
		return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str);
	}
	//电话
	public static function is_phone($value){
		return preg_match("/^0[0-9]{2,3}-[0-9]{7,8}(-[0-9]{1,6}){0,1}$/",$value);
	}
	//手机
	public static function is_mobile($value){
		return preg_match("/^1[34578][0-9]{9}$/",$value);
	}
	//是否为url地址
	public static function is_url($value){
		return preg_match("/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i",$value);
	}
	//QQ
	public static function is_qq($value){
		return preg_match("/^\d+\d{4,10}$/",$value);
	}
	//值不能含有电话号码
	public static function has_phone($value){
		return !preg_match("/^.*0[0-9]{2,3}-[0-9]{7,8}(-[0-9]{1,6}){0,1}.*$/",$value)
		&& !preg_match("/^.*1[3458][0-9]{9}.*$/",$value)
		&& !preg_match("/^.*[48]00-[0-9]{3}-[0-9]{4}.*$/",$value);
	}
	//检查ip地址
	public static function is_ip($ip, $which = ''){
		include_once str_replace('\\','/',dirname(__FILE__)).'/Validate/ip.php';
		return validate_ip::is_ip($ip,$which);
	}	
	//身份证检查
	public static function is_idcard($value){
		include_once str_replace('\\','/',dirname(__FILE__)).'/Validate/idcard.php';
		return validate_idcard::is_idcard($value);
	}
}
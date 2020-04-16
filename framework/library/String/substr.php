<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class string_substr {
	//字符串抽取,小包含
	static function my_substr($str='',$start = '',$end = ''){
		if($start == ''){
			return substr($str,0,strpos($str,$end));
		}elseif(strpos($str,$start) !== false){
			$str = substr($str,strpos($str,$start)+strlen($start));
			if($end !=='' && strpos($str,$end)!== false){
				return substr($str,0,strpos($str,$end));
			}else{
				return $str;
			}
		}
		return '';
	}
	//字符串抽取,大包含
	static function my_subrstr($str,$start = '',$end = ''){
		if($start == ''){
			return substr($str,0,strrpos($str,$end));
		}elseif(strpos($str,$start)!== false){
			$str = substr($str,strpos($str,$start)+strlen($start));
			if($end !=='' && strpos($str,$end) !== false){
				return substr($str,0,strrpos($str,$end));
			}else{
				return $str;
			}
		}
		return '';
	}
}
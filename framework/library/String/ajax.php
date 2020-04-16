<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class string_ajax {
	//输出json字符串,支持安卓,ios的全字符串返回,result结果,info信息,data数据
	static function out($result = 1,$info = '',$data = ''){
		$return = array(
			'result'=>$result,
			'info'=>$info,
			'data'=>$data,
		);
		exit(json_encode(self::str_val($return)));
	}
	//强行转换字符串
	static function str_val($string){
		if(!is_array($string)){
			return (string)$string;
		}
		$return = array();
		foreach($string as $key => $val){
			$return[$key] = self::str_val($val);
		}
		return $return;
	}
}
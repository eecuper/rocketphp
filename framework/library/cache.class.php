<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class cache{
	public static function get_instance($type,$option = array()){
		static $instances = array();
		$key = md5($type.serialize($option));
		if(!isset($instances[$key])){
			$class = 'cache_'.$type;
			require_cache(SYS_ROOT.'library/Cache/'.$class.'.php');
			$instances[$key] = new $class($option);
		}
		return $instances[$key];
	}
	public static function put_data($filename,$contents,$extends = 0){
		return file_put_contents(APP_PATH.'/caches/data/'.$filename,$contents,$extends);
	}
	public static function get_data($filename){
		$filename = APP_PATH.'/caches/data/'.$filename;
		if(is_file($filename)){
			return file_get_contents($filename);
		}
		return false;
	}
	public static function remove_data($filename){
		$filename = APP_PATH.'/caches/data/'.$filename;
		if(is_file($filename)){
			unlink($filename);
		}
	}
}
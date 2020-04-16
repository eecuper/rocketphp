<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class lang{
	static function get($config_name){
		static $langs = array();
		$items = strpos($config_name,'://') !== false?explode('.',substr(strrchr($config_name,'://'),3)):explode('.',$config_name);
		$app   = strpos($config_name,'://') !== false?substr($config_name,0,strpos($config_name,'://')):APPLICATION;
		$file  = CMS_ROOT.$app.'/language/'.DEFAULT_LANG.'/lang_'.$items[0].'.php';
		if(!isset($langs[$file])){
			if(is_file($file)){
				$langs[$file] = include $file;
			}else{
				 fatal_error($file.' does not exist!');
			}
		}
		array_shift($items);
		$lang = $langs[$file];
		foreach($items as $v){
			if(!empty($lang[$v])){
				$lang = $lang[$v];
			}else{
				return '';	
			}
		}
		return $lang;
	}
}
<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
/*app://dir/file.key1.key2*/
class config{
	static function parse_config_name($config_name,$common = false){
		$option 		= array();
		$preg = '#^(([a-z0-9_]+:\/\/)?|([a-z0-9_]+\/?))(([a-z0-9_]+\/?)|([a-z0-9_]+\.?))*[a-z0-9_]+$#i';
		if(!preg_match($preg,$config_name)){
			error('Config name ['.$config_name.'] error! Example: [app://dir/file.key1.key2]');
			return '';
		}
		$items = strpos($config_name,'://') !== false?explode('.',substr(strrchr($config_name,'://'),3)):explode('.',$config_name);
		$option['app']  = strpos($config_name,'://') !== false ?substr($config_name,0,strpos($config_name,'://')):APPLICATION;
		$option['name'] = $items[0];
		if($common){
			$dir = CMS_ROOT.'common/config/';
		}else{
			$dir = CMS_ROOT.$option['app'].'/config/';
		}
		$option['path'] = $dir.$items[0].'.php';
		$option['file'] = $items[0].'.php';
		array_shift($items);
		$option['keys'] = $items;
		return $option;
	}
	static function set($name, $config, $options = array(),$common = false){
		$options 		= !empty($options) ? $options : self::parse_config_name($name,$common);
		if(empty($options)) return false;
		if(!is_file($options['path'])){
			$config_string = "<?php \ndefined('ISROCKET') or exit('Access denied!'); \nreturn array();\n";
			file_put_contents($options['path'],$config_string);
		}
		if($options['keys']){
			$configs  = include $options['path'];
			$cmd = "\$configs";
			foreach($options['keys'] as $v){
				$cmd .=	'[\''.$v.'\']';
			}
			$cmd .="=\$config;";
			eval($cmd);
		}else{
			$configs = (array)$config;
		}
		$config_string 	= "<?php \ndefined('ISROCKET') or exit('Access denied!'); \nreturn " . 
		var_export($configs, true). ";\n";
		return file_put_contents($options['path'],$config_string, LOCK_EX);
	}
	static function get($name, $options = array(),$common = false){
		static $_config = array();
		$options = !empty($options) ? $options: self::parse_config_name($name,$common);
		if(empty($options)) return '';
		$options['name'] = substr(md5($options['path']),8,16);
		if(!isset($_config[$options['name']])){
			if(is_file($options['path'])){
				$_config[$options['name']] = include $options['path'];
			}elseif(!$common){
				$file = CMS_ROOT.'common/config/'.$options['file'] ;
				if(is_file($file)){
					$_config[$options['name']] = include $file;
				}
			}
		}
		if(!isset($_config[$options['name']]))
			return '';
			
		if($options['keys']){
			$config = $_config[$options['name']];
			foreach($options['keys'] as $v){
				if(isset($config[$v])){
					$config = $config[$v];
				}else{
					return '';
				}
			}
			return $config;
		}else{
			return $_config[$options['name']];
		}
	}
	static function del($name, $options = array()){
		$options = !empty($options) ? $options: self::parse_config_name($name);
		if(empty($options)) return false;
		if($options['keys']){
			$config = include $options['path'];
			$cmd 	= "unset(\$config";
			foreach($options['keys'] as $v){
				$cmd .=	'[\''.$v.'\']';
			}
			$cmd .=');';
			eval($cmd);
			$options['keys'] = '';
			self::set('',$config,$options);
			return true;
		}else{
			if(is_file($options['path']))unlink($options['path']);
			return true;
		}
	}
	static function set_common($name, $config){
		$dir = CMS_ROOT.'common/config/';
		if(!is_dir($dir)){
			mkdir($dir,0777,true);
		}
		return self::set($name,$config,array(),true);
	}
	static function get_common($name){
		return self::get($name,array(),true);
	}
}
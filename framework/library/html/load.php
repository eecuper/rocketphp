<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_load{
	static function get_dir($t = 0){
		$dir = dirname(str_replace(CMS_ROOT,'',$_SERVER['SCRIPT_FILENAME']));
		return $dir === '.' ?( $t ? CMS_ROOT : APP_PATH.'/'):(CMS_ROOT.$dir.'/');
	}
	static function is_root(){
		return dirname(str_replace(CMS_ROOT,'',str_replace('\\','/',$_SERVER['SCRIPT_FILENAME']))) === '.';
	}
	static function sys_js($file,$vsn = ''){
		$str = '';static $loaded = array();
		$vsn = '?v='.(empty($vsn)?'1.0':$vsn);
		if($j = explode(',',$file))
			foreach($j as $v){
				$v = trim($v);
				if($v && !isset($loaded[$v])){
					$str .= '<script type="text/javascript" src="'.SYS_JS_DIR.'/'.$v.'.js'.$vsn.'"></script>';
					$loaded[$v] = 1;
				}
			}
		return $str;
	}
	static function js($file,$vsn = ''){
		$str = '';static $loaded = array();
		$vsn = '?v='.(empty($vsn)?'1.0':$vsn);
		if($j = explode(',',$file))
			foreach($j as $v){
				$v = trim($v);
				$jf = 'static'.($v[0]=='/'?$v:'/js/'.$v).'.js';
				if($v && !isset($loaded[$v])){
					$str .= '<script type="text/javascript" src="'.(self::is_root()?APPLICATION.'/':'').$jf.$vsn.'"></script>';	
					$loaded[$v] = 1;
				}
			}
		return $str;
	}
	static function css($file,$vsn = ''){
		$str = '';static $loaded = array();
		$vsn = '?v='.(empty($vsn)?'1.0':$vsn);
		if($c = explode(',',$file))
			foreach($c as $v){
				$v = trim($v);
				$cf = 'static'.($v[0]=='/'?$v:'/css/'.$v).'.css';
				if($v && !isset($loaded[$v])){
					$str .= '<link href="'.(self::is_root()?APPLICATION.'/':'').$cf.$vsn.'" rel="stylesheet" type="text/css">';
					$loaded[$v] = 1;
				}
			}
		return $str;
	}
}
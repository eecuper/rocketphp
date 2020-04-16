<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_img{
	static function make($src){
		return empty($src)?'':'<img src="'.self::src($src).'" />';
	}
	static function src($src){
		$src = str_replace('\\','/',$src);
		if(preg_match("#^https?://\w+#",$src)){
			$host = '';
		}else if(preg_match("#\w+/upload/\d{4}/\d{2}/\d{2}/\d+\.#",$src)){
			$host = defined('APP_HOST')?APP_HOST:self::get_host();
		}else{
			$host = defined('IMG_HOST')?IMG_HOST:self::get_host();
		}
		return empty($src)?'':$host.$src;
	}
	static function get_host(){
		$host  = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://');
		$host .=  $_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] :'');
		return  $host.rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])),'/').'/';
	}
}
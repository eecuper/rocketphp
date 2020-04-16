<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
define('HTTP_FILE_DIR',str_replace('\\','/',dirname(__FILE__)).'/Http');
class http {
	static function curl_post($url = '',$data = array(), $headers = array(),$timeout = 5){
		include_once HTTP_FILE_DIR.'/post.php';
		return http_post::curl_post($url,$data,$headers,$timeout);
	}
	static function curl_get($url,$headers = array(),$timeout = 5){
		include_once HTTP_FILE_DIR.'/get.php';
		return http_get::curl_get($url,$headers,$timeout);
	}
	static function get_head($url,$type = 0){
		include_once HTTP_FILE_DIR.'/head.php';
		return http_head::get($url,$type);
	}
	static function get_host(){
		include_once HTTP_FILE_DIR.'/host.php';
		return http_host::get();
	}
	static function get_city($ip){
		include_once HTTP_FILE_DIR.'/host.php';
		return http_host::get_city($ip);
	}
	static function auth_check($autharr){
		include_once HTTP_FILE_DIR.'/auth.php';
		return http_auth::check($autharr);
	}
	static function send_code($code){
		include_once HTTP_FILE_DIR.'/code.php';
		return http_code::send($code);
	}
	static function curl_error_log($err){
		if(function_exists('error')){
			error($err);
		}else{
			$errmsg = date('Y-m-d h:i:s').' > '.$err."\r\n";
			file_put_contents('curl_error.log',$errmsg,FILE_APPEND);
		}
	}
}
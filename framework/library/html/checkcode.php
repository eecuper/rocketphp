<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_checkcode{
	static function make(){
		$url = !empty($_GET['r']) ? 'r=api/checkcode' 	: 'm=api&a=checkcode';
		return '<img id="check_code" src="?'.$url.'" alt="验证码" style="cursor:pointer;height:30px;vertical-align: middle;"'
		.' title="换一张" onclick="this.src=\'?'.$url.'&t=\'+Math.random()">';
	}
	static function check($code = ''){
		session::get_instance();
		if($code && isset($_SESSION['__code__']) && strtolower($code) == strtolower($_SESSION['__code__'])){
			unset($_SESSION['__code__']);		
			return TRUE;
		}else{
			unset($_SESSION['__code__']);		
			return FALSE;
		}
	}
}
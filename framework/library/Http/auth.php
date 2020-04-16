<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class http_auth{
	static function check($autharr){
		if(empty($_SERVER['PHP_AUTH_DIGEST'])) {
			self::unauthed();
		}
		$needed_parts = array('realm'=>1,'nonce'=>1,'nc'=> 1,'cnonce'=> 1,'qop' =>1,'username' =>1,'uri'=>1,'response'=>1);
		$data = array();
		$keys = implode('|', array_keys($needed_parts));
		preg_match_all('/('.$keys.')=(?:([\'"])([^\2]+?)\2|([^\s,]+))/', $_SERVER['PHP_AUTH_DIGEST'], $matches, PREG_SET_ORDER);
		foreach ($matches as $m){
		  $data[$m[1]] = $m[3] ? $m[3] : $m[4];
		  unset($needed_parts[$m[1]]);
		}
		if(!isset($autharr[$data['username']])){
			self::unauthed();
		}
		$A1 = md5($data['username'].':'.'user'.':'.$autharr[$data['username']]);
		$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
		$response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
		if($response != $data['response']){
			self::unauthed();
		}
		$_SERVER['AUTH_USER'] = $data['username'];
	}
	static function unauthed(){
		$realm 	= 'user';
		$opaque = md5($realm.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.$opaque.'"');
		exit('Auth failed!');
	}
}
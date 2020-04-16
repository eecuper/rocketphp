<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class cookie{
	static function act($key = '', $val = '',$unencode = false,$expire = 2592000,$httponly = false){
		if($val === null){
			unset($_COOKIE[$key]);
			setcookie($key, NULL, -86400, '/');
		}elseif($key !== '' && $val === ''){
			if(isset($_COOKIE[$key])){
				if($unencode){
					return $_COOKIE[$key];
				}
				if(sys_auth($_COOKIE[$key], 'DECODE', AUTH_KEY,$expire)){
					return unserialize(sys_auth($_COOKIE[$key], 'DECODE', AUTH_KEY,$expire));
				}else{
					$c = preg_replace('/(create|alter|drop|truncate|select|insert|replace|update|delete)/i','',$_COOKIE[$key]);
					return (MAGIC_QUOTES_GPC ? $c : addslashes($c));	
				}				
			}else{
				return false;
			}
		}elseif($key !== '' && $val !== ''){
			if($unencode){
				return setcookie( $key, $val, time() + $expire,'/',NULL,NULL,$httponly);
			}
			$value = sys_auth(serialize($val), 'ENCODE', AUTH_KEY, $expire);
			return setcookie( $key, $value, time() + $expire,'/',NULL,NULL,$httponly);	
		}
	}
}
<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
define('CLS_SECURITY_FILR_DIR',str_replace('\\','/',dirname(__FILE__)).'/Security');
class security {
	//加解密函数
	static function sys_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
		$ckey_length = 4;
		$key = md5($key != '' ? $key : "asfw#@!*^%!");
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}
	//js>php,rsa decrypt
	static function rsa_decrypt($crypttext,$private_key){ 
		$crypttext	= base64_encode(pack("H*", $crypttext)); 
		$key_content= base64_decode($private_key)?base64_decode($private_key):$private_key;  
		$prikeyid	= openssl_get_privatekey($key_content);  
		$crypttext	= base64_decode($crypttext);  
		$padding	= OPENSSL_NO_PADDING ;  
		if (openssl_private_decrypt($crypttext, $sourcestr, $prikeyid, $padding)){  
			return  rawurldecode(rtrim(strrev($sourcestr), "/0"));  
		}
		return;
	}
	static function remove_xss($val){
		require_cache(CLS_SECURITY_FILR_DIR.'/xss.php');
		return security_xss::remove($val);
	}
	//手机端访问识别
	static function check_mobile(){
		require_cache(CLS_SECURITY_FILR_DIR.'/mobile.php');
		return security_mobile::check();	
	}
}
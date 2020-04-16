<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class string_password {
	//获得随机字符串
	static function random($length, $numeric = 0) {
		PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
		if ($numeric) {
			$hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
		} else {
			$hash = '';
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
			$max = strlen($chars) - 1;
			for ($i = 0; $i < $length; $i++) {
				$hash.=$chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}
	static function password($pwd){
		$salt = substr(uniqid(mt_rand()), -6);
		return array('password'=>md5(md5($pwd).$salt),'salt'=>$salt);
	}
	//生成UUID 单机使用
	static function uuid() {
		$charid = md5(uniqid(mt_rand(), true));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
		.substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12)
		.chr(125);// "}"
		return $uuid;
	}
}
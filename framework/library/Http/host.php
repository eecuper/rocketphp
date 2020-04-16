<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class http_host {
	static function get(){
		$host  = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://');
		$host .=  $_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] :'');
		return  $host.rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])),'/').'/';
	}
	static function get_city($queryIP){
		$url    = 'http://www.ip138.com/ips138.asp?ip='.$queryIP;
		$result = http::get($url);
		preg_match('@<ul class="ul1"><li>(.*)</li>@iU',$result,$ipArray);
		$loc    = mb_convert_encoding($ipArray[1], 'utf-8', 'gb2312');
		$city   = explode('：',$loc);
		return $city[1];
	}
}
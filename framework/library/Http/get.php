<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class http_get{
	static function curl_get($url,$headers = array(),$timeout = 5){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		if($headers){
			$header = array();
			foreach($headers as $key => $val){
				if(is_string($key)){
					$header[] = $key.': '.$val;
				}else{
					$header[] = $val;
				}
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; SeaPort/1.2; Windows NT 5.1; SV1; InfoPath.2)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if(stripos($url, 'https://') !== FALSE){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		}
		$data = curl_exec($ch);
		if(curl_errno($ch)){
    		http::curl_error_log('curl_get error : '.curl_error($ch).' url : '.$url);
		}
		curl_close($ch);
		return $data;
	}
}
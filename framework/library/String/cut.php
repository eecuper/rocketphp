<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class string_cut {
	//字符串截取
	static function str($str, $length , $dot = '...', $start = 0, $charset="utf-8" ) {
		if(function_exists("mb_substr")){
			$slice = mb_substr($str, $start, $length, $charset);
		}elseif(function_exists('iconv_substr')) {
			$slice = iconv_substr($str,$start,$length,$charset);
			if(false === $slice) {
				$slice = '';
			}
		}else{
			$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re[$charset], $str, $match);
			$slice = join("",array_slice($match[0], $start, $length));
		}
		if($str == $slice){
			return $str;
		}else{
			return $slice.$dot;	
		}
	}
	//切割html
	static function html($sourcestr, $cutlength = 20, $lenstr = '', $allow = '') {
		$sourcestr 	= str_replace('&nbsp;', ' ', $sourcestr);
		$sourcestr	= html_entity_decode($sourcestr);
		$sourcestr 	= preg_replace('/\n|\r/i', '', $sourcestr);
		$sourcestr 	= trim($sourcestr);
		$sourcestr 	= strip_tags($sourcestr, $allow);
		$returnstr 	= self::str($sourcestr, $cutlength, $lenstr);
		return $returnstr;
	}
}
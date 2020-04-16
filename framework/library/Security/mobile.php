<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class security_mobile {
	//手机端访问识别
	static function check() {
		$mobile = array();
		static $mobilebrowser_list =array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
					'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
					'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
					'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
					'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
					'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
					'benq', 'haier', '^lct', '320x320', '240x320', '176x220');
		$pad_list = array('pad', 'gt-p1000');
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
		if(self::dstrpos($useragent, $pad_list)) {
			return false;
		}
		if(($v = self::dstrpos($useragent, $mobilebrowser_list, true))) {
			$_G['mobile'] = $v;
			return true;
		}
		$brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');
		if(self::dstrpos($useragent, $brower)) return false;
		$_G['mobile'] = 'unknown';
		if($_GET['mobile'] === 'yes') {
			return true;
		} else {
			return false;
		}
	}
	static function dstrpos($string, $arr, $returnvalue = false) {
		if(empty($string)) return false;
		foreach((array)$arr as $v) {
			if(strpos($string, $v) !== false) {
				$return = $returnvalue ? $v : true;
				return $return;
			}
		}
		return false;
	}
}
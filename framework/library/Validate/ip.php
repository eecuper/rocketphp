<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class validate_ip{
	//检查ip地址
	public static function is_ip($ip, $which = ''){
		$which = strtolower($which);
		if (is_callable('filter_var')){
			switch ($which) {
				case 'ipv4':
					$flag = FILTER_FLAG_IPV4;
					break;
				case 'ipv6':
					$flag = FILTER_FLAG_IPV6;
					break;
				default:
					$flag = '';
					break;
			}
			return filter_var($ip, FILTER_VALIDATE_IP, $flag) ? true : false;
		}
		if ($which !== 'ipv6' && $which !== 'ipv4'){
			if (strpos($ip, ':') !== FALSE){
				$which = 'ipv6';
			}elseif (strpos($ip, '.') !== FALSE)	{
				$which = 'ipv4';
			}else{
				return false;
			}
		}
		$func = '_valid_'.$which;
		return self::$func($ip);
	}
	//ipv4
	public static function _valid_ipv4($value){
		if (preg_match('/^([01]{8}.){3}[01]{8}$/i', $value)) {
            $value = bindec(substr($value, 0, 8)) . '.' . bindec(substr($value, 9, 8)) . '.'
                   . bindec(substr($value, 18, 8)) . '.' . bindec(substr($value, 27, 8));
        } elseif (preg_match('/^([0-9]{3}.){3}[0-9]{3}$/i', $value)) {
            $value = (int) substr($value, 0, 3) . '.' . (int) substr($value, 4, 3) . '.'
                   . (int) substr($value, 8, 3) . '.' . (int) substr($value, 12, 3);
        } elseif (preg_match('/^([0-9a-f]{2}.){3}[0-9a-f]{2}$/i', $value)) {
            $value = hexdec(substr($value, 0, 2)) . '.' . hexdec(substr($value, 3, 2)) . '.'
                   . hexdec(substr($value, 6, 2)) . '.' . hexdec(substr($value, 9, 2));
        }
        $ip2long = ip2long($value);
        if ($ip2long === false) {
            return false;
        }
        return ($value == long2ip($ip2long));
	}
	//ipv6检查
	public static function _valid_ipv6($value){
		if (strlen($value) < 3) {
            return $value == '::';
        }
        if (strpos($value, '.')) {
            $lastcolon = strrpos($value, ':');
            if (!($lastcolon && self::_valid_ipv4(substr($value, $lastcolon + 1)))) {
                return false;
            }
            $value = substr($value, 0, $lastcolon) . ':0:0';
        }
        if (strpos($value, '::') === false) {
            return preg_match('/\A(?:[a-f0-9]{1,4}:){7}[a-f0-9]{1,4}\z/i', $value);
        }
        $colonCount = substr_count($value, ':');
        if ($colonCount < 8) {
            return preg_match('/\A(?::|(?:[a-f0-9]{1,4}:)+):(?:(?:[a-f0-9]{1,4}:)*[a-f0-9]{1,4})?\z/i', $value);
        }
        if ($colonCount == 8) {
            return preg_match('/\A(?:::)?(?:[a-f0-9]{1,4}:){6}[a-f0-9]{1,4}(?:::)?\z/i', $value);
        }
        return false;
	}
}
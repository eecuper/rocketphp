<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class string_alpha_id{
	static function make($in, $to_num = false, $pad_up = false, $passKey = null){  
	  $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";  
	  if ($passKey !== null) {  
		for ($n = 0; $n<strlen($index); $n++) {  
		  $i[] = substr( $index,$n ,1);  
		}  
		$passhash = hash('sha256',$passKey);  
		$passhash = (strlen($passhash) < strlen($index))  
		  ? hash('sha512',$passKey)  
		  : $passhash;  
		for ($n=0; $n < strlen($index); $n++) {  
		  $p[] =  substr($passhash, $n ,1);  
		}  
		array_multisort($p,  SORT_DESC, $i);  
		$index = implode($i);  
	  }  
	  $base  = strlen($index);  
	  if ($to_num) {  
		$in  = strrev($in);  
		$out = 0;  
		$len = strlen($in) - 1;  
		for ($t = 0; $t <= $len; $t++) {  
		  $bcpow = bcpow($base, $len - $t);  
		  $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;  
		}  
		if (is_numeric($pad_up)) {  
		  $pad_up--;  
		  if ($pad_up > 0) {  
			$out -= pow($base, $pad_up);  
		  }  
		}  
		$out = sprintf('%F', $out);  
		$out = substr($out, 0, strpos($out, '.'));  
	  } else {  
		if (is_numeric($pad_up)) {  
		  $pad_up--;  
		  if ($pad_up > 0) {  
			$in += pow($base, $pad_up);  
		  }  
		}  
		$out = "";  
		for ($t = floor(log($in, $base)); $t >= 0; $t--) {  
		  $bcp = bcpow($base, $t);  
		  $a   = floor($in / $bcp) % $base;  
		  $out = $out . substr($index, $a, 1);  
		  $in  = $in - ($a * $bcp);  
		}  
		$out = strrev($out);
	  }  
	  return $out;  
	}
}
<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class alarm{
	static function slow(){ 
		$elapsed_time = number_format(abs(microtime(true) - SLOW_LOG_START),3);
		if($elapsed_time >= SLOW_LOG_TIME)
			log::d('','<b style="color:#c00">SLOW:</b> '.$elapsed_time.'s');
	}
	static function memory(){
		$m = abs(memory_get_usage() - SLOW_LOG_USAGE_START)/1024;
		if($m >= MEMORY_LOG_SIZE)
			log::d('','<b style="color:#c00">MEMORY:</b> '.number_format($m).'k');
	}
}
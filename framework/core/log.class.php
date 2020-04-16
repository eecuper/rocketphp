<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
defined('LOG_STYLE') 			or define('LOG_STYLE', 2);//db用的
defined('LOG_ROLL_FILE_SIZE')	or define('LOG_ROLL_FILE_SIZE', 3);//文件切割大小,单位为MB
defined('LOG_COMPRESS')			or define('LOG_COMPRESS', 1);//是否启用压缩
defined('LOG_FILTER')			or define('LOG_FILTER', '*');//标签过滤器,多个标签用逗号隔开
define('_IS_CLI',				php_sapi_name() == "cli");
class log{
	static $off = 0;
	static function d($tag = '',$content = ''){
		if(self::$off or (ROUTE_M == 'api' && (ROUTE_A == 'log' || ROUTE_A == 'dellog')))
			return;
		//分标签记录,只记录固定标签的log,
		if(LOG_FILTER !== '' && LOG_FILTER != '*'){
			$tagc = $tag !== '' ? strtolower(strip_tags($tag)):'debug';
			$arr = array_map('trim',explode(',',LOG_FILTER));
			if(!in_array($tagc,$arr)){
				return;
			}
		}
		self::get_instance();
		log_instance::d($tag,$content);
	}
	static function show(){
		self::auth_check();
		self::get_instance();
		if(SHOW_LOG){
			log_instance::show();
		}else{
			echo '>>Log closed ...!';
		}
	}
	static function del(){
		self::auth_check();
		self::get_instance();
		log_instance::del();
		if(isset($_GET['fl']))
			exit('>>Log delt!<script>top.location=top.location</script>');
		if(isset($_GET['path']))
			exit('>>Log delt!<script>setTimeout(function(){window.close()},1000)</script>');
		exit('>>Log is delt!<script>setTimeout(function(){window.location.href="'
		.$_SERVER['HTTP_REFERER'].'"},1000)</script>');
	}
	static function response(){
		$str = ob_get_contents();
		self::d('','<b style="color:green">RESPONSE:</b><br/>'.htmlspecialchars($str).'<br/>');
	}
	static function auth_check(){
		if(!(LOG_AUTHED == 0 or $_COOKIE['log_authed_key'] == LOG_AUTHED_KEY)){
			exit('No Permission!');
		}
	}
	static function get_instance(){
		static $loaded = 0;
		if(!$loaded){
			$dir  = str_replace('\\','/',dirname(__FILE__)).'/log/';
			$path = in_array(LOG_FORMAT,array('file','file_advanced','db')) 
			? $dir.'log_'.LOG_FORMAT.'.php'
			: $dir.'log_file.php';
			;
			include $path;
			$loaded = 1;
		}
	}
	static function get_head(){
		$r['ip']	= empty($_SERVER['HTTP_X_REAL_IP'])
					  ?(_IS_CLI?'127.0.0.1':$_SERVER['REMOTE_ADDR']):$_SERVER['HTTP_X_REAL_IP'];
		$r['method']= _IS_CLI ? 'CLI' : $_SERVER['REQUEST_METHOD'];
		$r['url']  	= _IS_CLI ? str_replace('\\','/',implode(' ',$_SERVER['argv']))
					  : htmlspecialchars(urldecode($_SERVER['REQUEST_URI']));
		$r['time'] 	= date('m-d H:i:s',$_SERVER['REQUEST_TIME']);
		return $r;
	}
}
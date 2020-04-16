<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class base{
	static function run_application($app = '', $module = '', $action = ''){
		if(defined('CMS_ROOT')){
			return;
		}
		self::init();
		//create app
		define('APPLICATION', $app == '' ? 'application' : $app);
		if(APPLICATION == trim(SYS_DIR, '/')){
			exit('Sorry, you can not create system application!');
		}

		define('APP_PATH', CMS_ROOT.APPLICATION);
		if(!is_dir(APP_PATH)){
			app::create(APPLICATION);
		}

		//load app config
		require APP_PATH.'/config/config.php';

		//load common config
		require SYS_ROOT.'config/public.php';
		require SYS_ROOT.'config/common.'.ENV.'.php';

		//load common function
		if(is_file(CMS_ROOT.'common/lib/app.func.php')){
			include CMS_ROOT.'common/lib/app.func.php';
		}
		if(is_file(APP_PATH.'/lib/app.func.php')){
			include APP_PATH.'/lib/app.func.php';
		}

		//load core files
		require SYS_ROOT.'core/controller.class.php';
		require SYS_ROOT.'core/global.func.php';
		require SYS_ROOT.'core/log.class.php';
		require SYS_ROOT.'core/model.class.php';
		require SYS_ROOT.'core/view.class.php';
		$dbdriver = DB_DRIVER == 'mysqli' ? 'mysqlii' : (DB_DRIVER == 'pdo' ? 'pdoo' : 'mysql');
		require SYS_ROOT.'core/db/'.$dbdriver.'.class.php';

		//init application
		$timezone = defined('TIMEZONE') && TIMEZONE != '' ? TIMEZONE : 'Asia/Shanghai';
		date_default_timezone_set($timezone);

		if(ERROR_LOG){
			set_error_handler('base::error_app');
		}
		if(SLOW_LOG){
			define('SLOW_LOG_START', microtime(TRUE));
			register_shutdown_function('alarm::slow');
		}
		if(MEMORY_LOG){
			define('SLOW_LOG_USAGE_START', memory_get_usage());
			register_shutdown_function('alarm::memory');
		}
		if(TRACE_LOG){
			register_shutdown_function('trace::end');
			trace::start();
		}

		//diapatch
		if(!empty($_GET['r'])){
			$arr = explode('/', str_replace('\\', '/', getg('r')));
			$m   = isset($arr[0]) ? $arr[0] : '';
			$a   = isset($arr[1]) ? $arr[1] : '';
		}else{
			$m = getg('m');
			$a = getg('a');
		}
		define('ROUTE_M', $module == ''
			? ($m
				? $m
				: (isset($_SERVER['argv'][1])
					? $_SERVER['argv'][1]
					: 'main'
				)
			)
			: $module
		);
		define('ROUTE_A', $action == ''
			? ($a
				? $a
				: (isset($_SERVER['argv'][2])
					? $_SERVER['argv'][2]
					: 'index'
				)
			)
			: $action
		);

		//解析cli第三个参数为$_GET,需要时"c=d&e=f"
		if(IS_CLI && !empty($_SERVER['argv'][3])){
			parse_str($_SERVER['argv'][3], $_GET);
		}

		//run application
		if(!in_array(ROUTE_M, explode(',', MODULES))){
			message::error_404('Module ['.ROUTE_M.'] not allowed!');
		}

		$filename = APP_PATH.'/controls/'.ROUTE_M.'.php';
		if(!is_file($filename)){
			message::error_404('Module ['.ROUTE_M.'] load error!');
		}
		include $filename;

		$classname = ROUTE_M;
		if(class_exists($classname, false)){
			$control = new $classname();
			$method  = 'on_'.ROUTE_A;
			if(method_exists($control, $method)){
				$control->$method();
			}else if(IS_CLI && method_exists($control, 'do_'.ROUTE_A)){
				$method = 'do_'.ROUTE_A;
				$control->$method();
			}else{
				message::error_404('Action ['.ROUTE_A.'] not found!');
			}
		}else{
			message::error_404('Controller ['.$classname.'] not found!');
		}
	}

	//init
	private static function init(){
		self::init_env();
		//register_autoload
		spl_autoload_register('base::autoload');
		//register_exception
		set_exception_handler('base::exception');
		//register shut down func
		register_shutdown_function('base::error_fatal');
		unset($GLOBALS, $_REQUEST, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);

	}

	//init_env
	private static function init_env(){
		define('CMS_ROOT', str_replace('\\', '/', dirname(dirname(__FILE__))).'/');
		define('SYS_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');
		define('SYS_DIR', basename(dirname(__FILE__)).'/');
		define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
		error_reporting(0);
		if(version_compare(PHP_VERSION, '5.4.0', '<')){
			ini_set('magic_quotes_runtime', 0);
			ini_set('magic_quotes_sybase', 0);
			define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
		}else{
			define('MAGIC_QUOTES_GPC', false);
		}
		header('Content-type: text/html; charset=UTF-8');
		header('X-Powered-By: FastPHP');
		if(function_exists('ob_gzhandler')){
			ob_start('ob_gzhandler');
		}else{
			ob_start();
		}
	}

	//autoload
	static function autoload($class_name){
		if(!class_exists($class_name, false)){
			//load system
			$core    = array('config', 'template');
			$library = array('alarm', 'app', 'cache', 'checkcode', 'cookie', 'crypt', 'date', 'email', 'html', 'http', 'image',
				'lang', 'message', 'page', 'rpc', 'security', 'session', 'string', 'trace', 'upload', 'validate');
			$dir     = '';
			if(in_array($class_name, $core)){
				$dir = SYS_ROOT.'core';
			}else if(in_array($class_name, $library)){
				$dir = SYS_ROOT.'library';
			}
			if($dir){
				require $dir.'/'.$class_name.'.class.php';
				return;
			}

			//load controller
			if(substr($class_name, -11) == '_controller'){
				$file = APP_PATH.'/controls/'.$class_name.'.php';
				if(is_file($file)){
					include $file;
					return;
				}
				fatal_error('Class '.$class_name.' load error!');
			}

			//load model
			if(substr($class_name, -6) == '_model'){
				$dir_file = str_replace('_', '/', substr($class_name, 0, -6)).'_model.php';
				$file     = APP_PATH.'/models/'.$dir_file;
				if(is_file($file)){
					include $file;
				}else{
					$ok = include CMS_ROOT.'common/models/'.$dir_file;
					if(!$ok){
						fatal_error('Class '.$class_name.' load error!');
					}
				}
				return;
			}

			//load by namespace
			if(strpos($class_name, '\\') !== false){
				if(defined('DEBUG_BACKTRACE_IGNORE_ARGS')){
					$back = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
				}else{
					$back = debug_backtrace();
				}
				$call = $back[1];
				unset($back);
				$file = dirname($call['file']).'/'.str_replace('\\', '/', $class_name).'.php';
				if(is_file($file)){
					include $file;
					return;
				}
				fatal_error('Class '.$class_name.' load error!');
			}

			//load lib class
			$dir_file = str_replace('_', '/', $class_name).'.php';
			$file     = APP_PATH.'/lib/'.$dir_file;
			if(is_file($file)){
				include $file;
			}else{
				$ok = include CMS_ROOT.'common/lib/'.$dir_file;
				if(!$ok){
					fatal_error('Class '.$class_name.' load error!');
				}
			}
		}
	}

	//exception
	static function exception($e){
		log::d(
			'<b>Exception</b>',
			htmlspecialchars($e->getMessage())
			.'<br/>in '
			.$e->getFile()
			.' on line '
			.$e->getLine()
			.'<br/>Trace: <br/>'
			.nl2br($e->getTraceAsString())
		);
		header('HTTP/1.1 500 Internal Server Error', TRUE, 500);
		if(ENV == 'dev'){
			echo '<font color="red">500 Internal Server Error!</font>';
		}
	}

	//ERROR_LEVEL:notice,warning,error,all
	static function error_app($errno, $errstr, $errfile, $errline){
		if((!defined('ERROR_LEVEL') || ERROR_LEVEL == 'all' || ERROR_LEVEL == 'notice')
			|| (ERROR_LEVEL == 'warning' && in_array($errno, array(E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING)))
		){
			$block_error = preg_match('#^include\(.*?\)#', $errstr) && strpos($errfile, __DIR__) !== false;
			if(!$block_error){
				log::d('<b>Error</b>', htmlspecialchars(mb_convert_encoding($errstr, 'utf8', 'gb2312')).'<br/>in '.$errfile.' on line '.$errline);
			}
		}
	}

	static function error_fatal(){
		if($error = error_get_last()){
			switch($error ['type']){
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
					if(!defined('ROUTE_M')){
						exit('<b style="color:#c00">Fatal error: </b> <br/>'.$error['message'].'<br/>in '.$error['file'].' on line '.$error['line']);
					}
					log::d('<b>Fatal error</b>', htmlspecialchars($error['message']).'<br/>in '.$error['file'].' on line '.$error['line']);
					if(ENV == 'dev'){
						echo '<font color="red">500 Internal Server Error!</font>';
					}
					header('HTTP/1.1 500 Internal Server Error', TRUE, 500);
			}
		}
	}
}
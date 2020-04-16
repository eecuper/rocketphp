<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class trace{
	private static $start_time;
	private static $start_usage;
	private static $sqls       = array();
	private static $sql_wc     = 0;
	private static $trace_item = array();

	static function start(){
		if(TRACE_LOG){
			if(self::is_trace('time')){
				self::$start_time['global'] = self::my_microtime();
			}
			if(self::is_trace('usage')){
				self::$start_usage['global'] = memory_get_usage();
			}
		}
	}

	private static function is_trace($item){
		if(empty(self::$trace_item)){
			parse_str(str_replace(';', '&', defined('TRACE_ITEMS') ? TRACE_ITEMS : ''), self::$trace_item);
		}
		return !empty(self::$trace_item[$item]);
	}

	//开始跟踪时间
	static function trace_start($tag = 'tag'){
		self::$start_time[$tag] = self::my_microtime();
	}

	//结束跟踪时间
	static function trace_end($tag = 'tag', $storage = false){
		$elapsed_time = number_format(abs(self::my_microtime() - self::$start_time[$tag]), 6).'s';
		if($storage)
			return $elapsed_time;
		else
			log::d('', ' (Tag ['.$tag.'] time:<font color="red"> '.substr($elapsed_time, 0, -3).' '.substr($elapsed_time, -3).'</font>)');
	}

	//trace_sql
	static function sql($sql, $type){
		if(self::is_trace('db')){
			if($type == 1){
				self::$sqls[] = $sql;
			}else{
				self::$sql_wc++;
			}
		}
	}

	/*
	调试跟踪输出,	time 运行时间,usage 内存使用,maxusage  
	内存峰值,	userclass 用户类,	userclass_detail 用户类详情,userfunc 用户函数
	userfunc_detail 用户函数详情,	include 加载文件数,include_detail 加载文件
	*/
	static function end(){
		if(TRACE_LOG){
			if(defined('TRACE_FILTER') && TRACE_FILTER !== '*' && !in_array(ROUTE_M.'_'.ROUTE_A, array_map('trim', explode(',', TRACE_FILTER))))
				return;
			$r = array();

			if(self::is_trace('time')){
				$elapsed_time = number_format((self::my_microtime() - self::$start_time['global']), 6);
				$elapsed_time = substr($elapsed_time, 0, -3).' '.substr($elapsed_time, -3);
				$r['运行时间']    = $elapsed_time.'秒';
			}
			if(self::is_trace('usage')){
				$r['内存使用'] = number_format((memory_get_usage() - self::$start_usage['global']) / 1024, 3).'K';
			}
			if(self::is_trace('maxusage')){
				$r['内存峰值'] = number_format((memory_get_peak_usage() - self::$start_usage['global']) / 1024, 3).'K';
			}
			if(self::is_trace('userclass') || self::is_trace('userclass_detail')){
				$userclas = array();
				foreach(get_declared_classes() as $class){
					$reflection = new ReflectionClass($class);
					if($reflection->isUserDefined()){//判断是否是自声明的类
						$userclas[] = $class;
					}
				}
			}
			if(self::is_trace('userclass')){
				$r['用户类'] = count($userclas);
			}
			if(self::is_trace('userclass_detail')){
				$r['用户类详情'] = $userclas;
			}
			if(self::is_trace('userfunc')){
				$func      = get_defined_functions();
				$r['用户函数'] = count($func['user']);
			}
			if(self::is_trace('userfunc_detail')){
				$func        = get_defined_functions();
				$r['用户函数详情'] = $func['user'];
			}
			if(self::is_trace('db')){
				$r['数据库操作'] = 'Read:'.count(self::$sqls).' Write:'.self::$sql_wc;
				$r['SQL语句'] = print_r(self::$sqls, true);
			}
			if(self::is_trace('include')){
				$r['加载文件数'] = count(get_included_files());
			}
			if(self::is_trace('include_detail')){
				$files = get_included_files();
				$total = 0;
				foreach($files as $k => $file){
					$t         = filesize($file);
					$files[$k] .= ' '.number_format($t, 0, '', ', ');
					$total     += $t;
				}
				$files[]   = 'Total: '.number_format($total, 0, '', ', ');
				$r['加载文件'] = $files;
			}
			if($r){
				log::d('运行跟踪', $r);
			}
		}
	}

	static function my_microtime(){
		$time = microtime();
		$arr  = explode(' ', $time);
		return $arr[1].substr($arr[0], 1);
	}
}
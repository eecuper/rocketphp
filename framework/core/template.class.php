<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class template{
	static function parse_file($tpl_file,$cache_file){
		file_put_contents($cache_file,self::parse(file_get_contents($tpl_file)));
		//chmod($cache_file,0777);
	}
	static function parse( $str ) {
		$str = self::parse_literal($str);
		/*$str = preg_replace ( '/<!--.+?-->/s','',$str );*/
		/*$str = preg_replace ( "/\{include\s+(.+)\}/", "<?php include \\1; ?>", $str );*/
		$str = preg_replace ( "/\{php\s+([^\}]+?)\}/is", "<?php \\1?>", $str );
		$str = preg_replace ( "/\{if\s+(.+?)\}/", "<?php if(\\1) { ?>", $str );
		$str = preg_replace ( "/\{else\}/", "<?php } else { ?>", $str );
		$str = preg_replace ( "/\{elseif\s+(.+?)\}/", "<?php } elseif (\\1) { ?>", $str );
		$str = preg_replace ( "/\{else if\s+(.+?)\}/", "<?php } elseif (\\1) { ?>", $str );
		$str = preg_replace ( "/\{\/if\}/", "<?php } ?>", $str );
		$str = preg_replace ( "/\{for\s+(.+?)\}/","<?php for(\\1) { ?>",$str);
		$str = preg_replace ( "/\{\/for\}/","<?php } ?>",$str);
		$str = preg_replace ( "/\{\+\+(.+?)\}/","<?php ++\\1; ?>",$str);
		$str = preg_replace ( "/\{\-\-(.+?)\}/","<?php ++\\1; ?>",$str);
		$str = preg_replace ( "/\{([^\{\}]+?)\+\+\}/","<?php \\1++; ?>",$str);
		$str = preg_replace ( "/\{([^\{\}]+?)\-\-\}/","<?php \\1--; ?>",$str);
		$str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\}/", "<?php \$n=1;if((\\1) && is_array(\\1)) foreach(\\1 AS \\2) { ?>", $str );
		$str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", "<?php \$n=1; if((\\1) && is_array(\\1)) foreach(\\1 AS \\2 => \\3) { ?>", $str );
		$str = preg_replace ( "/\{\/loop\}/", "<?php \$n++;} ?>", $str );
		$str = preg_replace ( "/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/s","<?php echo \\1;?>", $str );
		$str = preg_replace ( "/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str );
		$str = preg_replace ( "/\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str );
		$str = preg_replace ( "/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str );
		/*$str = preg_replace ( "/\{(\\$[a-zA_Z_\x7f-\xff][^{}]*?\?[^{}]*?\:[^{}]*?)\}/", "<?php echo \\1;?>", $str );*/
		$str = preg_replace ( "/\{([^\s\?\r\n][^\{\}:\r\n]+?[^<]\?[^>][^\{\}]*?:[^\{\}]+?)\}/", "<?php echo \\1;?>", $str );//三目运算符
		$str = preg_replace ( "/\{([^\{\}:]+?::[^\{\}:]*?[^<]\?[^>][^\{\}]*?:[^\{\}]+?)\}/", "<?php echo \\1;?>", $str );//三目运算符2
		$str = preg_replace ( "/\{echo\s+([^\}]+?)\}/is", "<?php echo \\1;?>", $str );
		$str = preg_replace ( "/\{dump\s+([^\}]+?)\}/is", "<?php var_dump(\\1);?>", $str );
		$str = preg_replace ( "/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1;?>", $str );
		$str = self::parse_static($str);
		$str = "<?php defined('ISROCKET') or exit('Denied!'); ?>\n" . $str;
        $str = preg_replace(array("~>\s+<~","~>(\s+\n|\r)~"), array('><','>'), $str);
		$str = self::parse_literal($str,2);
		self::security_check($str);
		$str = preg_replace ( "/\{template\s+(.+)\}/", "<?php include template(\\1); ?>", $str );
		return $str;
	}
	static function addquote($var) {
		return str_replace ( "\\\"", "\"", preg_replace ( "/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var ) );
	}
	static function parse_static($str){
		preg_match_all("/\{%(.*?)%\}/",$str,$matches);
		if($matches[0]){
			foreach($matches[0] as $k=>$v){
				$str = str_replace($matches[0][$k],eval('return '.$matches[1][$k].';'),$str);
			}	
		}
		preg_match_all("/\{(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/s",$str,$matches);
		if($matches[0]){
			foreach($matches[0] as $k=>$v){
				$var = str_replace ( "\\\"", "\"", preg_replace ( "/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $matches[1][$k]));
				$str = str_replace ($matches[0][$k],'<?php echo '.$var.';?>',$str);
			}	
		}
		preg_match_all("#<style>.*?</style>#is",$str,$matches);
		if($matches[0]){
			foreach($matches[0] as $k=>$v){
				$str = str_replace ($matches[0][$k],str_replace(array("\r","\n","\t"),' ',$matches[0][$k]),$str);
			}
		}
		return $str;
	}
	static function parse_literal($str,$type = 1){
		static $literals = array();
		if($type == 1){
			preg_match_all("#<literal>.*?</literal>#s",$str,$matches);
			if($matches[0]){
				$literals = $matches[0];
				foreach($literals as $k=>$v){
					$str = str_replace($v,'<!##literal'.$k.'##!>',$str);
				}
			}	
		}
		if($type == 2 && !empty($literals)){
			foreach($literals as $k=>$v){
				$str = str_replace('<!##literal'.$k.'##!>',substr($v,9,-10),$str);
			}
		}
		return $str;
	}
	static function clear_app_caches($app = ''){
		$dir 	= (empty($app) ? APPLICATION : $app).'/caches/templates/';
		$files 	= glob($dir.'*.php');
		if($files){
			foreach($files as $file){
				unlink($file);
			}	
		}
	}

	static function security_check($str){
		$replace = array(
			//获取上下文
			'__FILE__',
			'__DIR__',
			'__CLASS__',
			'__FUNCTION__',
			'__LINE__',
			'get_defined_functions',
			'get_defined_constants',
			'get_included_files',
			'get_required_files',
			'get_declared_classes',
			'get_class_methods',
			'get_class_vars',
			'get_loaded_extensions',
			'php_ini_loaded_file',
			//文件夹操作
			'glob',
			'scandir',
			'dir',
			'readdir',
			'rmdir',
			'mkdir',
			//文件操作
			'copy',
			'file',
			'file_get_contents',
			'file_put_contents',
			'fopen',
			'move_uploaded_file',
			'rename',
			'unlink',
			'delete',
			'fread',
			'fwrite',
			'fputs',
			'fputcsv',
			'fflush',
			'readfile',
			'chroot',
			'chgrp',
			'chown',
			'chmod',
			//网络操作
			'stream_socket_client',
			'fsockopen',
			'pfsockopen',
			'socket_create',
			'socket_connect',
			'curl_exec',
			'curl_init',
			//进程控制
			'phpinfo',
			'passthru',
			'exec',
			'system',
			'shell_exec',
			'proc_open',
			'proc_get_status',
			'error_log',
			'readlink',
			'symlink',
			'popen',
			'fsocket',
			'syslog',
			'getenv',
			'putenv',
			'dl',
			'pcntl_fork',
			//包含
			'include',
			'include_once',
			'require',
			'require_once',
			'eval',
			'assert',
			'assertions',
			'create_function',
			'call_user_func',
			'call_user_func_array',
			'ReflectionClass',
			'ReflectionFunction',
			//userdir
			'dir_create',
			'dir_copy',
			'dir_list',
			'dir_tree',
			'dir_delete',
			'dir_delete_files'
		);
		$tokens = token_get_all($str);
		$funcs = array();
		$error = false;
		foreach($tokens as $v){
			if(is_long($v[0]) && in_array($v[0], array(T_STRING, T_CLASS_C, T_FUNC_C, T_FILE, T_DIR))){
				if($v[0] == T_STRING && in_array($v[1], array('DB_HOST', 'DB_USER', 'DB_PWD'))){
					$error = true;
					break;
				}
				$funcs[] = $v[1];
			}
		}
		$has = array_intersect($replace, $funcs);
		if($error || !empty($has)){
			fatal_error('Template attempt to call forbidden functions! <br/>['.implode(', ', $has).']', false);
		}
	}
}
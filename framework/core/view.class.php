<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class view{
	var $vars = array();
	function assign($k = '', $v = ''){
		if(is_array($k)) {
            $this->vars   =  array_merge($this->vars,$k);
        }else{
            $this->vars[$k] = $v;
        }
	}
	function display($filename = ''){
		if($this->vars){
			extract($this->vars, EXTR_SKIP);
		}
		include $this->template_cache($filename);
	}
	function create($filename = ''){
		$tfile = $filename.'.'.(defined('TEMPLATE_SUFFIX') ? TEMPLATE_SUFFIX : 'html');
		$tpl_file   = APP_PATH.'/templates/'.TEMPLATE_STYLE.'/'.$tfile;
		$dir		= dirname($tpl_file);
		if(!is_dir($dir)){
			mkdir($dir,0777,true);
		}
		if(!is_file($tpl_file)){
			file_put_contents($tpl_file,'Template file ['.$tfile.'] created success! '.date('Y-m-d H:i:s'));
		}
	}
	function get_content($filename = ''){
		ob_start();
		$this->display($filename);
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}
	static function template_cache($filename){
		if(!preg_match('/^[a-z\d]+[\/a-z_\.\-]*[a-z\d]+$/',$filename)){
			fatal_error('Template name error!',false);
		}
		$suffix		= defined('TEMPLATE_SUFFIX') ? TEMPLATE_SUFFIX : 'html';
		$cache_file = APP_PATH.'/caches/templates/'.md5(TEMPLATE_STYLE.$filename.AUTH_KEY).'.php';
		$tpl_file   = APP_PATH.'/templates/'.TEMPLATE_STYLE.'/'.$filename.'.'.$suffix;
		if(defined('TRACE_TEMPLATE')){
			log::d('Template', basename($cache_file).' => /'.$filename.'.'.$suffix);
		}
		$exist_cache= is_file($cache_file);
		$exist_tpl  = is_file($tpl_file);
		if((!$exist_cache && !$exist_tpl)||(ENV == 'dev' && !$exist_tpl)){
			fatal_error($tpl_file.' does not exist!',false);
		}
		if(!$exist_cache or ($exist_tpl && (filemtime($cache_file) < filemtime($tpl_file)))){
			template::parse_file($tpl_file,$cache_file);
		}
		return $cache_file;
	}
}
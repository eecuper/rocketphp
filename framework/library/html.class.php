<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html{
	static function __callStatic($name, $argus){
		$dir = str_replace('\\','/',dirname(__FILE__)).'/html';
		if(in_array($name,array('select','radio','checkbox','button','form_url_vars'))){
			require_cache($dir.'/form.php');
			return call_user_func_array(array('html_form',$name),$argus);	
		}elseif(in_array($name,array('date','upload','editor','chart','table'))){
			require_cache($dir.'/'.$name.'.php');
			return call_user_func_array(array('html_'.$name,'make'),$argus);
		}elseif(in_array($name,array('load_sys_js','load_js','load_css'))){
			require_cache($dir.'/load.php');
			return call_user_func_array(array('html_load',str_replace('load_','',$name)),$argus);	
		}elseif(in_array($name,array('img_src','img'))){
			require_cache($dir.'/img.php');
			return call_user_func_array(array('html_img',($name == 'img' ? 'make':'src')),$argus);	
		}elseif(in_array($name,array('checkcode','check_checkcode'))){
			require_cache($dir.'/checkcode.php');
			return call_user_func_array(array('html_checkcode',($name == 'checkcode' ? 'make':'check')),$argus);	
		}elseif($name == 'tab'){
			require_cache($dir.'/tab.php');
			return call_user_func_array(array('html_tab','make'),$argus);
		}elseif($name == 'iframe'){
			require_cache($dir.'/iframe.php');
			return call_user_func_array(array('html_iframe','make'),$argus);
		}else{
			error('Method not found!');	
		}
	}
	static function clear_slash($string) {
		if (!is_array($string)) {
			return trim(str_replace(array("'",'"'),'',trim($string)));
		}
		foreach ($string as $key => $value) {
			$string[$key] = self::clear_slash($value);
		}
		return $string;
	}
	static function clear($str){
		return str_replace(array("\t","\r","\n"),'',$str);
	}		
	//配置处理
	static function O($items,$op){
		if(is_string($op)){
			$option = array();
			$o = explode(';',$op);
			foreach($o as $v){
				if($v){
					$option[self::clear_slash(substr($v,0,strpos($v,'=')))] 
					= self::clear_slash(substr($v,strpos($v,'=')+1));	
				}
			}
		}else{
			$option = $op;
		}
		foreach($items as $k=>$v){
			//k为item的key,获取自己需要的,
			if(isset($option[$k])){ 
				if(is_array($v)){
					if(is_array($option[$k]) && (empty($v) or count($option[$k]) == count($v))){
						$items[$k] = $option[$k]; 
					}
				}elseif(!is_array($option[$k]) && $option[$k] !== ''){
					$items[$k] = $option[$k];
				}
			}
		}
		return $items;
	}
}
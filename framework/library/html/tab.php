<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_tab{
	/**
	 * tab标签
	 * 调用方法:{html::tab($data))}
	 * $data 传入的值,需要$title 为键,$url 为值
	 */
	public static function make($data = array(),$default = 1,$load_more = false){
		$index = 0;
		$str  = '<link rel="stylesheet" type="text/css" href="'.SYS_JS_DIR
		.'/tab/tab'.(defined('TAB_THEME')?'_'.TAB_THEME:'').'.css?v=1.0"/>';
		if($load_more)
			$str .= '<script src="'.SYS_JS_DIR.'/tab/tab_more.js?v=1.0"></script>';
		$str .= '<div class="tab_wrapper"><ul>';
		if($data && is_array($data))
		foreach($data as $title => $url){
			$index++;
			if(is_array($url)){
				$keys 	= array_keys($url);
				$keys[] = '_tab_id';
				$nurl = '?';
				foreach($_GET as $k=>$v){
					if(!in_array($k,$keys))
						$nurl .= $k.'='.$v.'&';
				}
				foreach($url as $k=>$v){
					$nurl .= $k.'='.$v.'&';
				}
				$url = $nurl;
			}else{
				$url = $url == ''? '#' : $url.'&';
			}
			$selected = (isset($_GET['_tab_id'])?$_GET['_tab_id'] : $default) == $index ? 'selected':'';
			$str .='<li><div class="tabwraper '.$selected.'"><a href="'.$url.'_tab_id='.$index.'"><div class="tableft"></div>'.$title.'</a></div></li>';
		}
		$str .= '</ul></div>';
		return $str;
	}
}
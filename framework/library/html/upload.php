<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_upload{
	public static function make($option = ''){
		static $i = 1;
		static $urls = array();
		$op = array('callback'=>'upload_callback','type'=>'','width'=>'','height'=>'','maxheight'=>'','maxwidth'=>'','maxsize'=>'','minheight'=>'','minwidth'=>'','multiple'=>'','upload_limit'=>'','size'=>'');
		$param = html::O($op,$option);
		if($param['multiple'] && $param['callback'] =='upload_callback' )$param['callback'] = 'multiple_upload_callback';
		$url = '?'.(!empty($_GET['r'])?'r=api/upload':'m=api&a=upload').'&i='.$i;
		foreach($param as $k=>$v){
			if($v){
				$url .= '&'.$k.'='.$v;
			}
		}
		$url .='&v='.md5($url.AUTH_KEY);
		$string = '<iframe src="'.$url.'" id="upload_iframe_'.$i.'" frameborder="0" style="height:24px;width:54px;vertical-align:middle" scrolling="no"></iframe>';
		$i++;
		$urls[] = md5($url);
		cookie('u_c_r',$urls,false,3600*24,true);
		return $string;
	}
}
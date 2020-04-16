<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class log_instance{
	static $yibulog = '';
	static function d($tag,$content){
		static $start = 0;
		$log = '';
		if($start == 0){
			if(YIBU_LOG){
				self::$yibulog = '';
				register_shutdown_function('log_instance::save');
			}
			$h	= log::get_head();
			$log .= '<p class="t"><table><tr>'; 
			$log .= '<td>['.$h['time'].']</td>';
			$log .= '<td width="137">['.$h['ip'].']</td>';
			$log .= '<td width="48">['.$h['method'].']</td>';
			if(strlen($h['url'])>100){
				$log .= '<td title="'.$h['url'].'">['.substr($h['url'],0,100).'<font color="red"> more...</font>]</td>';
			}else{
				$log .= '<td>['.$h['url'].']'
				.($h['method'] == 'POST' && empty($_GET['a']) ? ' ['.ROUTE_A.'] ' : '')
				.'</td>';
			}
			$log .= '</tr></table></p>'."\n";
			$start = 1;	
		}
		if((is_array($content) or is_object($content))){
			$content = print_r($content,true);
		}
		if($tag !== ''){
			$tagc = strtolower(strip_tags($tag));
			$cls = '';
			if($tagc == 'fatal error' or $tagc == 'error')
				$cls = 'class="e"' ;
			$log  .= '<p '.$cls.'>[<i>'.$tag.'</i>&nbsp;'.$content.']</p>'."\n";
		}else{
			$log  .= '<p>['.$content.']</p>'."\n";
		}
		if(!is_writeable(APP_PATH.'/logs/'))
			exit('Log folder [ '.str_replace(CMS_ROOT,'',APP_PATH.'/logs/').' ] is not dir or writeable!');
		if(YIBU_LOG)
			self::$yibulog .= $log;
		else
			self::save($log,1);
	}
	static function save($log = '',$t = 0){
		$file = self::path();
		file_put_contents($file,$t?$log:self::$yibulog,FILE_APPEND);
		if(_IS_CLI){
			chmod($file,0777);
		}
		self::rollFile($file,LOG_ROLL_FILE_SIZE);
	}
	static function show(){
		include_once str_replace('\\','/',dirname(__FILE__)).'/log_file_show.php';
		log_file_show::show();
	}
	static function del(){
		if(getg('path')){
			@unlink(APP_PATH.'/logs/'.base64_decode(getg('path')));	
		}else{
			self::rollFile(self::path(),LOG_ROLL_FILE_SIZE,1);
			if(LOG_SAVED_DAYS > 0)
				self::gc();
		}
	}
	static function gc(){
		$lock = file_get_contents(APP_PATH.'/logs/gc.lock');
		if(empty($lock) || $lock < date('Ymd')){
			$deadtime = strtotime(date('Y-m-d',strtotime('-'.LOG_SAVED_DAYS.' days')).' 00:00:00');
			if($logs = glob(APP_PATH.'/logs/*.log*')){
				foreach($logs as $file){
					filemtime($file)<$deadtime && unlink($file);
				}
			}
			file_put_contents(APP_PATH.'/logs/gc.lock',date('Ymd'));
		}
	}
	static function path(){
		$dir = APP_PATH.'/logs/';
		$file = empty($_GET['path'])?date('Ymd').'_'.substr(md5(date('Ymd').'x2~^y1t!6#$'),0,8).'.log':base64_decode(getg('path'));
		return $dir.$file;
	}
	static function rollFile($file,$max,$force = 0){
		if(filesize($file) < $max * 1024 * 1024 && $force == 0) return;
		$tFile 	= $file.'.tmp';
		rename($file,$tFile);
		if($logs = glob($file.'.0*')){
			$arr = array();
			foreach($logs as $rfile){
				$arr[] = substr($rfile,strrpos($rfile,'.')+1);
			}
			$maxFile = $file.'.'.str_pad(max($arr),3,'0',STR_PAD_LEFT);
			if(filesize($maxFile) < $max * 1024 * 1024){
				file_put_contents($maxFile,file_get_contents($tFile),FILE_APPEND);
				@unlink($tFile);
				return ;
			}else{
				$rollFile =  $file.'.'.str_pad((max($arr)+1),3,'0',STR_PAD_LEFT);
			}
		}else{
			$rollFile = $file.'.001';
		}
		rename($tFile,$rollFile);
	}
}
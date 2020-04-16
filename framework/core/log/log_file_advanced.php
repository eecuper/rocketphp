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
		$iscli = empty($_SERVER['REQUEST_METHOD']);
		if($start == 0){
			if(YIBU_LOG){
				self::$yibulog = '';
				register_shutdown_function('log_instance::save');
			}
			$start= date('His').substr(stristr(microtime(true),'.'),1,3);
			$h	  = log::get_head();
			$log .= '<p class="t" id="p'.$start.'"><table cellspacing="0" cellpadding="0"><tr>'; 
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
		}
		if((is_array($content) or is_object($content))){
			$content = print_r($content,true);
		}
		if($tag !== ''){
			$tagClass = strtolower(strip_tags($tag));
			if($tagClass == 'fatal error')
				$tagClass = 'fatal';
			$log  .=
			'<p class="yeah-log-tag '.$tagClass.'" p="p'.$start.'">[<i>'
			.$tag.'</i>&nbsp;'.$content.']</p>'."\n";
		}else{
			$log .= '<p class="yeah-log-tag debug" p="p'.$start.'">['.$content.']</p>'."\n";
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
		file_put_contents($file,self::compress($t?$log:self::$yibulog),FILE_APPEND);
		if(_IS_CLI){
			chmod($file,0777);
		}
		self::rollFile($file,LOG_ROLL_FILE_SIZE);
	}
	static function show(){
		include_once str_replace('\\','/',dirname(__FILE__)).'/log_file_advanced_show.php';
		log_file_advanced_show::show();
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
		return APP_PATH.'/logs/'.date('Ymd').'_'.substr(md5(date('Ymd').'x2~^y1t!6#$'),0,8).'.log';
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
	//压缩,type=0为压缩,1为解压缩
	static function compress($str,$type = 0){
		if(!LOG_COMPRESS)return $str;
		$compress = array(
			'#^~0#'=>'<p class="t" id="p',
			'#^~1#'=>'"><table cellspacing="0" cellpadding="0"><tr><td>[',
			'#^~2#'=>']</td><td width="137">[',
			'#^~3#'=>']</td><td width="48">[',
			'#^~4#'=>']</td><td>[',
			'#^~5#'=>']</td><td title="',
			'#^~6#'=>'<font color="red"> more...</font>]</td>',
			'#^~7#'=>'</tr></table></p>'."\n",
			'#^~8#'=>'<p class="yeah-log-tag ',
			'#^~9#'=>'</i>&nbsp;',
			'#^~a#'=>']</td>',
			'#^~b#'=>']</p>'."\n",
		);
		if($type == 0){
			return 	str_replace(array_values($compress),array_keys($compress),$str);
		}else{
			return 	str_replace(array_keys($compress),array_values($compress),$str);
		}
	}
}
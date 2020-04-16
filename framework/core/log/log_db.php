<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
/*
DROP TABLE IF EXISTS `log_content`;
CREATE TABLE `log_content` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(8) NOT NULL,
  `appid` char(32) NOT NULL,
  `request_id` int(11) NOT NULL DEFAULT '0' COMMENT '请求的自增id',
  `tag` varchar(125) NOT NULL COMMENT 'log,tag',
  `tag_content` varchar(500) NOT NULL,
  `content` text NOT NULL COMMENT 'log内容',
  `delt` tinyint(1) NOT NULL DEFAULT '0', 
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `tag` (`tag`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `log_request`;
CREATE TABLE `log_request` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(8) NOT NULL,
  `time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '请求时间',
  `ip` char(15) NOT NULL COMMENT '请求ip',
  `method` char(8) NOT NULL DEFAULT '' COMMENT '请求类型:1get,2post,3request,4shell',
  `url` varchar(500) NOT NULL COMMENT '请求网址',
  PRIMARY KEY (`id`),
  KEY `requesttime` (`time`),
  KEY `requestip` (`ip`),
  KEY `requestdate` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
class log_instance{
	static function del(){
		$db 	= mysql::get_instance();
		$appId	= ' and appid = "'.md5(APP_PATH).'"';
		$date 	= getg('path')?getg('path'):date('Ymd');
		if($tag	= getg('tag')){
			$db->update('log_content','delt=1','date ='.$date.' and tag="'.$tag.'"'.$appId);
		}else{
			$db->update('log_content','delt=1','date ='.$date.$appId);
		}
		echo '<script>alert("删除成功!");top.location.href="?m=api&a=log"</script>';
	}
	static function d($tag,$content){
		static $rid = 0;
		$db = mysql::get_instance();
		$date = date('Ymd');
		if($rid == 0){
			$h	  				= log::get_head();
			$isCli				= empty($_SERVER['REQUEST_METHOD']);
			$param['date']		= $date;
			$param['time']		= $h['time'];
			$param['ip']		= $h['ip'];
			$param['method'] 	= $h['method'];
			$param['url']		= $h['url'];
			$rid = $db->add('log_request',$param);
		}
		if($tag !== ''){
			$tagc = strtolower(strip_tags($tag));
			if($tagc == 'fatal error')
				$tagc = 'fatal';
		}else{
			$tagc = 'debug';
		}
		if((is_array($content) or is_object($content))){
			$content = print_r($content,true);
		}
		$log['request_id'] 	= $rid;
		$log['date']		= $date;
		$log['appid']		= md5(APP_PATH);
		$log['tag'] 		= $tagc;
		$log['tag_content'] = $tag;
		$log['content'] 	= $content;
		$db->add('log_content',$log);
		//10%概率抽样删除
		if(mt_rand(1,1000) < 10){
			$deadtime = date('Ymd',strtotime('-'.LOG_SAVED_DAYS.' days'));
			$db->delete('log_request','date < '.$deadtime);
			$db->delete('log_content','date < '.$deadtime);
		}
	}
	static function show(){
		include_once str_replace('\\','/',dirname(__FILE__)).'/log_db_show.php';
		log_db_show::show();
	}
}
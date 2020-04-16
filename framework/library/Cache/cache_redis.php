<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
class cache_redis extends Redis{
	function __construct($option = array()){
		parent::__construct();
		$host = !empty($option['host'])?$option['host']:REDIS_HOST;
		$port = !empty($option['port'])?$option['port']:(defined('REDIS_PORT')?REDIS_PORT:6379);
		$db   = !empty($option['db'])?$option['db']:(defined('REDIS_DB')?REDIS_DB:0);
		$auth = !empty($option['auth'])?$option['auth']:(defined('REDIS_AUTH')?REDIS_AUTH:'');
		$this->connected = 1;
		try{
			$this->connect($host,$port,3);
			if($auth)
				$this->auth($auth);
			$this->select($db);
		}catch(Exception $e){
			$this->connected = 0;
			trigger_error('Redis connect error > host:'.$host.' port:'.$port,E_USER_WARNING);
		}
	}
	function __destruct(){
		if($this->connected){
            $this->close();
        }
	}
}
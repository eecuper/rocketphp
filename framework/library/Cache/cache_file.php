<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
//存储为数组序列化
class cache_file {
	var $db = '0';
	//选择数据库
	function select($db = ''){
		if($db === ''){
			error('No db selected!');	
		}else{
			$this->db = $db;
		}
	}
	//设置
	function set($key, $val, $ttl = -1){
		$option = array(
			'app'	=>	APPLICATION,
			'path'	=>	APP_PATH.'/caches/data/caches_file_db'.$this->db.'.php',
			'keys'	=>	array($key),
		);
		$contents = array(
				'time'		=> time(),
				'ttl'		=> $ttl,			
				'data'		=> serialize($val),
		);
		config::set('',$contents,$option);		
	}
	//获取key
	function get($key){
		$data = self::get_key($key);
		return $data ? unserialize($data['data']):false;
	}
	function get_key($key){
		$option = array(
			'app'	=>	APPLICATION,
			'name'	=>	$this->db,
			'path'	=>	APP_PATH.'/caches/data/caches_file_db'.$this->db.'.php',
			'keys'	=>	array($key),
		);
		if (!is_file($option['path'])){
			return FALSE;
		}
		$data = config::get('',$option);
		if($data){
			if ($data['ttl']!= -1 && time() >  $data['time'] + $data['ttl']){
				self::delete($key);
				return FALSE;
			}
			return $data;
		}
		return false;		
	}	
	//删除key
	public function del($key) {
		if(strpos($key,'/') !== false){
			$path 	= CMS_ROOT.substr($key,0,strpos($key,'/'));
			$key 	= substr($key,strpos($key,'/')+1);
		}else{
			$path = APP_PATH;
		}
		$option = array(
			'path'	=>	$path.'/caches/data/caches_file_db'.$this->db.'.php',
			'keys'	=>	array($key),
		);
		config::del('',$option);
	}
	//清空数据库
	function clean(){
		return config::del('',array(
			'path'	=>	APP_PATH.'/caches/data/caches_file_db'.$this->db.'.php',
			'keys'	=> '',
			)
		);	
	}
	
	//设置过期时间
	function expire($key,$ttl){
		if(self::get($key)){
			$option = array(
			'app'	=>	APPLICATION,
			'path'	=>	APP_PATH.'/caches/data/caches_file_db'.$this->db.'.php',
			'keys'	=>	array($key,'ttl'),
			);
			config::set('',$ttl,$option);
		}
		return false;
	}
	
	//获取过期时间
	function ttl($key){
		if($data = self::get_key($key)){
			return 	$data['ttl'] == -1?'-1':$data['time'] + $data['ttl']- time() ;		
		}
		return false;
	}
	
	//返回所有的key
	function keys($key){
		$path = APP_PATH.'/caches/data/caches_file_db'.$this->db.'.php';
		if (!is_file($path)){
			return false;	
		}
		$cache = include $path;
		$r = array();
		if($cache){
			$key = str_replace('*','.*',$key);
			$keys = array_keys($cache);
			foreach($keys as $v){
				if(preg_match('/'.$key.'/',$v)){
					$r[] = $v;	
				}
			}	
		}
		return $r;
	}
	
	//db信息
	public function dbinfo($db = '') {
		$path = APP_PATH.'/caches/data/caches_file_db'.($db?$db:$this->db).'.php';
		if (!is_file($path)){
			return false;	
		}
		$cache = include $path;
		$r['keys']   = count($cache);
		$r['dbsize'] = filesize($path);
		return $r;
	}
}
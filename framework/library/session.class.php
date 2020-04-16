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
DROP TABLE IF EXISTS `my_session`;
CREATE TABLE `my_session` (
  `session_id` char(32) NOT NULL,
  `session_data` text,
  `session_expire` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/
class session {
	private function __construct() {
        if(defined('SESSION_MODE') && SESSION_MODE == 'db'){
			session_set_save_handler(
				array(&$this,'sess_open'), array(&$this,'sess_close'), array(&$this,'sess_read'), 
				array(&$this,'sess_write'), array(&$this,'sess_destroy'), array(&$this,'sess_gc')
			);
			$this->db = T('session');
		}else{
			if(substr(SESSION_SAVEPATH,0,1) == '/'){
		        session_save_path(SESSION_SAVEPATH);
	        }else{
		        session_save_path(APP_PATH.'/'.SESSION_SAVEPATH);
	        }
			ini_set('session.gc_maxlifetime',SESSION_TIMEOUT);
		}
		session_start();
    }
	//单例模式,防止对象被重复初始化;
	static function &get_instance() {
		static $object;
		if(empty($object)) {
			$object = new self();
		}
		return $object;
	}
	//sessin执行函数
	static function act($key,$val){
		$session = self::get_instance();
		if($key != null){
			self::session_timeout_check();
		}
		if($key === null){
			return $session->destroy();	
		}elseif($val === null){
			return $session->del($key);	
		}elseif($key !== '' && $val === ''){			
			return $session->get($key);	
		}elseif($key !== '' && $val !== ''){
			return $session->set($key,$val);	
		}
	}
	//session过期检测
	static function session_timeout_check(){
		if(SESSION_TIMEOUT){
			$key = APPLICATION.'_start_time';
			if(empty($_SESSION[$key]) or time() < $_SESSION[$key] + SESSION_TIMEOUT){
				$_SESSION[$key] = time();
			}else{
				unset($_SESSION);
				session_destroy();
			}
		}
	}
	//获取session
	function get($key = ''){
		if($key === '') return NULL;
		return isset($_SESSION[APPLICATION.'_'.$key]) ? $_SESSION[APPLICATION.'_'.$key] : NULL;	
	}
	//设置session
	function set($key = '',$val = ''){
		static $i = 0;
		if($key === '' or $val === '') return FALSE;
		if($i)
			session_start();
		$_SESSION[APPLICATION.'_'.$key] = $val;
		session_write_close();
		$i = 1;
		return TRUE;
	}
	//删除session
	function del($key = ''){
		if($key === '') return FALSE;
		if(isset($_SESSION[APPLICATION.'_'.$key])){
			unset($_SESSION[APPLICATION.'_'.$key]);
			return TRUE;
		}else{
			return FALSE;
		}
	}
	//销毁所有的session
	function destroy(){
		unset($_SESSION);
		session_destroy();
		return TRUE;		
	}
	//db session
	function sess_open($savePath, $sessName){
		return true;	
	}
	function sess_read($sessID){
		$session = $this->db->select('session_id=\''.$sessID.'\' AND session_expire > '.time());
	  	return $session ? $session['session_data'] : '';
	}
	function sess_write($sessID,$sessData){
		if(empty($sessData))return FALSE;
	  	$expire = time() + SESSION_TIMEOUT;
	  	$param  = array('session_id'=>$sessID,'session_data'=>$sessData,'session_expire'=>$expire);
	  	return $this->db->replace($param) ? TRUE : FALSE;	
	}
	function sess_close(){
		if(mt_rand(0,100) < 30){
			$this->sess_gc(1);
		}
	  	return true;
	}   
	function sess_destroy($sessID){
	  	return $this->db->del('session_id=\''.$sessID.'\'') ? TRUE : FALSE;
	}
	function sess_gc($sessMaxLifeTime) { 
	 	return $this->db->del('session_expire < '.time())?TRUE : FALSE;
	}
}
<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class mysql {
	private $rw 	= 1; //读写模式,1为读,2为写
	private $link	= NULL;
	private $config	= array();
	private $trans	= false;
	const	READ 	= 1;
	const	WRITE	= 2;
	private function __construct($option = array()){
		$this->config['host'] 	 = !empty($option['host'])?$option['host']:DB_HOST;
		$this->config['user'] 	 = !empty($option['user'])?$option['user']:DB_USER;
		$this->config['password']= isset($option['password'])?$option['password']:DB_PWD;
		$this->config['db'] 	 = !empty($option['db'])?$option['db']:DB_NAME;
		$this->config['separate']= isset($option['separate'])?$option['separate']:DB_RW_SEPARATE;
	}
	static function &get_instance($option = array()) {
		static $object = array();
		$md5 = md5(serialize($option));
		if(empty($object[$md5])){
			$object[$md5] = new self($option);
		}
		return $object[$md5];
	}
	function db_connect(){
		if($this->config['separate']){
			static $splink = array();
			if($this->rw == self::WRITE && !empty($splink['write'])){
				return $this->link = $splink['write'];
			}
			if($this->rw == self::READ && !empty($splink['read'])){
				return $this->link = $splink['read'];
			}
			$db 	= explode(';',$this->config['host']);
			$user 	= explode(';',$this->config['user']);
			$pwd	= explode(';',$this->config['password']);
			if($this->rw == self::WRITE){
				$this->link = $splink['write'] = mysql_connect($db[0],$user[0],$pwd[0]);
			}else{
				$k = mt_rand(1,count($db) - 1);
				$this->link = $splink['read'] = mysql_connect($db[$k],$user[$k],$pwd[$k]);
			}
		}else{
			if($this->link)
				return;
			$this->link =  mysql_connect($this->config['host'],$this->config['user'],$this->config['password']);	
		}
		if(!$this->link){
			fatal_error('Could not connect: '.mysql_error());
		}
		//>5.0.1
		mysql_query("SET sql_mode=''",$this->link);
		mysql_query('SET NAMES '.(defined('DB_CHARSET')?DB_CHARSET:'utf8'),$this->link);
		mysql_select_db($this->config['db'],$this->link);
	}
	function get_count($table = '',$where = 1){
		if($table == '') return 0;
		$data	= $this->query('SELECT count(*) FROM `'.$table.'` WHERE '.$where.' LIMIT 1',MYSQL_NUM);
		return $data ? $data[0][0] : 0;
	}
	function get_exists($table = '' , $where = ''){
		if($table == '' or $where == '')return FALSE;
		return $this->get_count($table,$where) > 0 ? TRUE : FALSE;
	}
	function get_one($field = '' ,$table = '', $where = ''){
		if($table == '' or $field == '') return NULL;
		if($where === '') $where = 1;
		$data = $this->query('SELECT '.$field.' FROM `'.$table.'` WHERE '.$where.' LIMIT 1',MYSQL_NUM);
		return $data ? $data[0][0] : NULL;
	}
	function get_row($sql = ''){
		if($sql == '') return NULL;
		$return = NULL;
		$sql = trim($sql);
		if(preg_match('/^select/i', $sql)){
			if($data = $this->query( $sql.' LIMIT 1',MYSQL_ASSOC)){
				$v = array_values($data);
				$return = $v[0];
			}
		}
		return $return;
	}
	function get_column($sql = '',$i=0){
		$sql = trim($sql);
		if($sql == '') return NULL;
		$r 	 = NULL;
		if(preg_match('/^select/i',$sql)){
			if($result = $this->query($sql,MYSQL_ASSOC,1)){
				$type = is_int($i) ? MYSQL_NUM : MYSQL_ASSOC;
				$i	 = trim($i);
				while($data = mysql_fetch_array($result,$type)){
					if(isset($data[$i]) && $data[$i] !== '')
						$r[] = $data[$i];
				}
			}
		}
		return $r;
	}
	function get_all($sql = '',$primary = ''){
		$sql = trim($sql);
		if($sql == '') return NULL;
		$return = NULL;
		if(preg_match('/^select/i',$sql)){
			if($data = $this->query($sql,MYSQL_ASSOC,0,trim($primary)))
				$return = $data;
		}
		return $return;
	}
	function get_field($tbl = ''){
		$return = array();
		if($tbl == '') return $return;
		$result = $this->_execute_query('DESCRIBE `'.$tbl.'`');
		while ($field = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$return[] = $field;
		}
		return $return;
	}
	function add( $table = '' , $param = array(),$replace = 0 ){
		if($table == '' || !is_array( $param ) or count($param) == 0) return 0;
		$key = $values ='';
		foreach($param as $k => $v){
			if(is_string($k)){
				$tkey = trim($k);
			}else{
				$tkey = trim($v);
				$v    = getp($v);
			}
			if($v !== ''){
				$key   .= '`'.$tkey.'`,';
				$values.= '\''.trim(self::escapestring($v)).'\',';
			}
		}
		$key    = trim($key,',');
		$values = trim($values,',');
		if(empty($key)) return 0;
		$insert = $replace === 1 ? 'REPLACE' : 'INSERT'.($replace === 2?' IGNORE' :'');
		return self::query($insert.' INTO `'.$table.'` ('.$key.')VALUES('.$values.')'); 
	}
	function update($table = '' , $param , $condition = '',$ignore = 0){
		if($table == '' ||  $condition=='') return 0;
		$key = '';
		if(is_string($param) && $param != '') {
			$key = $param;
		}elseif(is_array($param) && count($param) > 0) {
			foreach($param as $k => $v){
				if(is_string($k)){
					$tkey = trim($k);
				}else{
					$tkey = trim($v);
					$v    = getp($v);
				}
				$key .='`'.$tkey.'`=\''.trim(self::escapestring($v)).'\',';
			}
			$key = trim($key,',');
		}else{
			return 0;
		}
		return self::query('UPDATE '.($ignore?'IGNORE ':'').'`'.$table.'` SET '.$key.' WHERE '.$condition);
	}
	function delete($table = '', $where = '') {
		if ($table == '' || $where == '') return 0;
		return $this->query('DELETE FROM `'.$table.'` WHERE '.$where);
	}
	private function query($sql,$type = MYSQL_ASSOC,$resource = 0,$primary = ''){
		if(preg_match('/(create|alter|drop|truncate|rename|show)\\s+(table|database|columns)/i',$sql)) return NULL;
		if(preg_match('/^select/i', $sql)){
			$this->rw = empty($this->read_from_master) ? self::READ : self::WRITE;
			$r = array();
			if($result = $this->_execute_query($sql)){
				if($resource) return $result;
				$pk = trim($primary);
				while($data = mysql_fetch_array($result,$type)){
					if($pk && isset($data[$pk])){
						$r[$data[$pk]] = $data;
					}else{
						$r[] = $data;
					}
				}
			}else{
				$r = NULL;
			}
			return $r;
		}elseif(preg_match('/^(insert|replace|update|delete)/i',$sql)){
			$this->rw = self::WRITE;
			$this->_execute_query($sql);
			if(preg_match('/^insert/i', $sql)){
				return mysql_insert_id($this->link);
			}else{
				return mysql_affected_rows($this->link);
			}
		}else{
			return NULL;
		}
	}
	private function _execute_query($sql = ''){
		$this->db_connect();
		if(defined('MYSQL_SLOW_LOG') && MYSQL_SLOW_LOG){
			$start = microtime(true);
		}
		$result = mysql_query($sql,$this->link);
		if(isset($start)){
			$elapsed_time = abs(microtime(true) - $start);
			if($elapsed_time >= (defined('MYSQL_SLOW_LOG_TIME') ? MYSQL_SLOW_LOG_TIME : 3))
				log::d('','<b style="color:#c00">MYSQL_SLOW_LOG:</b> '.number_format($elapsed_time,3).'s sql:'.$sql);
		}
		if(mysql_errno($this->link)){
			fatal_error('<b>Mysql Error:</b><br/>'.htmlspecialchars(mysql_error($this->link)).'<br/><b>SQL:</b><br/>'.htmlspecialchars($sql));	
		}
		if(defined('TRACE_SQL') && TRACE_SQL)
			log::d('',$sql);
		if(defined('TRACE_LOG') && TRACE_LOG){
			if($this->rw == self::READ){
				trace::sql($sql,1);
			}
			if($this->rw == self::WRITE){
				trace::sql('',2);
			}
		}
		return $result;
	}
	public function get_pages($sql,$pc=30,$option = array()){
		return page::get_pages($sql,$pc,$option,$this);
	}
	function escapestring($val){
		$this->db_connect();
		return mysql_real_escape_string($val,$this->link);
	}
	function begin_transaction(){
		$this->db_connect();
		if(!$this->trans){
			mysql_query('START TRANSACTION',$this->link);
			$this->trans = true;
		}
	}
	function commit(){
		if($this->trans){
			mysql_query('COMMIT',$this->link);	
			$this->trans = false;
		}
	}
	function rollback(){
		if($this->trans){
			mysql_query('ROLLBACK',$this->link);
			$this->trans = false;
		}
	}
}
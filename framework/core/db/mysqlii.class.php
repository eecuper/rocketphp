<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class mysqlii {
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
				$this->link = $splink['write'] = mysqli_connect($db[0],$user[0],$pwd[0],$this->config['db']);
			}else{
				$k = mt_rand(1,count($db) - 1);
				$this->link = $splink['read'] = mysqli_connect($db[$k],$user[$k],$pwd[$k],$this->config['db']);
			}
		}else{
			if($this->link)
				return;
			$this->link =  mysqli_connect($this->config['host'],$this->config['user'],$this->config['password'],$this->config['db']);	
		}
		if(!$this->link){
			fatal_error('Could not connect: '.mysqli_connect_error());
		}
		// > '5.0.1'
		mysqli_query($this->link,"SET sql_mode=''");
		mysqli_query($this->link,'SET NAMES '.(defined('DB_CHARSET')?DB_CHARSET:'utf8'));
	}
	function get_count($table = '',$where = 1){
		if($table == '') return 0;
		$st	= self::query('SELECT count(*) cnt FROM `'.$table.'` WHERE '.$where.' LIMIT 1');
		$r  = $st->fetch_assoc();
		$st->free_result();
		return $r['cnt'];
	}
	function get_exists($table = '' , $where = ''){
		if($table == '' or $where == '')return FALSE;
		return self::get_count($table,$where) > 0 ? TRUE : FALSE;
	}
	function get_one($field = '' ,$table = '', $where = ''){
		if($table == '' or $field == '') return NULL;
		if($where === '') $where = 1;
		$st = self::query('SELECT '.$field.' FROM `'.$table.'` WHERE '.$where.' LIMIT 1');
		$r  = $st->fetch_assoc();
		$st->free_result();
		return $r[trim($field)];
	}
	function get_row($sql = ''){
		$sql = trim($sql);
		if($sql == '') return NULL;
		$r		= NULL;
		if(preg_match('/^select/i', $sql)){
			$st = self::query($sql.' LIMIT 1');
			$r  = $st->fetch_assoc();
			$st->free_result();
		}
		return $r;
	}
	function get_column($sql = '',$i=0){
		$sql 	= trim($sql);
		if($sql == '') return NULL;
		$r		= NULL;
		if(preg_match('/^select/i',$sql)){
			$type 	= is_int($i) ? MYSQLI_NUM : MYSQLI_ASSOC;
			$i 		= trim($i);
			$st = self::query($sql);
			while($data = $st->fetch_array($type)){
				if(isset($data[$i]) && $data[$i] !== '')
					$r[] = $data[$i];
			}
			$st->free_result();
		}
		return $r;
	}
	function get_all($sql = '',$primary = ''){
		$sql 	= trim($sql);
		if($sql == '') return NULL;
		$pk 	= trim($primary);
		$r		= NULL;
		if(preg_match('/^select/i',$sql)){
			$st = self::query($sql);
			while($data = $st->fetch_assoc()){
				if($pk && isset($data[$pk])){
					$r[$data[$pk]] = $data;
				}else{
					$r[] = $data;
				}
			}
			$st->free_result();
		}
		return $r;
	}
	function get_field($tbl = ''){
		$return = array();
		if($tbl == '') return $return;
		$result = self::_execute_query('DESCRIBE  `'.$tbl.'`');
		while($field = $result->fetch_array(MYSQLI_ASSOC)){
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
		return self::query($insert.' INTO `'.$table.'` ('.$key.')VALUES('.$values.')',1); 
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
		return self::query('UPDATE '.($ignore?'IGNORE ':'').'`'.$table.'` SET '.$key.' WHERE '.$condition,2);
	}
	function delete($table = '', $where = '') {
		if ($table == '' || $where == '') return 0;
		return self::query('DELETE FROM `'.$table.'` WHERE '.$where,2);
	}
	private function query($sql,$type = 0){
		if(preg_match('/(create|alter|drop|truncate|rename|show)\\s+(table|database|columns)/i',$sql)) return NULL;
		if($type == 0){
			$this->rw = empty($this->read_from_master) ? self::READ : self::WRITE;
			return $this->_execute_query($sql);
		}else{
			$this->rw = self::WRITE;
			$this->_execute_query($sql);
			return $type == 1 ? mysqli_insert_id($this->link) : mysqli_affected_rows($this->link);
		}
	}
	private function _execute_query($sql = ''){
		$this->db_connect();
		if(defined('MYSQL_SLOW_LOG') && MYSQL_SLOW_LOG){
			$start = microtime(true);
		}
		$result = mysqli_query($this->link,$sql);
		if(isset($start)){
			$elapsed_time = abs(microtime(true) - $start);
			if($elapsed_time >= (defined('MYSQL_SLOW_LOG_TIME') ? MYSQL_SLOW_LOG_TIME : 3))
				log::d('','<b style="color:#c00">MYSQL_SLOW_LOG:</b> '.number_format($elapsed_time,3).'s sql:'.$sql);
		}
		if(mysqli_errno($this->link)){
			fatal_error('<b>Mysqli Error:</b><br/>'.htmlspecialchars(mysqli_error($this->link)).'<br/><b>SQL:</b><br/>'.htmlspecialchars($sql));	
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
		return mysqli_real_escape_string($this->link,$val);
	}
	function begin_transaction(){
		$this->db_connect();
		if(!$this->trans){
			$this->link->autocommit(false);
			$this->trans = true;
		}
	}
	function commit(){
		if($this->trans){
			$this->link->commit();
			$this->link->autocommit(true);
			$this->trans = false;
		}
	}
	function rollback(){
		if($this->trans){
			$this->link->rollback();
			$this->link->autocommit(true);
			$this->trans = false;
		}
	}
	function __destruct(){
		if($this->link){
			mysqli_close($this->link);
		}
	}
}
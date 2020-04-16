<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class pdoo {
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
		$this->db_type 			 = defined('DB_TYPE') ? DB_TYPE : 'mysql';
		$this->limit			 = $this->db_type == 'mysql' ? ' LIMIT 1' :'';
	}
	static function &get_instance($option = array()) {
		static $object = array();
		$md5 = md5(serialize($option));
		if(empty($object[$md5])){
			$object[$md5] = new self($option);
		}
		return $object[$md5];
	}
	private function db_connect(){
		try{
			$pconnect = defined('PCONNECT') ? array(PDO::ATTR_PERSISTENT => true): array();
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
					$this->link = $splink['write'] = new PDO($this->db_type.':host='.$db[0].';dbname='.$this->config['db'],$user[0],$pwd[0],$pconnect);
				}else{
					$k = mt_rand(1,count($db) - 1);
					$this->link = $splink['read'] = new PDO($this->db_type.':host='.$db[0].';dbname='.$this->config['db'],$user[0],$pwd[0],$pconnect);
				}
			}else{
				if($this->link)
					return;
				$this->link = new PDO($this->db_type.':host='.$this->config['host'].';dbname='.$this->config['db'],
							  $this->config['user'],$this->config['password'],$pconnect);
			}
		}catch(PDOException $e){
			  fatal_error( "Connect error: ".$e->getMessage());
		}
		$this->link->exec("SET sql_mode=''");
		$this->link->exec('SET NAMES '.(defined('DB_CHARSET')?DB_CHARSET:'utf8'));
	}
	function get_count($table = '',$where = 1){
		if($table == '') return 0;
		$st	= $this->query('SELECT count(*) cnt FROM `'.$table.'` WHERE '.$where.$this->limit);
		$r  = $st->fetch();
		return $r['cnt'];
	}
	function get_exists($table = '' , $where = ''){
		if($table == '' || $where == '')return FALSE;
		return $this->get_count($table,$where) > 0 ? TRUE : FALSE;
	}
	function get_one($field = '' ,$table = '', $where = ''){
		if($table == '' || $field == '') return NULL;
		if($where === '') $where = 1;
		$st = $this->query('SELECT '.$field.' FROM `'.$table.'` WHERE '.$where.$this->limit);
		$r  = $st->fetch();
		return $r[trim($field)];
	}
	function get_row($sql = ''){
		$sql = trim($sql);
		if($sql == '') return NULL;
		if(preg_match('/^select/i', $sql)){
			$st = $this->query($sql.$this->limit);
			$r = $st->fetch();
		}
		return !empty($r) ? $r : NULL;
	}
	function get_column($sql = '',$i=0){
		$sql = trim($sql);
		if($sql == '') return NULL;
		if(preg_match('/^select/i',$sql)){
			if($st = $this->query($sql)){
				$type = is_int($i) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
				$i 	  = trim($i);
				while($data = $st->fetch($type)){
					if(isset($data[$i]) && $data[$i] !== '')
						$r[] = $data[$i];
				}
			}
		}
		return !empty($r) ? $r : NULL;
	}
	function get_all($sql = '',$primary = ''){
		$sql 	= trim($sql);
		if($sql == '') return NULL;
		if(preg_match('/^select/i',$sql)){
			if($st = $this->query($sql)){
				$pk 		= trim($primary);
				while($rs = $st->fetch()){
					if($pk && isset($rs[$pk])){
						$r[$rs[$pk]] = $rs;
					}else{
						$r[] = $rs;
					}
				}
			}
		}
		return !empty($r) ? $r : NULL;
	}
	function get_field($tbl = ''){
		$return = array();
		if($tbl == '') return $return;
		$result = self::_execute_query('DESCRIBE  `'.$tbl.'`');
		while($field = $result->fetch(PDO::FETCH_ASSOC)){
			$return[] = $field;
		}
		return $return;
	}
	function add($table = '',$param = array(),$replace = 0){
		if($table == '' or !is_array( $param ) or count($param) == 0) return 0;
		$key = $values ='';
		$val = array();
		if(is_array($param) && count($param) > 0) {
			foreach($param as $k => $v){
				$values.= '?,';
				if(is_string($k)){
					$tkey	= trim($k);
				}else{
					$tkey  	= trim($v);
					$v 		= getp($v);
				}
				if($v !== ''){
					$key	.= '`'.$tkey.'`,';
					$val[]  = trim($v);
				}
			}
			$key    = trim($key,',');
			$values = trim($values,',');
		}
		if(empty($key)) return 0;
		$insert = $replace === 1 ? 'REPLACE' : 'INSERT'.($replace === 2?' IGNORE' :'');
		$sql = $insert.' INTO `'.$table.'` ('.$key.')VALUES('.$values.')';
		return $this->query($sql,$val); 
	}
	function update($table = '' , $param , $condition = '',$ignore = 0){
		if($table == '' or  $condition=='') return 0;
		$key = '';
		$val = array();		
		if(is_string($param) && $param != '') {
			$key = $param;
			$val = '__EXEC__';
		}elseif(is_array($param) && count($param) > 0) {
			foreach($param as $k => $v){
				if(is_string($k)){
					$tkey = trim($k);
				}else{
					$tkey = trim($v);
					$v 	  = getp($v);
				}
				$key .='`'.$tkey.'`=?,';
				$val[] = trim($v);
			}
			$key = trim($key,',');
		}else{
			return 0;
		}
		$sql = 'UPDATE '.($ignore?'IGNORE ':'').'`'.$table.'` SET '.$key.' WHERE '.$condition;
		return $this->query($sql,$val); 
	}
	function delete($table = '', $where = '') {
		if ($table == '' || $where == '') return 0;
		return $this->query('DELETE FROM `'.$table.'` WHERE '.$where,'__EXEC__');
	}
	private function query($sql,$type = ''){
		if(preg_match('/(create|alter|drop|truncate|rename|show)\\s+(table|database|columns)/i',$sql)) return NULL;
		if(preg_match('/^select/i', $sql)){
			$this->rw = empty($this->read_from_master) ? self::READ : self::WRITE;
			$result = $this->_execute_query($sql);
			$result->setFetchMode(PDO::FETCH_ASSOC);
			return $result;
		}elseif(preg_match('/^(insert|replace|update|delete)/i',$sql)){
			$this->rw = self::WRITE;
			$result = $this->_execute_query($sql,$type);
			if(preg_match('/^insert/i', $sql)){
				return $this->link->lastInsertId();
			}else{
				return $result;
			}
		}
	}
	private function _execute_query($sql = '',$val = array()){
		$this->db_connect();
		if(defined('MYSQL_SLOW_LOG') && MYSQL_SLOW_LOG){
			$start = microtime(true);
		}
		if($this->rw == self::READ){
			$result = $this->link->query($sql);
			$err 	= $this->link->errorInfo();
		}elseif($this->rw == self::WRITE){
			if(is_array($val) && !empty($val)){
				$stmt 	= $this->link->prepare($sql);
				$result = $stmt->execute($val);
				$err 	= $stmt->errorInfo();
			}elseif($val === '__EXEC__'){
				$result = $this->link->exec($sql);
				$err 	= $this->link->errorInfo();
			}
		}
		if($err[0] !== '00000' && $err[0] !== '01000'){
			fatal_error('<b>PDO Error:</b><br/>'.htmlspecialchars($err[2]).'<br/><b>SQL:</b><br/>'.htmlspecialchars($sql));
		}
		if(isset($start)){
			$elapsed_time = abs(microtime(true) - $start);
			if($elapsed_time >= (defined('MYSQL_SLOW_LOG_TIME') ? MYSQL_SLOW_LOG_TIME : 3))
				log::d('','<b style="color:#c00">MYSQL_SLOW_LOG:</b> '.number_format($elapsed_time,3).'s sql:'.$sql);
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
	function begin_transaction(){
		$this->db_connect();
		if(!$this->trans){
			$this->link->beginTransaction();
			$this->trans = true;
		}
	}
	function commit(){
		if($this->trans){
			$this->link->commit();	
			$this->trans = false;
		}
	}
	function rollback(){
		if($this->trans){
			$this->link->rollBack();
			$this->trans = false;
		}
	}
}
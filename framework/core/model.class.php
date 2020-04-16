<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class model {
	protected $item 	= array();
	protected $item_pre = 
	array('order'=>'ORDER BY','limit'=>'LIMIT','group'=>'GROUP BY','having'=>'HAVING','leftjoin'=>'LEFT JOIN','innerjoin'=>'INNER JOIN','field'=>'','where'=>'','primary'=>'');
    function __construct($option = array()){
		$this->db = DB_DRIVER == 'mysqli' ? mysqlii::get_instance($option) :
		(DB_DRIVER == 'pdo' ? pdoo::get_instance($option) : mysql::get_instance($option));
		$this->prefix = !empty($option['prefix'])?$option['prefix']:DB_PREFIX;
    }
	function __call($name,$argus){
		$name = strtolower($name);
		if(in_array($name,array_keys($this->item_pre))){
			$pre  = ' '.$this->item_pre[$name].' ';
			if($name == 'limit'){
				if(preg_match('/^\d+$/',$argus[0])){
					$this->item['limit'] = $pre.(empty($argus[1]) ? '0,'.$argus[0] : $argus[0].','.$argus[1]);
				}else{
					$this->item['limit'] = $pre.$argus[0];
				}
			}elseif($name == 'leftjoin' || $name == 'innerjoin'){
				$join_str = $pre.self::tbl_name($argus[0]).' ON '.$argus[1];
				if(empty($this->item['join']))
					$this->item['join']	= $join_str; 
				else
					$this->item['join']	.= $join_str;
			}else{
				$this->item[$name] = $name == 'where'?
				$this->_prase_where($argus[0]):($name == 'field' ? $argus[0] :(!empty($argus[0])? $pre.$argus[0] : ''));
			}
			return $this;
		}else{
			fatal_error('Undefined method of model: '.$name);
		}
	}
	function tbl_name($name){
		return (empty($this->prefix) || preg_match("/^{$this->prefix}_/",$name) || strpos($name,'.') !== false) ? $name : $this->prefix.'_'.$name;
	}
	function tbl($name){
		if(!empty($name) && $name !== '___blank___'){
			$this->tbl = self::tbl_name($name);
			return $this;
		}else{
			return $this->db;
		}
	}
	function count($where = 1){
		$w = $this->get_item('where');
		return $this->db->get_count($this->tbl,$w ? $w : $where);
	}
	function exists($where = ''){
		return ($where = $this->get_where($where)) ? $this->db->get_exists($this->tbl, $where):false;
	}
	function find($field = '*'){
		$this->field($field);
		$data   = $this->select($this->get_item('where'));
		if($data){
			if(count($data) == 1){
				$vals = array_values($data);
				return $vals[0];
			}else{
				return $data;
			}
		}
		return NULL;
	}
	function select($where = ''){
		$where = $this->get_where($where);
		return $this->db->get_row('select '.$this->get_item('field').' from '.$this->tbl.' '.$this->get_item('join').' '.($where ? ' WHERE '.$where : '').$this->get_item('order'));
	}
	function _get_sql($where = '',$nolimit = 0){
		$where = $this->get_where($where);
		$where = ($where?' WHERE '.$where:'').$this->get_item('group').$this->get_item('having').$this->get_item('order');
		return 'select '.$this->get_item('field').' from '.$this->tbl.' '.$this->get_item('join').' '.$where.($nolimit?'':$this->get_item('limit'));
	}
	function all($where = ''){
		$data = $this->db->get_all(self::_get_sql($where),$this->get_item('primary'));
		return $data?$data:array();
	}
	function column($index = 0,$where=''){
		return $this->db->get_column(self::_get_sql($where),$index);
	}
	function pages($where = '',$psize = 30,$option = array()){
		if(empty($option['primary'])){
			$option['primary'] = $this->get_item('primary');
		}
		return $this->db->get_pages(self::_get_sql($where,1),$psize,$option);
	}
	function add($param){
		return $this->db->add($this->tbl,$param);
	}
	function replace($param){
		return $this->db->add($this->tbl,$param,1);
	}
	function add_ignore($param){
		return $this->db->add($this->tbl,$param,2);
	}
	function update($param,$where = '',$ignore = 0){
		if($where = $this->get_where($where)){
			return $this->db->update($this->tbl,$param ,$where,$ignore);
		}else{
			return false;
		}
	}
	function add_uniq($param,$unique){
		$arr = explode(',',$unique);
		if($arr){
			$where = '';
			$d = '';
			foreach($arr as $k){
				$k = trim($k);
				if(isset($param[$k])){
					$where .= $d.'`'.$k.'`="'.$param[$k].'"';
					$d = ' and ';
				}
			}
			return self::add_update($param,'',$where);
		}
		return false;
	}
	function add_update($a_param,$u_param,$where = ''){
		if($where = $this->get_where($where)){
			if($this->db->get_exists($this->tbl,$where)){
				return $u_param?$this->db->update($this->tbl,$u_param,$where):false;
			}else{
				return $this->db->add($this->tbl,$a_param);
			}
		}else{
			return false;
		}
	}
	function update_ignore($param,$where = ''){
		return self::update($param,$where,1);
	}
	function delete($where = ''){
		return self::del($where);
	}
	function del($where = ''){
		if($where = $this->get_where($where)){
			return $this->db->delete($this->tbl,$where);
		}else{
			return false;
		}
	}
	function get_item($name){
		if(!empty($this->item[$name])){
			$r = $this->item[$name];
			unset($this->item[$name]);
			return $r;
		}else{
			return $name == 'field' ? self::get_table_fileld() : '';
		}
	}
	function get_where($where = ''){
		$w = $this->get_item('where');
		return $where !== '' ? $this->_prase_where($where):($w?$w:'');
	}
	function _prase_where($where = ''){
		if(preg_match('/^(-)?\d+$/',$where)){
			return '`'.self::get_table_primary().'`= '.intval($where).' ';
		}else if(empty($where)){
			trigger_error("where is empty!");
			return '';
		}else{
			return $where;
		}
	}
	
	//表信息缓存
	function get_table_fileld(){
		if(defined('DB_FIELD_CACHE') && DB_FIELD_CACHE){
			$tblinfo = self::get_table_info();
			if(!empty($tblinfo['fields'])){
				return $tblinfo['fields'];
			}
		}
		return '*';
	}

	function get_table_primary(){
		if(defined('DB_FIELD_CACHE') && DB_FIELD_CACHE){
			$tblinfo = self::get_table_info();
			if(!empty($tblinfo['primary'])){
				return $tblinfo['primary'];
			}
		}
		return 'id';
	}

	function get_table_info(){
		static $tblinfos = array();
		$key = DB_NAME.'|'.$this->tbl;
		if(empty($tblinfos[$key])){
			$file = self::get_table_file($this->tbl);
			if(is_file($file)){
				$tblinfos[$key] = include $file;
			}else{
				$tblinfos[$key] = self::set_table_info($this->tbl);
			}
		}
		return $tblinfos[$key];
	}

	function get_table_file($table){
		$dir = SYS_ROOT.'config/dbcache/'.DB_NAME;
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
		return $dir.'/'.$table.'.php';
	}

	function update_all_tables_info(){
		$tables = glob(SYS_ROOT.'config/dbcache/'.DB_NAME.'/*');
		if($tables){
			foreach($tables as $file){
				$table = substr(basename($file), 0, -4);
				self::set_table_info($table);
			}
		}
	}

	function set_table_info($table = ''){
		$table  = $table == '' ? $this->tbl : $table;
		$fields = $this->db->get_field($table);
		if(empty($fields)){
			return;
		}
		$fieldstr = '';
		$primary  = '';
		foreach($fields as $f){
			$fieldstr .= ($fieldstr ? ',' : '').'`'.$f['Field'].'`';
			if($f['Key'] == 'PRI'){
				$primary = $f['Field'];
			}
		}
		$tblinfo = array(
			'fields'  => $fieldstr,
			'primary' => $primary
		);
		file_put_contents(self::get_table_file($table), '<?php'."\n".'return '.var_export($tblinfo, true).';');
		return $tblinfo;
	}

	//model自动保存
	function save($param,$extparam = array(), $option = array()){
		$trace 	= debug_backtrace();
		$obj 	= $trace[1]['object'];
		unset($trace);
		$option['pk'] = trim($this->get_item('primary'));
		$pk 		= empty($option['pk'])?self::get_table_primary():$option['pk'];
		$id 		= getd($pk);
		if(submit_check()){
			if(empty($param)) fatal_error('Model save param error!');
			if(!self::check_tooken()){
				jsout('alert("请勿重复提交!")');
			}
			if($id){
				$r = $this->where($pk.'='.$id)->update($param);
			}else{
				if(!empty($extparam)){
					$param = array_merge($param,(array)$extparam);
				}
				$r = $this->add($param);
			}
			if($r){
				if(empty($option['success'])){
					jsout('top.save_ok();');
				}else{
					jsout($option['success']);
				}
			}else{
				jsout('alert("操作失败")');
			}
		}
		$obj->assign('data',$id?$this->select($pk.'='.$id):NULL);
		self::build_tooken();
		$obj->display(empty($option['template'])?'':$option['template']);
	}
	function build_tooken(){
		cookie('f_s_c',(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://')
		.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	}
	function check_tooken(){
		$tooken = cookie('f_s_c');
		cookie('f_s_c',null);
		if(!empty($_SERVER['HTTP_REFERER']) &&  $tooken == $_SERVER['HTTP_REFERER'] ){
			return true;
		}else{
			return false;
		}
	}
}
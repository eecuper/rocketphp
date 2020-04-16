<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class controller{
	private $tpl                   = '';
	private $reponse_json_unencode = false;

	function __construct(){
		if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)){
			//csfr_check();
		}
		if(method_exists($this, 'init')){
			$this->init();
		}
	}

	function __get($name){
		if($name == 'view'){
			$this->view = new view;
			return $this->view;
		}
	}

	function __call($name, $argus){
		fatal_error('Method ['.$name.'] does not exist!', false);
	}

	function assign($k = '', $v = ''){
		$this->view->assign($k, $v);
	}

	function create(){
		$this->view->create(self::parse_template());
	}

	//主要支持3中调用方式:
	//本类内调用
	//Model内调用,save
	//跨类调用C
	function display($tpl = ''){
		$this->view->display(self::parse_template($tpl));
	}

	function get_content($tpl = ''){
		return $this->view->get_content(self::parse_template($tpl));
	}

	//ajax response
	function set_response_unencode(){
		$this->reponse_json_unencode = true;
	}

	function response_success($data = array(), $msg = ''){
		self::response(1, $msg, $data);
	}

	function response_error($msg = ''){
		self::response(0);
	}

	function response($code, $msg = '', $data = array()){
		$resp['code'] = intval($code);
		$resp['msg']  = (string)$msg;
		$resp['data'] = $data;
		exit(self::json_encode($resp));
	}

	private function json_encode($array){
		if($this->reponse_json_unencode){
			if(defined('JSON_UNESCAPED_UNICODE')){
				return json_encode($array, JSON_UNESCAPED_UNICODE);
			}else{
				$str = json_encode($array);
				$str = preg_replace_callback("#\\\u([0-9a-f]{4})#i", function($matchs){
					return mb_convert_encoding(pack('H*', $matchs[1]), 'UTF-8', 'UTF-16');
				}, $str);
				return $str;
			}
		}else{
			return json_encode($array);
		}
	}

	private function parse_template($tpl = ''){
		$tpl_mode = defined('TPL_MODE') && in_array(TPL_MODE, array(1, 2, 3)) ? TPL_MODE : 3;
		if($tpl == ''){
			if($this->tpl == ''){
				if(defined('DEBUG_BACKTRACE_IGNORE_ARGS')){
					$back = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
				}else{
					$back = debug_backtrace();
				}
				foreach($back as $obj){
					if(strpos($obj['function'], 'on_') === 0){
						break;
					}
				}
				$action = substr($obj['function'], 3);
				if($back[1]['function'] == 'display'){
					$this->assign('controller_id', empty($this->controller_id) ? $obj['class'] : $this->controller_id);
					$this->assign('action_id', empty($this->action_id) ? $action : $this->action_id);
				}
				unset($back);
				$dilimiter = $tpl_mode == 3
					? '/'
					: ($tpl_mode == 2
						? '/'.$obj['class'].'_'
						: '_'
					);
				$this->tpl = $obj['class'].$dilimiter.$action;
			}
			return $this->tpl;
		}else{
			if($tpl_mode > 1 && strpos($tpl, '/') === false){
				$tpl = get_called_class().'/'.$tpl;
			}
			return $tpl;
		}
	}
}
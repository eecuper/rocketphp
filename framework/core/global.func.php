<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
function getg($k,$default = ''){
	$r = $default;
	if(isset($_GET[$k]) && $_GET[$k] !== ''){
		if(preg_match('/^\d+$/',$_GET[$k]))return $_GET[$k];
		$getfilter  =
		"'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|union.+?"
		."select|update.+?set|insert\\s+into.+?values|(select|delete).+?from|"
		."(create|alter|drop|truncate)\\s+(table|database)" ;
		if(!preg_match('/'.$getfilter.'/',strtolower($_GET[$k]))){
			$v = trim(htmlspecialchars($_GET[$k]));
			$r = MAGIC_QUOTES_GPC ? $v : addslashes($v);
		}
	}
	return $r;
}
function getp($k,$default = ''){
	$r = $default;
	if(isset($_POST[$k]) && $_POST[$k] !== ''){
		if(!is_array($_POST[$k]) && preg_match('/^\d+$/',$_POST[$k]))return $_POST[$k];
		$postfilter  =
		"\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|"
		."<\\s*script\\b|\\bexec\\b|union.+?select|update.+?"
		."set|insert\\s+into.+?values|(select|delete).+?from|"
		."(create|alter|drop|truncate)\\s+(table|database)" ;
		if(!attact_check($_POST[$k],$postfilter)){
			$v = MAGIC_QUOTES_GPC?strip_slashes($_POST[$k]):$_POST[$k];
			$r = html_special_chars($v);
		}
	}
	return $r;
}
function getd($k,$default = 0){
	return (isset($_GET[$k]) && $_GET[$k] !== '' ) ? intval($_GET[$k]) : $default;
}
function getpd($k){
	return (isset($_POST[$k]) && $_POST[$k] !== '' ) ? intval($_POST[$k]) : 0;
}
function getps($k){
	return addslashes(getp($k));
}
function strip_slashes($string) {
	if (!is_array($string)) {
		return stripslashes($string);
	}
	foreach ($string as $key => $value) {
		$string[$key] = strip_slashes($value);
	}
	return $string;
}
function html_special_chars($string) {
	if(!is_array($string)) {
		return htmlspecialchars(trim($string));
	}
	foreach($string as $key => $val){
		$string[$key] = html_special_chars($val);
	}
	return $string;
}
function attact_check($val,$filter){
	if(is_array($val)) {
		foreach($val as $v){
			attact_check($v,$filter);
		}
	}else{
		if(preg_match('/'.$filter.'/',strtolower($val))){
			return true;
		}
	}
}
function csfr_check(){
	if(empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])){
		return ;
	}else{
		error('CSFR check error!');
		exit('Access denied c!');
	}
}
function submit_check($key = 'do_submit'){
	//同域检查
	if(getp($key) && preg_match('#^https?://'.$_SERVER['HTTP_HOST'].'#',$_SERVER['HTTP_REFERER'])){
		unset($_POST[$key]);
		return true;
	}else{
		return false;
	}
}
function set_back() {
	return '&back='.urlencode(base64_encode(get_url()));
}
function get_back() {
	if(getg('back')){
		$back = base64_decode(urldecode(getg('back')));
	}else{
		$back='javascript:history.back();';
	}
	return $back;
}
function js($str){
	return '<script>'.$str.'</script>';
}
function jsout($str){
	exit('<script>'.$str.'</script>');
}
function ajax_success($data = '',$info = '操作成功'){
	string::ajax_out(1,$info,$data);
}
function ajax_fail($info = '操作失败!',$data = ''){
	string::ajax_out(0,$info,$data);
}
function get_json($str,$cn_encode = true){
	return string::get_json($str,$cn_encode);
}
function get_url() {
	return (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://')
	.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}
function msg( $m , $refer = '', $t = 2){
	message::msg( $m , $refer , $t);
}
function session($key = '',$val = ''){
	return session::act($key,$val);
}
function cookie($key = '', $val = '',$unencode = false,$expire = 31536000,$httponly = false){
	return cookie::act($key, $val,$unencode,$expire,$httponly);
}
function redirect($url){
	header('Location:'.$url);
	exit;
}
function cut_str($string, $length, $dot = '...') {
	$slice = mb_substr($string, 0, $length,'utf-8');
	return $string == $slice ? $string : $slice.$dot;
}
function load_sys_js($file,$version = ''){
	return html::load_sys_js($file,$version);
}
function load_js($file,$version = ''){
	return html::load_js($file,$version);
}
function load_css($file,$version = ''){
	return html::load_css($file,$version);
}
function check_ajax($method = 'POST'){
	csfr_check();
	if($_SERVER['REQUEST_METHOD'] != $method or isset($_GET['callback']) or !isset($_SERVER['HTTP_X_REQUESTED_WITH'])or !strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
		error('AJAX check error!');
		exit('Access denied a!');
	}
}
function sys_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0){
	if(empty($key))$key = AUTH_KEY;
	return security::sys_auth($string, $operation, $key, $expiry);
}
function logd(){
	if(DEBUG_LOG){
		$num  = func_num_args();
		if($num == 1){
			$var1 = func_get_arg(0);
			log::d('',$var1);
		}
		if($num == 2){
			$var1 = (string)func_get_arg(0);
			$var2 = func_get_arg(1);
			log::d($var1,$var2);
		}
		if($num > 2){
			for($i = 0;$i < $num;$i++){
				$var = func_get_arg($i);
				log::d('',$var);
			}
		}
	}
}
function error($error = ''){
	if(ERROR_LOG){
		if(defined('DEBUG_BACKTRACE_IGNORE_ARGS')){
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
		}else{
			$trace = debug_backtrace();
		}
		log::d('<b>Error</b>',htmlspecialchars($error).'<br/>in '.$trace[0]['file'].' on line '.$trace[0]['line']);
	}
}
function fatal_error($error = '',$tracesw = true){
	if(ERROR_LOG){
		$trace = debug_backtrace();
		$trace_str = '';
		if($tracesw){
			$trace_str = '<br/>Trace:';
			foreach($trace as $k=>$v){
				$argstr = '';
				$car = '';
				foreach($v['args'] as $arg){
					if(is_array($arg)){
						$argstr .= $car.'Array';
					}else if(is_object($arg)){
						$argstr .= $car.'Object';
					}else{
						$aas = strip_tags($arg);
						if(strlen($aas)>20){
							$argstr .= $car.'\'<span>'.substr($aas,0,20).'...'.'\'</span>';
						}else{
							$argstr .= $car.'\''.$aas.'\'';
						}
					}
					$car = ',';
				}
				$trace_str .= '<br/>#'.$k.' '.
				(isset($v['file'])?($v['file'].'('.$v['line'].'): '):'[internal function]: ').
				(isset($v['class'])?($v['class'].$v['type']):'').
				$v['function'].'('.$argstr.')';
			}
		}
		log::d('<b>Fatal error</b>',mb_convert_encoding($error,'utf8','gb2312').'<br/>in '. $trace[0]['file'].' on line '.$trace[0]['line'].$trace_str);
		header('HTTP/1.1 500 Internal Server Error',TRUE,500);
		exit(ENV == 'dev' ? '<font color="red">500 Internal Server Error!</font>' : '');
	}
}
function template($filename){
	return view::template_cache($filename);
}
function load_app_class($name = '',$instance = false){
	$items = _parse_name($name);
	if(!class_exists($items['item'],false))
		require_cache(CMS_ROOT.$items['app'].'/lib/'.$items['item'].'.php');
	if($instance) return new $items['item'];
}
function load_app_func($name){
	$items = _parse_name($name);
	require_cache(CMS_ROOT.$items['app'].'/lib/'.$items['item'].'.func.php');
}
function load_common_func($name){
	$items = _parse_name($name);
	require_cache(CMS_ROOT.'common/lib/'.$items['item'].'.func.php');
}
function load_sys_func($name){
	require_cache(SYS_ROOT.'library/helper/'.$name.'.func.php');
}
function _parse_name($name){
	$name = str_replace('\\','/',$name);
	if(strpos($name,'://') !== false){
		$parse 	= explode('://',$name);
		return 	array('app'=>$parse[0],'item'=>$parse[1]);
	}else{
		return array('app'=>APPLICATION,'item'=>$name);
	}
}
function require_cache($filename) {
    static $_import_files = array();
    if (!isset($_import_files[$filename])) {
        if (is_file($filename)) {
            include $filename;
            $_import_files[$filename] = 1;
        } else {
            fatal_error($filename.' does not exist!');
        }
    }
}
function D($time = 0,$type = 1,$font = '-'){
	return date::format_time($time,$type,$font);
}
function T($tbl = '',$option = array()){
	static $_model = array();
	$tbl = empty($tbl) ? '___blank___' : $tbl;
	if(empty($_model[$tbl])){
		$m = new model($option);
		$_model[$tbl] = $m->tbl($tbl);
	}
	return $_model[$tbl];
}
function M($name){
	static $_models = array();
	$items      = _parse_name($name);
	$modelclass = (strpos($items['item'], '/') !== false ? substr(strrchr($items['item'], '/'), 1) : $items['item']).'_model';
	if(!isset($_models[$modelclass])){
		$dir_file = str_replace('_', '/', $items['item']).'_model.php';
		$file     = CMS_ROOT.$items['app'].'/models/'.$dir_file;
		if(is_file($file)){
			include $file;
		}else{
			$ok = include CMS_ROOT.'common/models/'.$dir_file;
			if(!$ok){
				fatal_error('Model '.$items['item'].' load error!');
			}
		}
		$_models[$modelclass] = new $modelclass();
	}
	return $_models[$modelclass];
}
function C($name){
	static $contorls = array();
	if(!isset($contorls[$name])){
		$items = _parse_name($name);
		if(!class_exists($items['item'],false))
			require_cache(CMS_ROOT.$items['app'].'/controls/'.$items['item'].'.php');
		$contorls[$name] = new $items['item'];
	}
	return $contorls[$name];
}
function E($msg){
	if(!empty($msg)){
		if(!is_string($msg)){
			$msg = print_r($msg,true);
		}
		throw new Exception($msg);
	}
}
function RPC($service = ''){
	static $rpc_client = null;
	if($rpc_client == null){
		$rpc_client = new rpc();
	}
	if($service != ''){
		$rpc_client->set_service($service);
	}
	return $rpc_client;
}
function config($name,$default = ''){
	$val = config::get($name);
	if($val === ''){
		return $default !== '' ? $default : '';
	}else{
		return $val;
	}
}
function lang($config_name){
	return lang::get($config_name);
}
function log_response(){
	register_shutdown_function('log::response');
}
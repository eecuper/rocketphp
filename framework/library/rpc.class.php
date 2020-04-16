<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
class rpc{
	private $conns        = array();
	private $read_len     = 1024;
	private $time_out     = 30;
	private $url          = '';
	private $protocol     = '';
	private $request_type = 'http';
	private $shell_script = 'php';
	private $sync         = false;
	private $service      = '';

	function __construct(){
		$protocol = defined('RPC_PROTOCOL') ? RPC_PROTOCOL : 'tcp';
		if($protocol != 'tcp' && $protocol != 'http' && $protocol != 'https'){
			throw new Exception('Rpc protocol error: '.$protocol);
		}
		$this->protocol = $protocol;
		if($protocol != 'tcp'){
			if(!defined('RPC_DSN')){
				throw new Exception('Rpc dsn is empty!');
			}
			$reg = '/^((0|[1-9]\d?|1\d\d|2[0-4]\d|25[0-5])\.){3}(0|[1-9]\d?|1\d\d|2[0-4]\d|25[0-5]):\d+/';
			if(!preg_match($reg, RPC_DSN)){
				throw new Exception('Rpc dsn error: '.RPC_DSN);
			}
			$this->url = $protocol.'://'.RPC_DSN;
		}
	}

	function call($uri, $params){
		if($this->service == ''){
			throw new Exception('Rpc service name empty!');
		}

		if(is_string($uri) && $uri){
			if(strpos($uri, '.') !== false){
				$arr = explode('.', $uri);
			}else{
				$arr = explode('/', $uri);
			}
			if(count($arr) > 1){
				list($class, $method) = $arr;
			}else{
				$class  = $this->service;
				$method = $uri;
			}
		}else if(is_array($uri) && $uri){
			if(count($uri) > 1){
				list($class, $method) = $uri;
			}else{
				$class  = $this->service;
				$method = $uri[0];
			}
		}else{
			throw new Exception('Rpc uri error!');
		}

		$uri = $this->service.'://'.ucfirst($class).'/'.ucfirst($method);
		foreach($params as $k => &$v){
			if(!is_string($v)){
				$v = (string)$v;
			}
		}
		if($this->protocol == 'tcp'){
			$resp = $this->tcp_call($uri, $params);
		}else{
			$resp = $this->http_call($uri, $params);
		}

		if(empty($resp)){
			return array(
				'error' => 1,
				'data'  => 'Rpc response error!'
			);
		}
		$resp_json = json_decode($resp, true);
		if(!is_array($resp_json)){
			return array(
				'error' => 0,
				'data'  => $resp
			);
		}else{
			return $resp_json;
		}
	}

	function set_service($service){
		$this->service = $service;
	}

	private function tcp_call($uri, $params, $dsn_key = 'RPC_DSN'){
		if(empty($this->conns[$dsn_key])){
			if(!defined($dsn_key)){
				throw new Exception('Rpc request_dsn is empty!');
			}
			$dsn = $dsn_key == 'RPC_DSN' ? RPC_DSN : RPC_REQUEST_DSN;
			$reg = '/^((0|[1-9]\d?|1\d\d|2[0-4]\d|25[0-5])\.){3}(0|[1-9]\d?|1\d\d|2[0-4]\d|25[0-5]):\d+$/';
			if(!preg_match($reg, $dsn)){
				throw new Exception('Rpc dsn error: '.$dsn);
			}
			$this->conns[$dsn_key] = stream_socket_client('tcp://'.$dsn, $errno, $errstr, 3);
			if($errno){
				throw new Exception('Rpc connect error ('.$errno.')!');
			}
		}
		if(is_array($params)){
			$param = json_encode(
				array(
					'uri'    => $uri,
					'params' => $params,
				)
			);
		}else if(is_string($params)){
			$param = $params;
		}
		stream_set_timeout($this->conns[$dsn_key], $this->time_out);
		fwrite($this->conns[$dsn_key], $param."\n");
		$recv = '';
		do{
			$read = fgets($this->conns[$dsn_key], $this->read_len);
			$recv .= $read;
		}while(strlen($read) == $this->read_len - 1);
		return $recv;
	}

	private function http_call($uri, $params){
		if(!is_string($params)){
			$data  = array('uri' => $uri, 'params' => $params);
			$param = json_encode($data);
		}else{
			$param = $params;
		}
		return self::curl_post($this->url, $param);
	}

	private function curl_post($url = '', $data = array(), $headers = array(), $timeout = 5){
		$postfield = $s = '';
		if(!empty($data)){
			if(is_array($data)){
				foreach($data as $k => $v){
					$postfield .= $s.$k.'='.rawurlencode($v);
					if(preg_match('/^@/', $v)){
						$postfield = $data;
						break;
					}
					$s = '&';
				}
			}else if(is_string($data)){
				$postfield = $data;
			}
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		if($headers){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; SeaPort/1.2; Windows NT 5.1; SV1; InfoPath.2)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if(stripos($url, 'https://') !== FALSE){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		}
		$value = curl_exec($ch);
		if(curl_errno($ch)){
			error('curl_post error : '.curl_error($ch).' url : '.$url);
		}
		curl_close($ch);
		return $value;
	}

	//request async
	private function request_type($type, $scriptname = 'php'){
		$this->request_type = $type;
		if(!in_array($type, array('http', 'shell'))){
			throw new Exception('Request type error!');
		}
		if($type == 'shell' && $scriptname != 'php'){
			$this->shell_script = $scriptname;
		}
	}

	function request($url, $timeout = 0){
		if($url == ''){
			throw new Exception('Request url error!');
		}
		$url    = str_replace(' ', '', $url);
		$urlarr = parse_url($url);
		if(empty($urlarr['path']) || $urlarr['path'] == '/'){
			$urlarr['path'] = 'index.php';
		}
		if($this->request_type == 'http'){
			if(empty($urlarr['host'])){
				$urlarr['scheme'] = 'http';
				$urlarr['host']   = $_SERVER['HTTP_HOST'];
			}
			$type = 'http';
			$url  = $urlarr['scheme'].'://'.$urlarr['host'].'/'.ltrim($urlarr['path'], '/').'?'.$urlarr['query'];
		}else{
			$path = CMS_ROOT.ltrim($urlarr['path'], '/');
			$type = 'shell';
			$url  = $this->shell_script.' '.$path.' '.$urlarr['query'];
		}
		$param = array('type' => $type, 'after' => (string)$timeout, 'url' => $url);
		if($timeout == 0){
			$param['sync'] = $this->sync ? '1' : '0';
		}
		if($this->protocol == 'tcp'){
			return $this->tcp_call('', json_encode($param), 'RPC_REQUEST_DSN');
		}else{
			return $this->http_call('', json_encode($param));
		}
	}

	function __destruct(){
		if($this->conns){
			foreach($this->conns as $conn){
				fclose($conn);
			}
		}
	}
}
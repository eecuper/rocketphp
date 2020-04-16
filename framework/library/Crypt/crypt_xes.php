<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
/** 
 * 常用对称加密算法类 
 * 支持密钥：8/24/32 
 * 支持算法：DES/3DES/AES 
 * 支持模式：CBC/ECB,默认为cbc
 * 密文编码：base64(字符串)/hex(十六进制字符串)/bin(二进制字符串流),默认为base64 
 * 填充方式: PKCS5Padding（DES） 
 */  
class crypt_xes{
	private $cipher;  
	private $key;  
	private $mode;  
	private $iv;  
	function __construct($option = array()){
		if(empty($option['type'])) fatal_error('No cipher!');
		if(empty($option['key'])) fatal_error('No key!');
		//cipher
		switch(strtolower($option['type'])){
			case 'des':
				$this->cipher = MCRYPT_DES;
				$this->key = substr(str_pad($option['key'],8,'0'),0,8);
				break;
			case '3des':
				$this->cipher = MCRYPT_3DES;
				$this->key = substr(str_pad($option['key'],24,'0'),0,24);
				break;
			case 'aes':
				$this->cipher = MCRYPT_RIJNDAEL_128;
				$this->key = substr(str_pad($option['key'],32,'0'),0,32);
				break;
			case 'aes2':
				$this->cipher = MCRYPT_RIJNDAEL_256;
				$this->key = substr(str_pad($option['key'],32,'0'),0,32);
				break;
		}
		//mode
		$this->mode = 
		!empty($option['mode'])&&in_array(strtolower($option['mode']),array('cbc','ecb','ofb','cfb'))		
		?$option['mode']
		:'cbc';
		//blocksize
		$this->blocksize = mcrypt_get_block_size($this->cipher,$this->mode);
		//iv
		//ecb不需要向量
		$this->iv = 
		(empty($option['iv']) or $this->mode == 'ecb' or $option['iv'] == 'off')
		?null
		:($option['iv']=='auto'?
		@mcrypt_create_iv($this->blocksize,PHP_OS == 'WINNT' ? MCRYPT_RAND : MCRYPT_DEV_RANDOM)
		:$option['iv']);
		$this->iv_auto = !empty($option['iv']) && $option['iv']=='auto';
		//code
		$this->code = 
		!empty($option['encode'])&&in_array(strtolower($option['encode']),array('base64','base64_safe','hex','bin'))		
		?$option['encode']
		:'base64';
	}
	function get_config(){
		return array(
			"cipher" => $this->cipher,
			"key" => $this->key,
			"mode"=> $this->mode,
			"iv_need"=> $this->blocksize,
			"iv"=>$this->iv_auto ? ($this->code=='base64'
				?base64_encode($this->iv)
				:($this->code=='hex'?
				bin2hex($this->iv)
				:$this->iv)):$this->iv,
			"encode"=>$this->code,
		);	
	}
	function encrypt($input){
		if($this->cipher != MCRYPT_RIJNDAEL_128 && $this->cipher != MCRYPT_RIJNDAEL_256)
			$input = $this->pkcs5_pad($input);
		if(empty($this->iv)){
			$data = @mcrypt_encrypt($this->cipher, $this->key, $input, $this->mode);
		}else{
			$data = @mcrypt_encrypt($this->cipher, $this->key, $input, $this->mode, $this->iv); 
		}
		switch($this->code){
			case 'base64':
				$data = base64_encode($data);
				break;
			case 'base64_safe':
				$data = str_replace(array('+','/'),array('_','-'),base64_encode($data));
				break;
			case 'hex':
				$data = bin2hex($data);
				break;
		}
		return $data;
	}
	function decrypt($encrypted){
		switch($this->code){
			case 'base64':
				$encrypted = base64_decode($encrypted);
				break;
			case 'base64_safe':
				$encrypted = base64_decode(str_replace(array('_','-'),array('+','/'),$encrypted));
				break;
			case 'hex':
				$binData = "";
				for($i = 0; $i < strlen ( $encrypted ); $i += 2) {
					$binData .= chr ( hexdec ( substr ( $encrypted, $i, 2 ) ) );
				}
				$encrypted = $binData;
				break;
		}
		if(empty($encrypted))
			return '';
		if(empty($this->iv)){
			$data = @mcrypt_decrypt($this->cipher, $this->key, $encrypted, $this->mode);
		}else{
			$data = @mcrypt_decrypt($this->cipher, $this->key, $encrypted, $this->mode, $this->iv); 
		}
		if($this->cipher != MCRYPT_RIJNDAEL_128 && $this->cipher != MCRYPT_RIJNDAEL_256)
			$data = $this->pkcs5_unpad($data);  
		return trim($data);
	}
	private function pkcs5_pad ($text) {		
		$blocksize = $this->blocksize;
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}
	private function pkcs5_unpad($text){
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) {
			return false;
		}
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
			return false;
		}
		return substr($text, 0, -1 * $pad);
	}
}
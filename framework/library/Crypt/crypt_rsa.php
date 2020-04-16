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
 * 使用openssl实现非对称加密
 */
class crypt_rsa{
	public function generate_keys($config = ''){
		$configargs = array(
			"config" =>$config,
			'digest_alg' => 'sha1', 
			'private_key_type' => OPENSSL_KEYTYPE_RSA, 
			'private_key_bits' => 1024
		);
		$r		= openssl_pkey_new($configargs);
		$result	= openssl_pkey_export($r, $privKey,NULL,$configargs);
		$rp 	= openssl_pkey_get_details($r);
		if(empty($privKey))
		exit('File openssl.cnf is not correct!<br/>Example:<br/>WIN => f:/wamp/bin/php/php5.3.5/extras/openssl/openssl.cnf<br/>Linux => /etc/pki/tls/openssl.cnf');
		file_put_contents('rsa_private_key.pem',$privKey);
		file_put_contents('rsa_public_key.pem',$rp['key']);
		echo 'Generate keys ok!';
	}
    //公钥加密,最大长度117
    public function encrypt($data,$public_key_path) {
		$pubKey = file_get_contents($public_key_path);
		$res 	= openssl_get_publickey($pubKey);
        $encrypted = '';
		$count = ceil(strlen($data)/117);
		if($count>1){
			for($i=1;$i<=$count;$i++){
				openssl_public_encrypt(substr($data,($i-1)*117,117),$result,$res);
				$encrypted.= $result;
			}
		}else{
			openssl_public_encrypt($data, $result, $res);
			$encrypted = $result;
		}
        return base64_encode($encrypted);
    }
	//私钥解密
    public function decrypt($content, $private_key_path) {
		$priKey = file_get_contents($private_key_path);
		$res 	= openssl_get_privatekey($priKey);
		//用base64将内容还原成二进制
		$content = base64_decode($content);
		//把需要解密的内容，按128位拆开解密
		$result  = '';
		for($i = 0; $i < strlen($content)/128; $i++  ) {
			$data = substr($content, $i * 128, 128);
			openssl_private_decrypt($data, $decrypt, $res);
			$result .= $decrypt;
		}
		openssl_free_key($res);
		return $result;
	}
	//rsa签名
    public function sign($data, $private_key_path) {
		$priKey = file_get_contents($private_key_path);
		$res 	= openssl_get_privatekey($priKey);
		openssl_sign($data, $sign, $res);
		openssl_free_key($res);
		//base64编码
		$sign 	= base64_encode($sign);
		return $sign;
	}
	//rsa签名验证
	public function verify($data, $ali_public_key_path, $sign)  {
		$pubKey = file_get_contents($ali_public_key_path);
		$res 	= openssl_get_publickey($pubKey);
		$result = (bool)openssl_verify($data, base64_decode($sign), $res);
		openssl_free_key($res);    
		return $result;
	}
	
	function format_prikey($priKey) {
		$fKey = "-----BEGIN PRIVATE KEY-----\n";
		$len = strlen($priKey);
		for($i = 0; $i < $len; ) {
			$fKey = $fKey . substr($priKey, $i, 64) . "\n";
			$i += 64;
		}
		$fKey .= "-----END PRIVATE KEY-----";
		return $fKey;
	}
	
	function format_pubkey($pubKey) {
		$fKey = "-----BEGIN PUBLIC KEY-----\n";
		$len = strlen($pubKey);
		for($i = 0; $i < $len; ) {
			$fKey = $fKey . substr($pubKey, $i, 64) . "\n";
			$i += 64;
		}
		$fKey .= "-----END PUBLIC KEY-----";
		return $fKey;
	}
}
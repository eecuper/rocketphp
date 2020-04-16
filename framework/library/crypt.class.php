<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class crypt{
	/*
	 >>>>des:
	 *@param $type:des,3des,aes,rsa
	 *@param $option:key,*支持密钥长度：8/24/32
	 $des = crypt::get_instance('des',array('key'=>'12345678'));
	 option项:
	 	:mode >> ：CBC/ECB,默认为cbc
		://iv,//ecb不需要向量,'auto'
		:'encode' >> 'base64','hex','bin',输出编码规则,默认为base64
		:填充方式: PKCS5Padding（DES）
		
	 >>>>rsa:
	 use: $rsa = crypt::get_instance('rsa');
	 ->generate_keys($conf_path), 
	 ->encrypt($data,$public_key_path),
	 ->decrypt($data,$private_key_path); 
	*/
	public static function get_instance($type,$option = array()){
		static $instances = array();
		$type = strtolower($type);
		$key = md5($type.serialize($option));
		if(!isset($instances[$key])){
			if(in_array($type,array('des','3des','aes'))){
				require_cache(SYS_ROOT.'library/Crypt/crypt_xes.php');
				$option['type'] 	= $type;
				$instances[$key] 	= new crypt_xes($option);
			}else{
				$type = 'crypt_'.$type;
				require_cache(SYS_ROOT.'library/Crypt/'.$type.'.php');
				$instances[$key] 	= new $type($option);
			}			
		}
		return $instances[$key];
	}
}
<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
defined('UPLOAD_TEMP') or define('UPLOAD_TEMP',0);
defined('UPLOAD_ROOT') or define('UPLOAD_ROOT','');
class upload{
	private $uptypes = 
	array('image/jpeg'=>'jpg','image/pjpeg'=>'jpg','image/png'=>'png','image/x-png'=>'png',	'image/gif'=>'gif','image/wbmp'=>'bmp','image/bmp'=>'bmp','application/octet-stream'=>'png');
	private $max_file_size 	= 3000000;	//3M
	private $max_w         	= 2000;
	private $max_h         	= 1600;
	private $multiple  		= false;
	const UPBASEDIR			= 'upload';
	static function &get_instance($check = true) {
		static $object;
		if(empty($object)) {
			$object = new self($check);
		}
		return $object;
	}
	private function __construct($check){
		if($check && self::legal_check()){
			exit('Error!');
		}
		$this->multiple = !empty($_GET['multiple']);
	}
	function do_upload(){
		$c = cookie('u_c_r');
		if((ROUTE_A == 'upload' && (empty($c) || !in_array(md5('?'.$_SERVER['QUERY_STRING']),$c)))
		|| (ROUTE_A == 'editor_upload' && !session('u_c_r'))
		){
			error('直接请求上传!');
			exit;
		}
		$upfiles = $_FILES['upload'];
		//上传文件判断
		$errr_str = '';
		if($this->multiple){
			//检查上传数量
			if(empty($upfiles['tmp_name'])){
				$this->message('没有任何文件被上传!');	
			}
			$limit 	= getd('upload_limit');
			$cnt 	= count($upfiles['tmp_name']);
			if($limit && $cnt > $limit){
				$this->message('最多允许上传'.$limit.'张图片!');	
			}
			$xuhao = 1;
			for($i=0;$i<$cnt;$i++){
				$uploads[$i] = array(
					'name'=>$upfiles['name'][$i],
					'type'=>$upfiles['type'][$i],
					'tmp_name'=>$upfiles['tmp_name'][$i],
					'error'=>$upfiles['error'][$i],
					'size'=>$upfiles['size'][$i]
				);
				$error = $this->file_check($uploads[$i]);
				if($error){
					$errr_str .= ($xuhao++).'.上传文件 '.$uploads[$i]['name'].' 错误: '.$error."\\n";	
				}
			}
		}else{
			$errr_str = $this->file_check($upfiles);
		}
		if($errr_str)
				$this->message($errr_str);	
		//新建目录
		if(UPLOAD_ROOT){
			$this->set_home_root();
		}else{
			$this->set_home();
		}
		if($this->multiple){
			$files = array();
			$error = $success = 0;
			foreach($uploads as $up){
				$upresult = $this->move_upload_file($up);
				if(isset($upresult['filename'])){
					$files[] = $upresult['filename'];
					$success++;
				}else{
					$error++;
				}
			}
			self::show_form();
			$return = json_encode(array('imglist'=>$files,'total'=>count($uploads),'errors'=>$error,'success'=>$success));
			exit('<script>parent.'.getg('callback').'(\''.$return.'\')</script>');		
		}else{
			$upresult = $this->move_upload_file($upfiles);
			if(isset($upresult['filename'])){
				self::show_form();
				exit('<script>parent.'.getg('callback').'("'.$upresult['filename'].'",'.getd('i').')</script>');
			}else{
				$this->message($upresult['error']);
			}
		}		
	}
	//参数合法性判断
	static function legal_check(){
		return empty($_GET['CKEditor']) && getg('v') != md5('?'.substr($_SERVER['QUERY_STRING'],0,-35).AUTH_KEY);
	}
	function file_check($upfile){		
		if(empty($upfile)){
			return '没有任何文件被上传!';
		}
		if($error = $upfile['error']){
			switch($error){
				case 1:
				case 2:
					return '上传文件大小超过最大值!';
				case 3:
					return '文件只有部分被上传!';
				case 4:
					return '没有任何文件被上传!';
				case 6:
					return '找不到临时文件夹!';
				case 7:
					return '文件写入失败!';
			}
		}
		if(!is_uploaded_file($upfile['tmp_name'])) {
			return '上传内容不合法!';
		}
		if($upfile['size'] == 0){
			return '上传内容为空!';	
		}
		$whsize = getimagesize($upfile["tmp_name"]);
		if(empty($whsize) or !in_array( $upfile["type"],array_keys($this->uptypes))){
			return '只能上传图片文件类型:png,jpg,gif,bmp';
		}else{
			$suffix = $this->uptypes[$upfile["type"]];
		}
		if(isset($_GET['type'])){
			$types = explode(',',$_GET['type']);
			if($types){
				foreach($types as $t){
					if(!in_array($t,array_values($this->uptypes))){
						return 'Type 参数不正确';
					}
				}
				if(!in_array($suffix,$types)){
					return '请上传'.$_GET['type'].'类型的文件！';
				}
			}
		}
		$mxsize = isset($_GET['maxsize'])?intval($_GET['maxsize']):$this->max_file_size;
		if($upfile["size"] > $mxsize){
			return '文件太大,应小于'.self::sizecount($mxsize);
		}
		if(isset($_GET['maxwidth'])){
			$this->max_w = intval($_GET['maxwidth']);
		} 
		if(isset($_GET['maxheight'])){
			$this->max_h = intval($_GET['maxheight']);
		}
		if(isset($_GET['width']) && isset($_GET['height']) ){
			if($whsize[0] != $_GET['width'] or $whsize[1] != $_GET['height']){
				return '图片尺寸应为:宽*高'.$_GET['width'].'*'.$_GET['height'].'(px),请检查!';
			}
		}else{
			if(isset($_GET['width']) && $whsize[0] != $_GET['width']){
				return '图片尺寸应为:宽'.$_GET['width'].'px,请检查!';	
			}
			if(isset($_GET['height']) && $whsize[1] != $_GET['height']){
				return '图片尺寸应为:高'.$_GET['height'].'px,请检查!';
			}
		}
		if(isset($_GET['size'])){
			$arr = explode(',',$_GET['size']);
			$arr = array_map('trim',$arr);
			if(!in_array($whsize[0].'*'.$whsize[1],$arr)){
				return '图片尺寸应为:宽*高'.$_GET['size'].'(px),请检查!';
			}
		}
		if(isset($_GET['maxwidth']) && $whsize[0] > $_GET['maxwidth']){
			return '图片的宽度应小于'.$_GET['maxwidth'].'px,请检查!';
		}
		if(isset($_GET['maxheight']) && $whsize[1] > $_GET['maxheight']){
			return '图片的高度应小于'.$_GET['maxheight'].'px,请检查!';
		}
		if(isset($_GET['minwidth']) && $whsize[0] < $_GET['minwidth']){
			return '图片的宽度应大于'.$_GET['minwidth'].'px,请检查!';
		}
		if(isset($_GET['minheight']) && $whsize[1] < $_GET['minheight']){
			return '图片的高度应大于'.$_GET['minheight'].'px,请检查!';
		}
		if(isset($_GET['width'])){
			$this->max_w = max($_GET['width'],$this->max_w);
		} 
		if(isset($_GET['height'])){
			$this->max_h = max($_GET['height'],$this->max_h);;
		}
		return false;
	}
	//移动文件并做压缩处理
	function move_upload_file($upfile){
		$filename 	= (UPLOAD_TEMP?$this->updir_temp:$this->updir).uniqid().str_pad( dechex(mt_rand(0,255)),2,'0',STR_PAD_LEFT).'.'.$this->uptypes[$upfile['type']];
		if(!move_uploaded_file($upfile['tmp_name'],$filename)){
			return array('error'=>'移动文件错误,没有将文件移动到指定目录！');
		}else{
			$size   	= getimagesize($filename);
			if($size[0] > $this->max_w || $size[1] > $this->max_h){
				$image = new image();
				$image->thumb($filename,'',$this->max_w,$this->max_h);
			}			
			if(UPLOAD_ROOT){
				$base_dir 	= rtrim(UPLOAD_ROOT,'/');
				$upname = substr($filename,strlen($base_dir));
			}else{
				$upname = substr($filename,strrpos($filename,'/'.self::UPBASEDIR.'/')+1);
				if(dirname(str_replace(CMS_ROOT,'',str_replace('\\','/',$_SERVER['SCRIPT_FILENAME']))) === '.')
					$upname =   '/'.APPLICATION.'/'.$upname;
			}
			return array('filename'=>$upname);
		}
	}
	function set_home(){
		$dir 	= dirname(str_replace(CMS_ROOT,'',str_replace('\\','/',$_SERVER['SCRIPT_FILENAME'])));
		$updir 	= $dir === '.' ?  APP_PATH.'/' :(CMS_ROOT.$dir.'/');
		$dir  	= $updir.self::UPBASEDIR.'/'.date('Y/m/d').'/';
		if(!is_dir($dir)){
			mkdir($dir,0777,true);
		}
		$this->updir = $dir;
		if(UPLOAD_TEMP){
			$this->updir_temp = $updir.self::UPBASEDIR.'/temp/';
			if(!is_dir($this->updir_temp)){
				mkdir($this->updir_temp,0777,true);
			}
			$this->updir_temp .= date('Y_m_d_');
		}
	}
	function set_home_root(){
		$base_dir 	= rtrim(UPLOAD_ROOT,'/');
		$dir  		= $base_dir.'/'.date('Y/m/d').'/';
		if(!is_dir($dir)){
			mkdir($dir,0777,true);
		}
		$this->updir = $dir;
		if(UPLOAD_TEMP){
			$this->updir_temp = $base_dir.'/temp/';
			if(!is_dir($this->updir_temp)){
				mkdir($this->updir_temp,0777,true);
			}
			$this->updir_temp .= date('Y_m_d_');
		}
	}
	function message($str){
		self::show_form();
		exit('<script>alert(\''.$str.'\');</script>');
	}
	static function sizecount($filesize) {
		if ($filesize >= 1073741824) {
			$filesize = round($filesize / 1073741824 * 100) / 100 .' GB';
		} elseif ($filesize >= 1048576) {
			$filesize = round($filesize / 1048576 * 100) / 100 .' MB';
		} elseif($filesize >= 1024) {
			$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
		} else {
			$filesize = $filesize.' B';
		}
		return $filesize;
	}
	static function move_upload($filename){
		if(UPLOAD_TEMP && strpos($filename,'/temp/') !== false){
			$tofilename = str_replace(array('/temp/','_'),'/',$filename);
			if(UPLOAD_ROOT){
				$base_dir = rtrim(UPLOAD_ROOT,'/');
				$from 	= $base_dir.$filename;
				$to 	= $base_dir.$tofilename;
			}else{
				$from 	= CMS_ROOT.$filename;
				$to 	= CMS_ROOT.$tofilename;
			}
			if(is_file($from)){
				rename($from,$to);
				return $tofilename;
			}
		}
		return $filename;
	}
	static function clear_upload($expire = 3600){
		if(UPLOAD_TEMP){
			if(UPLOAD_ROOT){
				$temp_dir = rtrim(UPLOAD_ROOT,'/').'/temp/';
			}else{
				$dir 	= dirname(str_replace(CMS_ROOT,'',str_replace('\\','/',$_SERVER['SCRIPT_FILENAME'])));
				$updir 	= $dir === '.' ?  APP_PATH.'/' :(CMS_ROOT.$dir.'/');
				$temp_dir = $updir.self::UPBASEDIR.'/temp/';
			}
			if($files = glob($temp_dir.'*')){
				$deadtime = strtotime('-'.$expire.' seconds');
				foreach($files as $f){
					filemtime($f) < $deadtime && unlink($f);
				}	
			}
		}
	}
	
	function upload_stream($img_stream,$max = 0){
		if(empty($img_stream)){
			return array('error'=>'content is empty!');	
		}
		$max = $max ? $max : $this->max_file_size;
		if(strlen($img_stream) > $max){
			return array('error'=>'file is too big!');
		}
		$type = self::get_file_type($img_stream);
		if(!in_array($type,array('png','jpg','bmp','gif'))){
			return array('error'=>'type is error!');
		}
		if(UPLOAD_ROOT){
			$this->set_home_root();
		}else{
			$this->set_home();
		}
		$filename 	= (UPLOAD_TEMP?$this->updir_temp:$this->updir)
		.uniqid().str_pad( dechex(mt_rand(0,255)),2,'0',STR_PAD_LEFT).'.'.$type;
		file_put_contents($filename,$img_stream);
		if(UPLOAD_ROOT){
			$base_dir 	= rtrim(UPLOAD_ROOT,'/');
			$upname 	= substr($filename,strlen($base_dir));
		}else{
			$upname = substr($filename,strrpos($filename,'/'.self::UPBASEDIR.'/')+1);
			if(dirname(str_replace(CMS_ROOT,'',str_replace('\\','/',$_SERVER['SCRIPT_FILENAME']))) === '.')
				$upname =   '/'.APPLICATION.'/'.$upname;
		}
		return array('error'=>0,'filename'=>$upname);
	}
	
	function get_file_type($imgstream){    
		$hex 		= bin2hex(substr($imgstream,0,2));
		$typeCode   = intval(hexdec(substr($hex,0,2)).hexdec(substr($hex,-2)));
		$fileType   = '';
		switch($typeCode){
			case 7790:
				$fileType = 'exe';
				break;
			case 7784:
				$fileType = 'midi'; 
				break;
			case 8297:
				$fileType = 'rar'; 
				break;
			case 255216:
				$fileType = 'jpg';
				break;
			case 7173:
				$fileType = 'gif';
				break;
			case 6677:
				$fileType = 'bmp';
				break;
			case 13780:
				$fileType = 'png';
				break;
			default:
			  $fileType = 'unknown';
		}    
		return $fileType;
	}
	
	function show_form(){
		$timestamp 	= time();
		$tooken 	= md5(AUTH_KEY.$timestamp);
		$sysdir		= SYS_JS_DIR;
		$namestr 	= $this->multiple ? 'name="upload[]" multiple="true" ' : 'name="upload"'  ;
		echo
		<<<EOT
		<!DOCTYPE HTML>
		<html>
		<head>
		<style>
		body{margin:0px}
		#upbtn{width:54px; height:24px;overflow:hidden;background:url({$sysdir}/up.png) no-repeat;}
		#upbtn:active{background-position:0px -26px}
		#upfile{height:500px;width:200px;font-size:200px;filter:Alpha(opacity=0);opacity:0;}
		#upfile{cursor:pointer;cursor:hand;border:none;margin-left:-10px;margin-top:-10px;}
		#upbutton{display:none}
		</style>
		</head>
		<body>
        <form method="post" enctype="multipart/form-data">
        <input type='hidden' name="timestamp" value="{$timestamp}"/>
        <input type='hidden' name="tooken" value="{$tooken}"/>
        <div id="upbtn">
        <input type="file" id="upfile" {$namestr} accept="image/*" onchange="upbutton.click()" title='请选择文件'/>
        <input type="submit" id="upbutton" name="_dosubmit" value="up"/>
        </div>
        </form>
        </body>
        </html>
EOT;
	}
}
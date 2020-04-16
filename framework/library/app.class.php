<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class app{
	static function create($app_name = ''){
		$app_name = CMS_ROOT.$app_name;
		if(self::make_dirs($app_name)) return;
		if(strpos(strtolower($_SERVER['SERVER_SOFTWARE']),'apache')!== false){
			self::make_templates_secure($app_name);
			self::make_logs_secure($app_name);
			self::make_upload_secure($app_name);
		}
		self::make_config($app_name);
		self::make_controller($app_name);
		self::make_template($app_name);
		self::make_lang($app_name);
	}
	static function make_dirs($app_name){
		if(is_dir($app_name)){
			return true;
		}else{
			$oldumask = umask(0); 
			mkdir($app_name,0775,true);
			umask($oldumask);
		}
		self::build_dir_secure($app_name);
		$dirs  = array(
			'caches',
			'caches/data',
			'caches/sessions',
			'caches/templates',
			'config',
			'controls',
			'models',
			'upload',
			'upload/avatar',
			'templates',
			'templates/default',
			'templates/default/main',
			'static',
			'static/js',
			'static/css',
			'static/images',
			'html',
			'lib',
			'logs',
			'language',
			'language/cn'
		);
        foreach ($dirs as $dir){
			$dir = $app_name.'/'.$dir;
            if(!is_dir($dir)){
				$oldumask=umask(0); 
				mkdir($dir,0775,true);
				umask($oldumask);
				self::build_dir_secure($dir);
			}
        }
	}
	static function make_templates_secure($app_name){
		$str = 
		'<Files *.html>
		Order Allow,Deny 
		Deny from all
		</Files>';
		file_put_contents($app_name.'/templates/.htaccess',str_replace("\t",'',$str));
	}
	static function make_logs_secure($app_name){
		$str = 
		'<Files *>
		Order Allow,Deny 
		Deny from all
		</Files>';
		file_put_contents($app_name.'/logs/.htaccess',str_replace("\t",'',$str));
	}
	static function make_upload_secure($app_name){
		$str = 
		'<Files *.php>
		Order Allow,Deny 
		Deny from all
		</Files>';
		file_put_contents($app_name.'/upload/.htaccess',str_replace("\t",'',$str));
	}
	static function make_config($app_name){
		$str = 
		'<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined(\'ISROCKET\') or exit(\'Access denied!\');
		define(\'DEBUG_LOG\', 1);
		define(\'ERROR_LOG\', 1);
		define(\'YIBU_LOG\', 1);
		define(\'ERROR_LEVEL\', \'all\');
		define(\'SHOW_LOG\', 1);
		define(\'TRACE_LOG\', 0);
		define(\'TRACE_ITEMS\', \'time=1;usage=1;maxusage=1;userclass=0;db=0;userclass_detail=0;include=0;include_detail=0;userfunc=0;userfunc_detail=0;\');
		define(\'SLOW_LOG\', 0);
		define(\'SLOW_LOG_TIME\', 5);
		define(\'MEMORY_LOG\', 0);
		define(\'MEMORY_LOG_SIZE\', 1000);
		define(\'DEFAULT_LANG\', \'cn\');
		define(\'TEMPLATE_STYLE\', \'default\');
		define(\'SESSION_MODE\', \'file\');
		define(\'SESSION_TIMEOUT\', \'3600\');
		define(\'SESSION_SAVEPATH\', \'caches/sessions\');
		define(\'AUTH_KEY\' , \''.md5(microtime(true)).mt_rand(1000,9999).'\');
		define(\'LOG_FORMAT\', \'file\');
		define(\'LOG_AUTHED\', 0);
		define(\'LOG_AUTHED_KEY\', \'127.0.0.1\');
		define(\'LOG_SAVED_DAYS\', 30);
		define(\'APP_HOST\', \'http://\'.$_SERVER[\'HTTP_HOST\'].\'/\');
		define(\'SYS_JS_DIR\', SYS_DIR.\'js\');
		define(\'IMG_PATH\', APPLICATION.\'/static/images\');
		define(\'MODULES\',\'main,api\');';
		file_put_contents($app_name.'/config/config.php',str_replace("\t",'',$str));
	}
	static function make_lang($app_name){
		$str = 
		'<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined(\'ISROCKET\') or exit(\'Access denied!\');
		return array();
		';
		file_put_contents($app_name.'/language/cn/lang_common.php',str_replace("\t",'',$str));
	}
	static function make_controller($app_name){
		$str = 
		'<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
class main extends controller {
		    function on_index(){
		        $this->display();
		    }
		}';
		file_put_contents($app_name.'/controls/main.php',str_replace("\t",'',$str));
		$str = 
		'<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
class api{
		    function on_index(){
		      exit;	
		    }
			function on_upload(){
				$up = upload::get_instance();
				if(getp("_dosubmit") && getp("tooken") == md5(AUTH_KEY.getp("timestamp"))){
					$up->do_upload();
				}else{
					$up->show_form();
				}
			}
			function on_editor_upload(){
				$up = upload::get_instance();
				$up->do_upload();
			}
			function on_log(){
				log::show();
			}
			function on_dellog(){
				log::del();
			}
			function on_checkcode(){
				$img = new checkcode();
				$img->doimage();
			}
		}';
		file_put_contents($app_name.'/controls/api.php',str_replace("\n\t\t","\n",$str));
	}
	static function make_template($app_name){
		$str='<html><body>hello world ^_^ !</body></html>';
		file_put_contents($app_name.'/templates/default/main/index.html',$str);
	}
	static function build_dir_secure($dir = '') {
		file_put_contents($dir.'/index.html','');
	}
}
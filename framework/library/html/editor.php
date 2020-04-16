<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_editor{
	//编辑器
	public static function make($val = '',$option = ''){
		$op =
		array('name'=>'editor_content','id'=>'editor1','type'=>'basic','height'=>300);
		extract(html::O($op,$option));
		$string  =
		'<textarea id="'.$id.'" name="'.$name.'" style="display:none" ></textarea>
		<script type="text/javascript" src="'.SYS_JS_DIR.'/ckeditor/ckeditor.js"></script>
		<script type="text/javascript">
			CKEDITOR.replace( \''.$id.'\',{toolbar:\''.$type.'\',height:\''.$height.'px\'});
			function upload_back(img){CKEDITOR.tools.callFunction(2,img);}
		</script>';
		if($val !== ''){
			$string .= '
			<div id="editor_temp_content" style="display:none">'.stripslashes(htmlspecialchars_decode($val)).'</div>
			<script type="text/javascript">
				var oEditor = CKEDITOR.instances.'.$id.';
				oEditor.setData(document.getElementById("editor_temp_content").innerHTML);
			</script>';
		}
		session('u_c_r','----');
		return str_replace("\t",'',$string);;
	}
}
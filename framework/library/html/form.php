<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_form{
	//data,可一纬数组,可二维数组,current当前值
	public static function select($items = array(),$current = -10,$option = '') {
		static $i = 1;
		$j = $i++;
		$op = 
		array('name'=>'select'.$j,'class'=>'','str'=>'','check'=>'','value_as_key'=>false,'default_value'=>'','default_title'=>'','id'=>'select'.$j);
		extract(html::O($op,$option));
		$html  = '<select name="'.$name.'"'.($class?' class="'.$class.'"':'')
		.($str?' '.$str:'').($check?' check="'.$check.'"':'').' id="'.$id.'">';
		$html .= '<option value="'.$default_value.'">'.$default_title.'</option>';
		$current =   $current === '' && $default_value !== '' ? $default_value : $current;
		if(is_array($items))
		foreach($items as $k => $v) {
			if (is_array($v)) {
				$v = array_values($v);
				$k = $v[0];
				$v = $v[1];
			}
			$kb = $k;
			if($value_as_key)
				$k = $v;
			$selected = ($current !== '' && $k == $current) ? 'selected="selected"':'';
			$html .= $kb === 'group' ? '<optgroup label="'.$v.'"></optgroup>'
			:'<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
		}
		$html .= '</select>';
		return $html;
	} 
	//radio
	//param : data,current,option
	public static function radio($items = array(),$current = -10,$option = ''){
		static $i = 1;
		$op  = array('name'=>'inputradio'.$i++,'str'=>'','class'=>'','default'=>'');
		extract(html::O($op,$option));
		$html = '<div'.($class ? ' class="'.$class.'"':'').'>';
		if($default !== '' && ($current === '' || $current === null)){
			$current = $default;
		}
		if(is_array($items)){
			foreach($items as $k => $v){
				if (is_array($v)) {
					$val = array_values($v);
					$k = $val[0];
					$v = $val[1];
				}
				$checked = ($current !== '' && $k == $current) ? ' checked="checked"':'';
				$html .= '<label><input name="'.$name.'" type="radio" value="'.$k.'"'
				.($str?' '.$str:'').$checked.' style="width:auto;"/>'.$v.'</label>';
			}
		}
		$html .= '</div>';
		return $html;
	}
	//checkbox
	//param:data,current..array,option
	public static function checkbox($items = array(),$current = array(),$option = '') {
		static $i = 1;
		$op  = array('name'=>'inputcheckbox'.$i++.'[]','class'=>'','str'=>'','value_as_key'=>false);
		extract(html::O($op,$option));
		$html = '<div'.($class?' class="'.$class.'"':'').'>';
		if(is_array($items)){
			foreach($items as $k => $v) {
				if (is_array($v)) {
					$val = array_values($v);
					$k = $val[0];
					$v = $val[1];
				}
				if($value_as_key)
					$k = $v;
				$checked = in_array($k,$current) ? ' checked="checked"':'';
				$html .= '<label><input name="'.$name.'" value="'.$k.'" type="checkbox"'
				.$checked.($str?' '.$str:'').'>'.$v.'</label>';
			}
		}
		$html .= '</div>';
		return $html;
	}
	//按钮
	public static function button($option = '',$backto = ''){
		$op = array('submit'=>1,'close'=>1,'back'=>0,'position'=>'left','class'=>'button');
		extract(html::O($op,$option));
		$html = '<div style="text-align:'.$position.'">';
		if($submit == 1){
			$html .= '<input type="submit" value="提交" name="do_submit" style="width:auto" class="'.$class.'" />';
		}
		if($close == 1 && $back == 0){
			$html .= '<input type="button" value="关闭" onclick="parent.dialog_close()"  style="width:auto" class="'
			.$class.'" />';
		}
		if($back == 1){
			$backto = $backto == ''? get_back():$backto;
			$html .= '<input type="button" value="返回" '.
			'onclick="window.location.href=\''.$backto .'\'"'
			.'  style="width:auto" class="'.$class.'" />';
		}
		$html .= '</div>';
		return $html;
	}
	//产生formgeturl
	static function form_url_vars(){
		$str = '<script>$(function(){var all="?";var i="";$(".fuv").each(function(){if($(this).parents("form").find("[name=\'"+$(this).attr("name")+"\']").size()>1){$(this).remove();}else{all+= i + $(this).attr("name")+"="+$(this).val();i="&"}});$("#searchall").click(function(){window.location.href=all})})</script>';
		foreach($_GET as $k => $v){
			if(!is_array($v))
				$str .='<input class="fuv" type="hidden" name="'.$k.'" value="'.$v.'" />';
		}
		return $str;
	}
}
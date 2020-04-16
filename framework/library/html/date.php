<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_date{
	//可配置参数,Id:多个必须不同,val:默认值,可时间字符串,可时间戳,isdatetime:有无时间
	public static function make($val = '',$option = '') {
		static $i = 1;
		$j = $i++;
		$op = array('id'=>'inputdate'.$j,'name'=>'inputdate'.$j,'class'=>'date',
		'isdatetime'=>0,'showweek'=>0,'systime'=>0,'str'=>'');
		extract(html::O($op,$option));
		$string = $str;
		$size 		= $isdatetime ? 21:10;
		$format 	= $isdatetime ? '%Y-%m-%d %H:%M:%S':'%Y-%m-%d';
		$dformat 	= $isdatetime ? 'Y-m-d H:i:s':'Y-m-d';
		$showsTime  = $isdatetime ? 'true':'false';
		$str = '';
		if(empty($val)){
			$value = $systime?date($dformat):'';
		}else{
			$value = preg_match('/^\d+$/',$val)?date($dformat,$val):$val;
		}
		if($i==2) {
			$str .= '<script type="text/javascript" src="'.SYS_JS_DIR.'/calendar.js?v=1.0"></script>';	
		}
		$str .= "\n";
		$str .='<input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.
		'" size="'.$size.'" class="'.$class.'" readonly'.($string?' '.$string:'').'>';
		$str .= "\n";
		$str .=
		'<script type="text/javascript">
		Calendar.setup({
		weekNumbers: '.$showweek.',
		inputField : "'.$id.'",
		trigger    : "'.$id.'",
		dateFormat : "'.$format.'",
		showTime   : '.$showsTime.',
		minuteStep : 1,
		animation  : false,
		onSelect   : function() {this.hide();}
		});
		</script>';
		return html::clear($str);
	}
}
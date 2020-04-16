<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_chart{
	//图表
	static function make($data = array(),$option = ''){
		//Line,Area,Bar
		static $i = 1;
		$op = array('xkey'=>'','ykeys'=>'','labels'=>'','type'=>'Line');
		extract(html::O($op,$option));
		$yks = explode(',',$ykeys);
		$dstr = '';
		if(is_array($data)){
			foreach($data as $v){
				$dstr .= '{'.$xkey.':"'.$v[$xkey].'",';
				foreach($yks as $k){
					$dstr .= $k.':"'.$v[$k].'",';
				}
				$dstr = trim($dstr,',');
				$dstr .= '},';
			}	
		}
		$dstr = trim($dstr,',');
		$str  = $i == 1 ?'<script type="text/javascript" src="'.SYS_JS_DIR.'/morris.js?v=1.0"></script>':'';
		$str .= '<div id="chart_container'.$i.'"></div>';
		$str .= '<script type="text/javascript">
				new Morris.'.ucfirst($type).'({
					element: "chart_container'.$i.'",
					xkey: "'.$xkey.'",
					ykeys: ['.'"'.implode($yks,'","').'"'.'],
					labels: ['.'"'.implode(explode(',',$labels),'","').'"'.'],
					data: ['.$dstr.'],
					parseTime: false
				});
				</script>';
		$i++;
		return html::clear($str);
	}
}
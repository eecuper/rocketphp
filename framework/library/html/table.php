<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_table{
	static function make($title = array(),$data = array(),$option = ''){
		$op = array('class'=>'','border'=>1);
		extract(html::O($op,$option));
		$str  = '<table border="'.$border.'" class="'.$class.'">';
		if($title && is_array($title)){
			$str .= '<tr style="background-color:#ccc">';	
			foreach($title as $h){
				$str .= '<th>'.$h.'</th>';
			}
			$str .= '</tr>';
		}
		if($data && is_array($data)){
			foreach($data as $k=>$row){
				if(is_array($row)){
					$str .= '<tr>';
					foreach($row as $v){
						$str .= '<td>'.($v !== ''? $v : '&nbsp;').'</td>';
					}
					$str .= '</tr>';
				}else{
					$str .= '<tr><td>'.$k.'</td><td>'.$row.'</td></tr>';
				}
				
			}
		}
		$str .= '</table>';
		return $str;
	}
}
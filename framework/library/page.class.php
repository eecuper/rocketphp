<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class page{
	static function get_pages($sql,$pc=30,$option = array(),$obj){
		$ppc 	= !empty($option['ppc'])?$option['ppc']:10;
		$lng 	= !empty($option['lng'])?$option['lng']:'cn';
		$count 	= !empty($option['count'])?$option['count']:0;
		$primary= !empty($option['primary'])?$option['primary']:'';
		if(empty($count)){
			preg_match('/\sfrom\s/i',$sql,$from);
			preg_match('/\sgroup\s+by\s/i',$sql,$group);
			if(!empty($group[0])){
				$gby_str = substr($sql,stripos($sql,$group[0]) + strlen($group[0]), strlen($sql));
				preg_match('/\sorder\s+by\s/i',$gby_str,$orderby);
				if(!empty($orderby[0])){
					$gby_str = substr($gby_str,0,stripos($gby_str,$orderby[0]));
				}
				preg_match('/\shaving\s/i',$gby_str,$having);
				if(empty($having[0])){
					$count_sql = 
					'select count(distinct '.$gby_str.') cnt '
					.substr($sql,strpos($sql,$from[0]),strpos($sql,$group[0])-strpos($sql,$from[0]));
				}else{
					$gby_str = substr($gby_str,0,stripos($gby_str,$having[0]));
					$count_sql = 
					'select SUM(cnt) cnt from (select count(distinct '.$gby_str.') cnt '.substr($sql,strpos($sql,$from[0])).') tmp';
				}
			}else{
				$count_sql	= 'select count(*) cnt '.substr($sql,strpos($sql,$from[0]));
			}
			$dc    			= $obj->get_row($count_sql);
			$count          = $dc['cnt']; 
		}
		$pages = page::make($count,array('page_count'=>$pc,'ppage_count'=>$ppc,'lng'=>$lng));
		$pages['data']	= $count?$obj->get_all($sql.$pages['limit'],$primary):array();
		unset($pages['limit']);
		return $pages;
	}
	static function make( $count = 0, $option = array()){
		$count = pintval($count);
		if(empty($count)){
			return array('records' => 0,'pages' => 0,	'current' => 0,	'html' => '','limit' => '',);	
		}
		$page_count	= isset($option['page_count'])  ? pintval($option['page_count']) : 15;
		$ppage_count= isset($option['ppage_count']) ? pintval($option['ppage_count']): 10;
		$lng = isset($option['lng'])? $option['lng']: 'cn';
		$position = isset($option['position'])? $option['position']: 'right';
		$page_prefix = isset($option['prefix'])	? $option['prefix']: 'page';
		$pages = ceil($count/$page_count); 
		if(isset($_GET[$page_prefix]) && pintval($_GET[$page_prefix]) > 0){
			$page_current = $pages < pintval($_GET[$page_prefix]) ? $pages : pintval($_GET[$page_prefix]);
		} else {
			$page_current = 1 ;			
		}
		if($pages > 1){
			$page_prev = $page_current - 1;//上一页
			$page_next = $page_current + 1;//下一页
			$ppage_current	= ceil( $page_current / $ppage_count);		//当前页分页
			$page_start	= ($ppage_current - 1) * $ppage_count + 1;  //分页开始
			$page_start	= $page_start < 0 ? 0 : $page_start;
			$page_end	= $ppage_current*$ppage_count+1;			//分页结束
			if($pages < $page_end ) $page_end = $pages + 1;				//分页结束处理
			$url = '?'.preg_replace('/&'.$page_prefix.'=\d+/','',$_SERVER["QUERY_STRING"]).'&';
			//view
			$page_code = 
			'<style>.page-box {height:30px;line-height:30px; text-align:'.$position.';position:relative}.page-box a{display:inline; margin:0px; margin-left:5px;text-decoration:none;color:#333}.page-box a{padding:3px 6px; border: 1px solid #dcdddd; background-color:#fff}.page-box a:hover,.page-box a.current{background-color:#004499; color:#fff}#page_to{position:absolute;display:none;right:0px;bottom:30px;border:1px solid #a0a0a0;background:#fff;padding:3px 8px}#pagenum{width:30px;margin-left:2px;vertical-align:middle}#pagebtn{border-radius:3px;}#pagebtn:hover{background:#fff;color:#333}</style>';
			$lang = array(
				'cn' => array('prev'=>'上一页','next'=>'下一页'),
				'en' => array('prev'=>'PREV','next'=>'NEXT'),
				'symbol' => array('prev'=>'<<','next'=>'>>'),
			);
			$page_code = str_replace("\t",'',$page_code);
			$page_code .= '<div id="page-box" class="page-box">';
			$page_code .= $page_current > 1 ? "<a href='{$url}{$page_prefix}={$page_prev}'><span>{$lang[$lng]['prev']}</span></a>":'';	
			if(1 < $page_start){
				$page_code .= "<a href='{$url}{$page_prefix}=1'>1</a><a href='{$url}{$page_prefix}=".($page_start-1)."'>...</a>";
			}
			for($i = $page_start;$i < $page_end ; $i++){
				$css = $i==$page_current ? "class='current'":'';
				$page_code.= "<a href='{$url}{$page_prefix}=$i' $css>$i</a>";
			}
			if($page_end != $pages + 1){
				$page_code.="<a href='{$url}{$page_prefix}=".$page_end."'>...</a><a href='{$url}{$page_prefix}=".$pages."'>$pages</a>";
			}
			$page_code .= $pages > $page_current ? "<a href='{$url}{$page_prefix}=$page_next'><span>{$lang[$lng]['next']}</span></a>" : '';	
			$page_code .= '&nbsp;&nbsp;&nbsp;&nbsp;共'.$pages.'页<span>&nbsp;&nbsp;&nbsp;&nbsp;到第</span><input type="text" onkeyup="if(event.keyCode == 13){pagebtn.click()}" id="pagenum"/><span>页</span><a id="pagebtn" onclick="window.location.href=\''.$url.$page_prefix.'=\'+document.getElementById(\'pagenum\').value">确定</a>';
			$page_code .='</div><script>if(typeof jQuery != "undefined") {$(function(){$("*").not("#pagemore,#page_to").not($("#page_to").children()).click(function(){$("#page_to").hide()})});$("#pagemore,#page_to").click(function(event){event.stopPropagation();})}</script>';
		}else{
			$page_code = '';
		}
		$xh  = ($page_current -1)*$page_count+1;
		return array(
			'records' => $count,
			'xuhao'	  => $xh,
			'page_cnt'=> $page_count,
			'pages_total'  => $pages,
			'pages'   => $page_code,
			'current' => $page_current,
			'html'	  => $page_code,
			'limit'	  => ' LIMIT '.($page_current-1) * $page_count.','
						.($page_current*$page_count > $count?($count - ($page_current-1)*$page_count):$page_count),
		);
	}
}

//转化为非负这个数
if(!function_exists('pintval')){
	function pintval($val){
		return abs(intval($val));
	}
}

/*
//example:
$pages = page::make(330,array('page_count','ppage_count','lng','position','page_prefix'));
print_r($pages);
*/
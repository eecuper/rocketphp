<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class log_db_show{
	static function show(){
		$db = mysql::get_instance();
		$appId	= ' and appid = "'.md5(APP_PATH).'" ';
		if(getg('op') == 'list'){
			$logs = $db->get_all('select date from log_content where delt = 0 '.$appId.' group by date');
			if($logs){
				$code = '<style>a{color:#333}a:hover{color:red}</style><table>';
				foreach($logs as $log){
					$code.= 
					'<tr><td width="100"><a target="_blank" href="?m=api&a=log&path='
					.$log['date'].'"> >'.$log['date']
					.' </td><td>(Requests:'.$db->get_one('count(distinct request_id)','log_content',
					'delt = 0 and date='.$log['date'].$appId).', Logs:'
					.$db->get_one('count(id)','log_content',
					'delt = 0 and date='.$log['date'].$appId)
					.')</a>&nbsp;<a href="?m=api&a=dellog&path='.$log['date'].'">X</a></td></tr>';
				}
				exit($code.'</table>');
			}else{
				exit('>>No log!');	
			}
		}else{
			$code = 
			'<html>
			<head>
			<title>'.ucfirst(APPLICATION).'日志</title>
			<style>
			body{padding-bottom:30px;margin:5px}
			p{margin:0px;padding:0px;word-break:break-all}
			a{text-decoration:none; color:blue; padding:2px 5px;}
			fieldset{margin:0px;padding:5px;padding-top:0px;}
			.r{margin:3px 0px; background-color: #ccc;border-left:3px solid #DD1100}
			.e legend b{color:#cc0000}
			.close-btn a:hover{background:gray;color:white}
			.close-btn{margin:20px 0px;}
			div.page-box{margin:-5px 60px 0px 0px;}
			#pagemore{display:none}
			</style>
			</head>
			<body>
			<div>>>Log opened ...!</div>
			';
			//292,314
			$l = preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT'])?292:314;
			if(LOG_STYLE == 2){
				$code .='
				<style>
				td{padding:0px;}
				p{margin-left:'.$l.'px;}
				i{font-style:normal;color:#000;font-weight:normal}
				.r{background:#ddd;border-left:1px solid #DD1100}
				.e{color:#cc0000;}
				.t{color:green;}
				.e,.t{float:left;padding-right:10px}
				</style>
				';
			}
			$setting = '
			<div id="setting" style="float:right; margin-left:15px; '
			.((empty($_GET['url']) && empty($_GET['content']) && empty($_GET['tag']))?'':'').'">
			Url:
			<input id="url" name="url" onkeyup="if(event.keyCode == 13){search.click()}"
			ondblclick="event.preventDefault();event.stopPropagation()"
			value ="'.(isset($_GET['url'])?$_GET['url']:'').'" />
			Content:
			<input id="contentt" name="contentt" onkeyup="if(event.keyCode == 13){search.click()}"
			ondblclick="event.preventDefault();event.stopPropagation()"
			value ="'.(isset($_GET['content'])?$_GET['content']:'').'" />
			Tag:
			<input id="tag" name="tag" onkeyup="if(event.keyCode == 13){search.click()}" 						
			ondblclick="event.preventDefault();event.stopPropagation()"
			value ="'.(isset($_GET['tag'])?$_GET['tag']:'').'"/>
			<button id="search" 
			onclick="window.location = \'?m=api&a=log&tag=\' + tag.value + 
			\'&content=\' + contentt.value + \'&url=\' + url.value ">Search</button>
			<button onclick="window.location=\'?m=api&a=log\'">All</button></div>';
			$code = str_replace(array("\t","\r","\n",),'',$code)."\n";						
			$date = getg('path') ? getg('path') : date('Ymd');
			if(($tag = getg('tag')) && $tag != 'all'){
				$ts = explode(',',$tag);
				$tag = ' and(';
				foreach($ts as $v){
					$tag .=' tag="'.$v.'" or';
				}
				$tag = substr($tag,0,-2).') ';
			}else{
				$tag = '';
			}
			if($con = getg('content')){
				$con = ' and content like "%'.$con.'%"';
			}else{
				$con = '';	
			}
			if($url = getg('url')){
				$appId2	= ' and a.appid = "'.md5(APP_PATH).'" ';
				$logs = $db->get_pages('select 
				request_id 
				from log_content  a
				left join log_request b
				on a.request_id = b.id
				where a.delt = 0 and a.date='.$date.$appId2.$tag.$con.'
				and b.url like "%'.$url.'%"
				group by request_id 
				order by request_id desc
				',100);	
			}else{
				$logs = $db->get_pages('select 
				request_id 
				from log_content 
				where delt = 0 and date='.$date.$appId.$tag.$con.'
				group by request_id 
				order by request_id desc
				',100);	
			}
			$pages = '';
			if($logs['data']){
				asort($logs['data']);
				foreach($logs['data'] as $log){
					$request =  
						$db->get_row('select * from log_request where id='.$log['request_id']);
					$code .= 
						'<p class="r"><table cellspacing="0" cellpadding="0"><tr><td>['.date('m-d H:i:s',$request['time']).']</td>'
						.'<td width="137">['.$request['ip'].']</td>'
						.'<td width="48">['.$request['method'].']</td>'
						.'<td>['.$request['url'].']</td>'
						.'</tr></table></p>'."\n";
					$logContents = 
						$db->get_all('select tag,tag_content,content
						 from log_content 
						 where delt = 0 and request_id='.$log['request_id'].$tag.$con.' 
						 order by id asc
						 ');
					foreach($logContents as $lcon){
						if($lcon['tag'] == 'debug')
							$code .= '<p>['.$lcon['content'].']</p>'."\n";
						else{
							$class = '';
							if($lcon['tag'] == 'fatal' or $lcon['tag'] == 'error')
								$class = 'class="e"';
							else
								$class = 'class="t"';
							if(LOG_STYLE == 2){
								$code .=
								'<p '.$class.'><i>[</i>'.$lcon['tag_content'].'</p><p>'.$lcon['content'].']</p>'."\n";
							}else{
								$code .= 
								'<fieldset '.$class.'><legend>'.$lcon['tag_content'].'</legend>'
								.$lcon['content'].'</fieldset>'."\n";	
							}
						}
					}
				}
				$pages = $logs['html'];
			}else{
				$code .= 'No log!';	
			}
			if(isset($_GET['vs'])){
				$code = '<pre>'.$code.'</pre>';	
			}
			$delurl = '?m=api&a=dellog'.(getg('path')?'&path='.getg('path'):'').'&tag='.getg('tag');
			$msg 	= '确定要删除'.(getg('tag')?'Tag为'.getg('tag'):'所有').'日志吗?';
			$code  .=  
			str_replace("\t",'',
			'<div class="close-btn" style="position:relative;z-index:1000">
			<div style="float:right;margin-top:3px;margin-left:10px;">
			<a onclick="if(confirm(\''.$msg.'\')){dellog(\''.$delurl.'\')}" href="javascript:;">X</a>
			<a onclick="setting.style.display=\'block\'" href="javascript:;" style="display:none" >S</a>
			</div>'.$setting.
			'</div>'.$pages.'
			<iframe style="display:none" name="saveframe" id="saveframe"></iframe>
			<script type="text/javascript">
			setTimeout(function(){document.body.scrollTop = document.body.scrollHeight+50;},50);
			document.ondblclick=function(){window.location.reload();};
			function dellog(url){
				document.getElementById("saveframe").setAttribute("src",url);
			}
			</script></body></html>');
			echo $code;
		}	
	}
}
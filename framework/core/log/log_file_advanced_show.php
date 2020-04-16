<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class log_file_advanced_show{
	static function show(){
		$logdir = APP_PATH.'/logs/';
		if(getg('op') == 'list'){
			$logs = glob($logdir.'*.log*');
			if($logs){
				$code = '<style>a{color:#333}a:hover{color:red}table{float:left;margin-right:50px;}</style><table>';
				$index = 1;
				foreach($logs as $log){
					$logfile = str_replace($logdir,'',$log);
					$code.= 
					'<tr><td width="280"><a target="_blank" href="?m=api&a=log&path='
					.base64_encode($logfile).'"> >'.$logfile.' </td><td>('.filesize($log).')</a></td><td><a target="saveframe" href="?m=api&a=dellog&path='.base64_encode($logfile).'&fl=1">X</a></td></tr>';
					if($index%45 == 0){
						$code.= '</table><table>';
					}
					$index++;
				}
				exit($code.'</table><iframe name="saveframe" style="display:none" ></iframe>');
			}else{
				exit('>>No log!');
			}
		}else{
			$l = preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT'])?292:314;
			//content
			$head = '<html><head><title>'.ucfirst(APPLICATION).'日志</title>';
			$head.= '
			<style>
			a{text-decoration:none; color:blue; padding:2px 5px;}
			a:hover{background:gray;color:white}
			p{margin:0px;padding:0px;margin-left:'.$l.'px;word-break:break-all}
			i{font-style:normal}
			.t{margin:3px 0px; background-color: #ddd;border-left:1px solid #DD1100}
			.yeah-log-tag i{color:green}
			.yeah-log-tag.error i,.yeah-log-tag.fatal i{color:#cc0000;}
			.close-btn{margin-bottom:50px;text-align:right;padding:5px 0px}
			.setting{position:absolute;right:0;top:-3px}
			</style>
			';
			$head .=  '</head><body>';
			if(($tag = getg('tag')) && $tag != 'all'){
				$head .= '<style>.yeah-log-tag{display:none}.t{display:none}</style>';
			}
			$head .='
			<div style="position:relative;" >>>Log opened ...!
			<div class="setting">
			Tag:<input id="tag" name="tag" onkeyup="if(event.keyCode == 13){search.click()}" ondblclick="event.preventDefault();event.stopPropagation()" value ="'.(isset($_GET['tag'])?$_GET['tag']:'').'"/>
			<button id="search" onclick="window.location = \'?m=api&a=log&tag=\' + tag.value">Search</button>
			<button onclick="window.location=\'?m=api&a=log\'">All</button></div></div>';
			$head = str_replace(array("\t","\r","\n"),'',$head)."\n";
			//content
			$path = getg('path')?$logdir.base64_decode(getg('path')):self::path();
			if(is_file($path)){
				$content = file_get_contents($path);
			}else{
				$content .= 'No log!';
			}
			if(isset($_GET['vs'])){
				$content = '<pre>'.$content.'</pre>';
			}
			$content = self::compress($content,1);
			if($l==292){
				$script =
				"if(!document.getElementsByClassName){  
        	document.getElementsByClassName = function(className, element){  
            var children = (element || document).getElementsByTagName('*');  
            var elements = new Array();  
            for (var i=0; i<children.length; i++){  
					var child = children[i];  
					var classNames = child.className.split(' ');  
					for (var j=0; j<classNames.length; j++){  
						if (classNames[j] == className){   
							elements.push(child);  
							break;  
						}  
					}  
				}   
				return elements;  
			};  
		}  ";
			}else{
			 $script = '';
			}
			//foot
			$foot ='
			<div class="close-btn">
			<a href="?m=api&a=dellog'.(getg('path')?'&path='.getg('path'):'').'">X</a>
			<a onclick="javascript:document.body.scrollTop = 0" href="javascript:;" >&uArr;</a>
			</div>
			<script type="text/javascript">
			'.$script;
			if(($tag = getg('tag')) && $tag != 'all'){
				$ts = explode(',',$tag);
				foreach($ts as $v){
					$foot .=
					'currentTags = document.getElementsByClassName("'.$v.'");
					len = currentTags.length;
					for(var i =0;i< len;i ++){
						try{
							currentTags[i].style.display = "block";
							document.getElementById(currentTags[i].getAttribute("p")).style.display = "block";
						}catch(e){}
					}
					';	
				}
			}
			$foot.='
			setTimeout(function(){document.body.scrollTop = document.body.scrollHeight+50;},50);
			document.ondblclick=function(){window.location.reload();};
			</script>
			</body></html>
			';
			$foot = str_replace(array("\t","\r","\n"),'',$foot);
			echo $head.$content.$foot;
		}
	}
	static function path(){
		return APP_PATH.'/logs/'.date('Ymd').'_'.substr(md5(date('Ymd').'x2~^y1t!6#$'),0,8).'.log';
	}
	//压缩,type=0为压缩,1为解压缩
	static function compress($str,$type = 0){
		if(!LOG_COMPRESS)return $str;
		$compress = array(
			'#^~0#'=>'<p class="t" id="p',
			'#^~1#'=>'"><table cellspacing="0" cellpadding="0"><tr><td>[',
			'#^~2#'=>']</td><td width="137">[',
			'#^~3#'=>']</td><td width="48">[',
			'#^~4#'=>']</td><td>[',
			'#^~5#'=>']</td><td title="',
			'#^~6#'=>'<font color="red"> more...</font>]</td>',
			'#^~7#'=>'</tr></table></p>'."\n",
			'#^~8#'=>'<p class="yeah-log-tag ',
			'#^~9#'=>'</i>&nbsp;',
			'#^~a#'=>']</td>',
			'#^~b#'=>']</p>'."\n",
		);
		if($type == 0){
			return 	str_replace(array_values($compress),array_keys($compress),$str);
		}else{
			return 	str_replace(array_keys($compress),array_values($compress),$str);
		}
	}
}
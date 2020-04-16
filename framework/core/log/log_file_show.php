<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class log_file_show{
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
					'<tr><td width="280"><a target="_blank" href="?m=api&a=log&path='.base64_encode($logfile).'"> >'
					.$logfile.' </td><td>('.filesize($log).')</a></td><td><a target="saveframe" href="?m=api&a=dellog&path='.base64_encode($logfile).'&fl=1">X</a></td></tr>';
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
			echo '<html><head><title>'.ucfirst(APPLICATION).'日志</title><body>>>Log opened ...!<br/><style>'
			,'table{border-collapse:collapse;}.t{margin:2px 0px; background-color: #ddd;border-left:1px solid #DD1100}'
			,'p{margin:0px;padding:0px;margin-left:'
			.(preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT'])?299:321).'px;}i{font-style:normal;color:green}.e i{color:#c00;}'
			,'a{text-decoration:none; color:blue; padding:2px 5px;}a:hover{background:gray;color:white}p{word-break:break-all}</style>'."\n"
			,(isset($_GET['vs'])?'<pre>':'')
			.(is_file(self::path())?file_get_contents(self::path()):'No log!')
			.(isset($_GET['vs'])?'</pre>':'')
			.'<p style="text-align:right"><a href="?m=api&a=dellog'.(getg('path')?'&path='.getg('path'):'').'">X</a></p><script>'
			,'setTimeout(function(){document.body.scrollTop = document.body.scrollHeight+50;},50);'
			,'document.ondblclick=function(){window.location.reload();}'
			,'</script></body></html>';
			exit;
		}
	}
	static function path(){
		$dir = APP_PATH.'/logs/';
		$file = empty($_GET['path'])?date('Ymd').'_'.substr(md5(date('Ymd').'x2~^y1t!6#$'),0,8).'.log':base64_decode(getg('path'));
		return $dir.$file;
	}
}
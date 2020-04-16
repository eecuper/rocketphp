<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class message {
	static function msg( $m , $refer = '',$timeout = 2){
		if($refer 	=== '')$refer = isset($_SERVER['HTTP_REFERER'])?htmlentities($_SERVER['HTTP_REFERER']):'#';
		$back  	= $refer === false?'':'<meta http-equiv="refresh" content="'.$timeout.';url='.$refer.'"/>';
		$link 	= $refer === false?'':'<p> ^-^ <a href="'.$refer.'">如果您的浏览器没有自动跳转，请点击这里。</a> !!! </p>';
		$imgpath = IMG_PATH;
		echo <<<EOT
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>提示信息</title>
		$back
		<style>
		table, tr, td, body, h1, h3, h4, div, p { margin:0px; padding:0px; font-size:12px; }
		body{height:100%;width:100%}
		table { width:100%; height:460px; font-family:"Times New Roman", Times, serif; text-align:center; background: #fff; color: #666;position:relative;top:50%;margin-top:-300px; }
		a { color:#333; text-decoration:none; }
		img { border:0; }
		h3 { width:500px; margin:10px auto 0 auto; background:#cecece; color:#fff; line-height:25px; }
		h4 { padding: 15px 0; color:#c00; font-weight:normal; }
		</style>
		</head>
		<body>
		<table>
		  <tr>
			<td>
			<div style=' width:500px; margin:auto'>
			<h1></h1>
			  <h3>提示信息</h3>
			  <h4>$m</h4>
		$link
		</div>
		</td>
		</tr>
		</table>
		</body>
	</html>
EOT;
		exit;
	}
	
	static function error_404($errmsg = ''){
		if($errmsg){
			error($errmsg);
		}
		if(defined('ERROR_PAGE') && ERROR_PAGE)
			redirect(ERROR_PAGE);
		echo <<<EOT
		<html><head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8">
		<title>您访问的网页不存在!</title>
		<style>
		body {font-family:"微软雅黑",arial,sans-serif;background:#fff; color:#000}
		ol{padding-left:20px}
		</style>
		</head>
		<body>
		<div style='margin-left:20px;'>
		<H1>404 Not Found, 您访问的网页不存在!</H1>
		<p>The requested URL was not found on this server. </p>
		<ol>    
		<li>请检查您输入的网址是否正确。</li>
		<li>确认无误有可能我们的页面正在升级或维护。</li>
		<li>您可以尝试稍后访问。</li>   
		</ol>
		</div>
		</body></html>
EOT;
		header('HTTP/1.1 404 Not Found',TRUE,404);
exit;
	}
}
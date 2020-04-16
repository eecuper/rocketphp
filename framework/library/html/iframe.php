<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class html_iframe{
	/*
	 *加载iframecontent
	 */
	public static function make($content,$style = ''){
	return <<<EOF
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">var frame_load=false;function show_frame(frame){if(frame_load){return;}frame_load=true;var innerDoc = frame.contentDocument||frame.contentWindow.document;setTimeout(function(){innerDoc.write(frame.innerHTML);if(navigator.userAgent.indexOf("MSIE")==-1){innerDoc.close();}},10)}
</script>
<iframe id="contentFrame" frameborder="0" style="width:100%;{$style}" onload="show_frame(this)">
<div id="container" style="word-wrap:break-word;word-break:break-all; overflow:hidden">
{$content}
</div>
<script type="text/javascript">if(window.innerWidth){winWidth=window.innerWidth;}else if((document.body)&&(document.body.clientWidth)){winWidth = document.body.clientWidth;}document.getElementById('container').style.width=winWidth-50+'px';window.onload=function(){parent.document.getElementById('contentFrame').style.height=document.body.scrollHeight+'px';var ln=document.images.length;if(ln>0){for(i=0;i<ln;i++){img=document.images[i];if(img.width>winWidth){img.width=winWidth-50;img.height=img.height*winWidth/img.width;}}}}
</script>
</iframe>
EOF;
}
}
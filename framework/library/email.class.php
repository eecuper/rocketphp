<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class email {
	var $smtp_port;
	var $time_out;
	var $host_name;
	var $log_file;
	var $relay_host;
	var $debug;
	var $auth;
	var $user;
	var $pass;
	var $sock;
	var $from;
	function __construct($user = '', $pwd = '' ,$from = '',$smtp = ''){
		if($user == '' or $pwd == '') exit('Email config error!');
		$this->user       = $user;
		$this->pass       = $pwd;
		$this->smtp_port  = 25;
		$this->relay_host = $smtp == ''?'smtp.'.substr($user,strpos($user,'@')+1):$smtp;
		$this->time_out   = 10; // is used in fsockopen()
		$this->auth       = TRUE; // auth
		$this->host_name  = "localhost"; // is used in HELO command
		$this->log_file   = "";
		$this->sock       = FALSE;
		$this->from		  = $from == ''?$this->user:$from.'<'.$this->user.'>';
	}
	//发送邮件,多个地址用;隔开
	public function send($option = array()){
		if(empty($option) or !is_array($option)) return '配置为空';
		$param = 
		array('to'=>'','cc'=>'','bcc'=>'','title'=>'邮件标题','content'=>'邮件内容',
		'debug'=>false,'html'=>true,'log_file'=>'');
		foreach($param as $k=>$v){
			if(!empty($option[$k]))
				$param[$k] = $option[$k];
		}
		extract($param);
		$this->debug    = $debug;
		$this->log_file = $log_file;
		return self::sendmail($to, $this->from, $title, $content, $html ? 'HTML':'TXT',$cc,$bcc);
	}
	/* Main Function */
	private function sendmail($to, $from, $subject = "", $body = "", $mailtype, $cc = "", $bcc = "", $additional_headers = "") {
		$header = "";
		$separator = ';';
		$to = trim(trim($to),$separator);
		//body组装
		$mail_from = $this->get_address ( $this->strip_comment ( $from ) );
		$body = preg_replace ( "/(^|(\r\n))(\\.)/", "\\1.\\3", $body );
		$header .= "MIME-Version:1.0\r\n";
		if ($mailtype == "HTML") {
			$header .= "Content-Type:text/html;charset=utf-8\r\n";
		}
		$header .= "To: " . $to . "\r\n";
		if ($cc != "") {
			$header .= "Cc: " . $cc . "\r\n";
		}
		$header .= "From:" . $from . "\r\n";
		$header .= "Subject: =?UTF-8?B?".base64_encode($subject)."?=\r\n";
		$header .= $additional_headers;
		$header .= "Date: " . date ( "r" ) . "\r\n";
		$header .= "X-Mailer:By Redhat (PHP/" . phpversion () . ")\r\n";
		list ( $msec, $sec ) = explode ( " ", microtime () );
		$header .= "Message-ID: <" . date ( "YmdHis", $sec ) . "." . ($msec * 1000000) . "." . $mail_from . ">\r\n";
		//地址处理		
		$TO = explode ($separator, $this->strip_comment($to));
		if ($cc != "") {
			$TO = array_merge ( $TO, explode ($separator, $this->strip_comment ( $cc ) ) );
		}
		if ($bcc != "") {
			$TO = array_merge ( $TO, explode ($separator, $this->strip_comment ( $bcc ) ) );
		}
		$errors = '';
		foreach ($TO as $rcpt_to ) {
			$rcpt_to = $this->get_address ($rcpt_to);
			if(empty($rcpt_to)){
				continue;
			}elseif(!$this->is_mail($rcpt_to)){
				$errors .= $rcpt_to.':格式不正确; ';
			}elseif(!$this->smtp_sockopen ( $rcpt_to )) {
				$errors .= $rcpt_to.':地址无效; ';
			}elseif(!$this->smtp_send ($this->host_name, $mail_from, $rcpt_to, $header, $body )) {
				$errors .= $rcpt_to.':发送失败; ';
			}
		}
		if($this->sock)fclose($this->sock);
		return empty($errors)?'success':$errors;
	}
	/* Private Functions */
	function smtp_send($helo, $from, $to, $header, $body = "") {
		if (! $this->smtp_putcmd ( "HELO", $helo )) {
			return $this->smtp_error ( "sending HELO command" );
		}
		if ($this->auth) {
			if (! $this->smtp_putcmd ( "AUTH LOGIN", base64_encode ( $this->user ) )) {
				return $this->smtp_error ( "sending HELO command" );
			}
			
			if (! $this->smtp_putcmd ( "", base64_encode ( $this->pass ) )) {
				return $this->smtp_error ( "sending HELO command" );
			}
		}
		if (! $this->smtp_putcmd ( "MAIL", "FROM:<" . $from . ">" )) {
			return $this->smtp_error ( "sending MAIL FROM command" );
		}
		if (! $this->smtp_putcmd ( "RCPT", "TO:<" . $to . ">" )) {
			return $this->smtp_error ( "sending RCPT TO command" );
		}
		if (! $this->smtp_putcmd ( "DATA" )) {
			return $this->smtp_error ( "sending DATA command" );
		}
		if (! $this->smtp_message ( $header, $body )) {
			return $this->smtp_error ( "sending message" );
		}
		if (! $this->smtp_eom ()) {
			return $this->smtp_error ( "sending <CR><LF>.<CR><LF> [EOM]" );
		}
		if (! $this->smtp_putcmd ( "QUIT" )) {
			return $this->smtp_error ( "sending QUIT command" );
		}
		return TRUE;
	}
	function smtp_sockopen($address) {
		if ($this->relay_host == "") {
			return $this->smtp_sockopen_mx ( $address );
		} else {
			return $this->smtp_sockopen_relay ();
		}
	}
	function smtp_sockopen_relay() {
		$this->log_write ( "Trying to " . $this->relay_host . ":" . $this->smtp_port . "\n" );
		$this->sock = @fsockopen ( $this->relay_host, $this->smtp_port, $errno, $errstr, $this->time_out );
		if (! ($this->sock && $this->smtp_ok ())) {
			$this->log_write ( "Error: Cannot connenct to relay host " . $this->relay_host . "\n" );
			$this->log_write ( "Error: " . $errstr . " (" . $errno . ")\n" );
			return FALSE;
		}
		$this->log_write ( "Connected to relay host " . $this->relay_host . "\n" );
		return TRUE;
	}
	function smtp_sockopen_mx($address) {
		$domain = preg_replace ( "/^.+@([^@]+)$/", "\\1", $address );
		if (! @getmxrr ( $domain, $MXHOSTS )) {
			$this->log_write ( "Error: Cannot resolve MX \"" . $domain . "\"\n" );
			return FALSE;
		}
		foreach ( $MXHOSTS as $host ) {
			$this->log_write ( "Trying to " . $host . ":" . $this->smtp_port . "\n" );
			$this->sock = @fsockopen ( $host, $this->smtp_port, $errno, $errstr, $this->time_out );
			if (! ($this->sock && $this->smtp_ok ())) {
				$this->log_write ( "Warning: Cannot connect to mx host " . $host . "\n" );
				$this->log_write ( "Error: " . $errstr . " (" . $errno . ")\n" );
				continue;
			}
			$this->log_write ( "Connected to mx host " . $host . "\n" );
			return TRUE;
		}
		$this->log_write ( "Error: Cannot connect to any mx hosts (" . implode ( ", ", $MXHOSTS ) . ")\n" );
		return FALSE;
	}
	function smtp_message($header, $body) {
		fputs ( $this->sock, $header . "\r\n" . $body );
		$this->smtp_debug ( "> " . str_replace ( "\r\n", "\n" . "> ", $header . "\n> " . $body . "\n> " ) );
		return TRUE;
	}
	function smtp_eom() {
		fputs ( $this->sock, "\r\n.\r\n" );
		$this->smtp_debug ( ". [EOM]\n" );
		return $this->smtp_ok ();
	}
	function smtp_ok() {
		$response = str_replace ( "\r\n", "", fgets ( $this->sock, 512 ) );
		$this->smtp_debug ( $response . "\n" );
		if (! preg_match ( "/^[23]/", $response )) {
			fputs ( $this->sock, "QUIT\r\n" );
			fgets ( $this->sock, 512 );
			$this->log_write ( "Error: Remote host returned \"" . $response . "\"\n" );
			return FALSE;
		}
		return TRUE;
	}
	function smtp_putcmd($cmd, $arg = "") {
		if ($arg != "") {
			if ($cmd == "")
				$cmd = $arg;
			else
				$cmd = $cmd . " " . $arg;
		}
		fputs ( $this->sock, $cmd . "\r\n" );
		$this->smtp_debug ( "> " . $cmd . "\n" );
		return $this->smtp_ok ();
	}
	function smtp_error($string) {
		$this->log_write ( "Error: Error occurred while " . $string . ".\n" );
		return FALSE;
	}
	function log_write($message) {
		if(preg_match('/^Error/',$message)){
			if(function_exists('logd')){
				log::d('','Send email error: '.$message);	
			}else{
				file_put_contents('email.error.log',$message,FILE_APPEND);
			}	
		}
		$this->smtp_debug ( $message );
		if ($this->log_file == "") {
			return TRUE;
		}
		$message = date( "Y-m-d H:i:s " ).get_current_user()."[".getmypid ()."]: ".$message;
		file_put_contents('email.send.log',$message,FILE_APPEND);
		return TRUE;
	}
	function strip_comment($address) {
		$comment = "/\\([^()]*\\)/";
		while ( preg_match ( $comment, $address ) ) {
			$address = preg_replace ( $comment, "", $address );
		}
		return $address;
	}
	function get_address($address) {
		$address = preg_replace ( "/([ \t\r\n])+/", "", $address );
		$address = preg_replace ( "/^.*<(.+)>.*$/", "\\1", $address );
		return $address;
	}
	function smtp_debug($message) {
		if ($this->debug) {
			echo $message . "<br>";
		}
	}
	//验证
	public function is_mail($email){
		return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email);
	}
	function get_attach_type($image_tag) { //
		$filedata = array ();
		$img_file_con = fopen ( $image_tag, "r" );
		unset ( $image_data );
		while ( $tem_buffer = AddSlashes ( fread ( $img_file_con, filesize ( $image_tag ) ) ) )
			$image_data .= $tem_buffer;
		fclose ( $img_file_con );
		$filedata ['context'] = $image_data;
		$filedata ['filename'] = basename ( $image_tag );
		$extension = substr ( $image_tag, strrpos ( $image_tag, "." ), strlen ( $image_tag ) - strrpos ( $image_tag, "." ) );
		switch ($extension) {
			case ".gif" :
				$filedata ['type'] = "image/gif";
				break;
			case ".gz" :
				$filedata ['type'] = "application/x-gzip";
				break;
			case ".htm" :
				$filedata ['type'] = "text/html";
				break;
			case ".html" :
				$filedata ['type'] = "text/html";
				break;
			case ".jpg" :
				$filedata ['type'] = "image/jpeg";
				break;
			case ".tar" :
				$filedata ['type'] = "application/x-tar";
				break;
			case ".txt" :
				$filedata ['type'] = "text/plain";
				break;
			case ".zip" :
				$filedata ['type'] = "application/zip";
				break;
			default :
				$filedata ['type'] = "application/octet-stream";
				break;
		}
		return $filedata;
	}
}
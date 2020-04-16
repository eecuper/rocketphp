<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
//支持读写分离与分布式部署,仅支持单库操作,一个主,多从,从为随机读取
//第一个为主server
defined('ISROCKET') or exit('Access denied!');
define('DB_DRIVER',		'mysqli');
define('DB_HOST', 		'127.0.0.1');
define('DB_USER', 		'root');
define('DB_PWD', 		'');
define('DB_NAME', 		'');
define('DB_PREFIX', 	'');
define('DB_CHARSET', 	'utf8');
define('DB_RW_SEPARATE',0);

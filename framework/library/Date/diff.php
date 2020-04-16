<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class date_diff {
	//计算时间间隔
	public static function time($time ,$start = 1,$offset = 5,$now = 0) {
        $chunks = array(array(31536000,'年'),array(2592000,'月'),array(86400,'天'),array(3600 ,'小时'),array(60,'分钟'),array(1,'秒'));
        $df		= (is_numeric($time)?$time:strtotime($time)) - ($now == 0 ? time():$now);
		$diff 	= abs($df);
		$start 	= $start - 1;
		$end    = $start + $offset;
		$since 	= '';
        for($i = $start;$i <= $end;$i++) {
            if( $diff >= $chunks[$i][0]) {
                $num    =  floor($diff/$chunks[$i][0]);
                $since .= sprintf('%d'.$chunks[$i][1],$num);
                $diff =  (int)($diff-$chunks[$i][0]*$num);
            }
       }
	   if($since == ''){$since = '1'.$chunks[$i-1][1];}
       return $df < 0 ? '-' . $since : $since;
    }
	//计算日期间隔
	public static function date($time, $elaps = "d") {
		$seconds = (is_numeric($time) ? $time : strtotime($time)) - time();
        switch ($elaps) {
            case "y"://转换成年
                return sprintf('%.2f',$seconds/(365*24*60*60));
                break;
            case "m"://转换成月
                return sprintf('%.2f',$seconds/(30*24*60*60));
                break;
			case "w"://转换成星期
                return  sprintf('%.2f',$seconds/(7*24*60*60));
                break;
            case "d"://转换成天
                return  sprintf('%.2f',$seconds/(24*60*60));
                break;
            case "h"://转换成小时
                return  sprintf('%.2f',$seconds/(60*60));
                break;
            case "i"://转换成分钟
                return  sprintf('%.2f',$seconds/60);
                break;
            case "s"://转换成秒
                return  $seconds;
                break;
        }
    }
}
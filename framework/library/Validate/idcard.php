<?php
/**
 * RocketPhp3.0 - 简单高效的php框架
 * @Copyright weixin:Alexyu01 QQ:40989411
 * @License Please contact the author before using it.
 * @Last-Modified 2019-12-31 14:07
 * @Author weixin:Alexyu01 QQ:40989411@qq.com
 */
defined('ISROCKET') or exit('Access denied!');
class validate_idcard{
	//身份证检查
	public static function is_idcard($value){
		if(empty($value))
			return false;
		if(self::is_mainland_card($value) or self::is_hong_kong_card($value) or self::is_taiwan_card($value) or self::is_macao_card($value) or self::is_passport($value)){
			return TRUE;
		}else{
			return false;
		}
	}
	// 计算身份证校验码，根据国家标准GB 11643-1999 
	private static function idcard_verify_number($idcard_base){ 
		if(strlen($idcard_base) != 17){
			return false; 
		} 
		//加权因子 
		$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); 
		//校验码对应值 
		$verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); 
		$checksum = 0; 
		for ($i = 0; $i < strlen($idcard_base); $i++){ 
			$checksum += substr($idcard_base, $i, 1) * $factor[$i]; 
		} 
		$mod = $checksum % 11; 
		$verify_number = $verify_number_list[$mod]; 
		return $verify_number; 
	}
	// 将15位身份证升级到18位 
	private static function idcard_15to18($idcard){ 
		if (strlen($idcard) != 15){ 
			return false; 
		}else{
			if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){ 
				$idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9); 
			}else{ 
				$idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9); 
			} 
		} 
		$idcard = $idcard . self::idcard_verify_number($idcard); 
		return $idcard; 
	}
	// 18位身份证校验码有效性检查 
	private static function idcard_checksum18($idcard){ 
		if (strlen($idcard) != 18){ return false; }
		$idcard_base = substr($idcard, 0, 17); 
		if (self::idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){ 
			return false; 
		}else{ 
			return $idcard; 
		} 
	}
	//是否中国大陆身份证号
    public static function is_mainland_card($card_no){
        $card_no = strlen($card_no) == 15 ? self::idcard_15to18($card_no) : $card_no;
        return self::idcard_checksum18($card_no);
    }
	// 是否中国香港身份证号
    public static function is_hong_kong_card($card_no){
        $card_no = str_replace(array('（', '）'), array('(', ')'), $card_no);
        $pattern = '/^[a-z]\d{2,7}\([\da]\)$/i';
        $match = preg_match($pattern, $card_no);
        if($match){
            $alpha = strtolower($card_no[0]);
            $first = ord($alpha) - 96;
            $sum = $first*8 + $card_no[1]*7 + $card_no[2]*6 + $card_no[3]*5 + $card_no[4]*4 + $card_no[5]*3 + $card_no[6]*2;
            if(intval($card_no[8]) == 11 - $sum % 11){
                return true;
            }
        }
        return false;
    }
    //是否中国台湾身份证号
    public static function is_taiwan_card($card_no){
        $pattern = '/^[a-z]\d{9}$/i';
        $match = preg_match($pattern, $card_no);
        if($match){
            $sum = $card_no[1]*8 + $card_no[2]*7 + $card_no[3]*6 + $card_no[4]*5 + $card_no[5]*4 + $card_no[6]*3 + $card_no[7]*2 + $card_no[8]*1 + $card_no[9];
            if(intval($card_no[9]) == $sum % 10){
                return true;
            }
        }
        return false;
    }
    public static function is_macao_card($card_no){
        $card_no = str_replace(array('（', '）'), array('(', ')'), $card_no);
        $pattern = '/^(?:1|5|7)\d{7}\(\d\)$/i';
        $match = preg_match($pattern, $card_no);
        return $match ? true : false;
    }
    //是否护照号
    public static function is_passport($card_no){
        if(strlen($card_no) == 9){
            return true;
        }
        return false;
    }
}
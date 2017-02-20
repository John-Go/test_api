<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Travel_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->CI->load->model('_yps/config/lib_model');
	}

	public function paramNummCheck($val1, $val2, $arr){
		if(is_array($arr)){
			if(isset($arr[$val1]))
				return $val1;
			else
				return $val2;
		}else
			return ($val1) ? $val1 : $val2;
	}

	public function travelExpense($expenseMin, $expenseMax){ // 
		if ($expenseMin > 0 && $expenseMax > 0){ 
			if($expenseMin == $expenseMax)
				return number_format($expenseMin)." 원";
			else if($expenseMin == 0 && $expenseMax>0)
				return number_format($expenseMax)." 원";
			else if($expenseMax == 0 && $expenseMin>0)
				return number_format($expenseMin)." 원";
			else
				return number_format($expenseMin)."원 ~ ".number_format($expenseMax)." 원";
		}

		return "";
	}

	public function travelPduParkCheck($val){
		if(!strcmp($val,"F")) return '무료주차';
		if(!strcmp($val,"S")) return '유료주차';
		return '주차불가';
	}

	public function themeInfo($mpsIdx){ // 테마정보 노출

		$result = $this->CI->lib_model->themeInfo($mpsIdx);

		$str = array();
		foreach ($result as $row) {
			$str[] = $row['mtName'];
		}
		
		$str = implode(', ',$str);
		
		return $str;
	}

	public function travelThemeInfo($idx){ // 테마정보 노출

		return $this->CI->lib_model->travelThemeInfo($idx);

	}

	public function travelImageCount($idx){ // 이미지 노출

		$result	= $this->CI->lib_model->travelImageCount($idx);
		
		$imageNum = 0;
		foreach( $result as $row) {
			for($i=1;$i<=100;$i++){
				if(strcmp($row['dniiFileName'.$i],'NoFile') && $row['dniiFileName'.$i])
					$imageNum++;
			}
		}

		return $imageNum;

	}

	function get_cate_limit($ca_code) {
		$code_exp = explode('.', $ca_code);

		if (!isset($code_exp[1]))
			$limit_code = $ca_code + 1;
		else {
			$code_ori = substr($code_exp[1], 0, -3);
			$code_num = substr($code_exp[1], -3) + 1;
			$code_plus = str_repeat('0', 3-strlen($code_num)).$code_num;
			$limit_code = $code_exp[0].'.'.$code_ori.$code_plus;
		}

		return $limit_code;
	}
}

?>
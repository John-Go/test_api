<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Cate_count {
	private $gt;
	private $lt;
	private $theme;
	private $sublen;
	private $tmain;
	private $theme_main;
	private $CI;

	function __construct() {
		$this->CI	= &get_instance();
		$this->CI->load->model('_yps/travel/travel_model');
		//$this->CI->load->model('_yps/travel/ynj_model');
		//$this->CI->load->model('_yps/travel/ssan_model');
	}

	public function getCategoryCount($param=array()) {
		extract($param); // area, theme, gt, lt
		# 하위 분류 범위
		if (!$this->gt) $this->gt = 3;
		if (!$this->lt) $this->lt = 6; // $gt + 3;
		if ($this->theme) {
			list($tmain, $tsub) = explode('.', $this->theme);
			$tmain = $tsub ? $theme : $tmain.'.'; // .(점)이 중요
			$this->sublen = strlen($tsub);
		}
		$this->sublen += 3;
		
		# 테마 코드별 이름 출력

		$cateInfo	= $this->CI->travel_model->getCateInfo($this->tmain,$this->gt,$this->lt);
		
		$theme_main = $theme_sub = array();		
		foreach( $cateInfo as $row ) {
			$ca_code = $row['ca_code'];
			
			list($main, $sub) = explode('.', $ca_code);
			$i = $main.'.'.substr($sub, 0, $this->sublen);
			
			if (strlen($sub) == $this->sublen) {
				$theme_main[$main][$ca_code]['name'] = $row['ca_name'];
				$theme_main[$main][$ca_code]['count'] = 0;
			}
			else
				$theme_sub[$i][$ca_code]['name'] = $row['ca_name'];
		}

		# 해당 지역에 등록된 여행지 키값
		$areaKeyInfo	= $this->CI->travel_model->getCateTravelKey('D',$area);
		$ci_idxs = array();
		foreach( $areaKeyInfo as $row ) {
			$ci_idxs[] = $row['ci_idx'];
		}

		# 등록된 여행지 키값으로 테마 키값 검색
		$ciInfo	= $this->CI->travel_model->getCateTravelThemeKdy($this->sublen,$this->tmain,$this->gt,$this->lt, $ci_idxs);

		$travel_total = 0;
		foreach( $ciInfo as $row ) {
			$ca_code = $row['mcode'];
			if ($ca_code == '8.000')
				$ca_code = '8.001'; // MySQL TRUNCATE() 값이 이상함

			list($main, $sub) = explode('.', $ca_code);
			$i = $main.'.'.substr($sub, 0, $this->sublen);

			$theme_main[$main][$i]['count'] = $row['count'];
			$travel_total += $row['count'];
		}
		
		unset($ciInfo);
		unset($ci_idxs);

		# 해당 지역에 등록된 호텔 키값		
		/*$areaKeyInfo	= $this->CI->travel_model->getCateTravelKey('H',$area);
		$ci_idxs = array();
		foreach( $areaKeyInfo as $row ) {
			$ci_idxs[] = $row['ci_idx'];
		}

		# 등록된 호텔 키값으로 테마 키값 검색
		$row	= $this->CI->ynj_model->getHotelInfoCount($ci_idxs);
		$theme_main["9"]["9.001"]['count'] = $row;

		$travel_total += $row;

		unset($areaKeyInfo);
		unset($ci_idxs);

		
		# 해당 지역에 등록된 모텔 키값		
		$areaKeyInfo	= $this->CI->travel_model->getCateTravelKey('M',$area);
		$ci_idxs = array();
		foreach($areaKeyInfo as $row) {
			$ci_idxs[] = $row['ci_idx'];
		}

		# 등록된 여행지 키값으로 테마 키값 검색
		$row	= $this->CI->ynj_model->getMotelInfoCount($ci_idxs);
		$theme_main["9"]["9.003"]['count'] = $row['count'];

		$travel_total += $row['count'];

		unset($areaKeyInfo);
		unset($ci_idxs);


		# 해당 지역에 등록된 여관 키값		
		$areaKeyInfo	= $this->CI->travel_model->getCateTravelKey('Y',$area);
		$ci_idxs = array();
		foreach($areaKeyInfo as $row) {
			$ci_idxs[] = $row['ci_idx'];
		}

		$row	= $this->CI->ssan_model->getSsanmotelInfoCount($ci_idxs);
		$theme_main["9"]["9.004"]['count'] = $row['count'];

		$travel_total += $row['count'];

		unset($areaKeyInfo);
		unset($ci_idxs);*/
		//echo number_format($travel_total);
		return number_format($travel_total);
		//return array($travel_total, $theme_main, $theme_sub);
	}
}
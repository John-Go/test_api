<?php

class Main_lol_list extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->config('yps/_constants');
		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
		$this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한


		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key가 없을경우

		
		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);

		$offset = ($page - 1) * $limit;

		$result = $this->pension_model->lolBannerList(array(
															'idx'=>$idx,
															'page'=>$page,
															'limit'=>$limit,
															'offset'=>$offset
														));



		if(!$result['count'])
			$this->error->getError('0005');	// 정보가 없을경우



		$banner = $this->pension_model->lolBannerBanner($idx);

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $result['count']."";
		$ret['image'] = "http://img.yapen.co.kr/pension/mobile/".$banner['amlbFilename'];

		// ******************************************** 임시정보 *************************************************************
		// ******************************************** 임시정보 *************************************************************
		// ******************************************** 임시정보 *************************************************************

		$no = 0;
		foreach ($result['query'] as $row) {
			//주소를 시/구/군 으로 자름
			$addr = @explode(' ',$row['mpsAddr1']);
			if( isset( $addr[0] ) && isset( $addr[1] ) ) $addr = $addr[0].' '.$addr[1];
			else $addr = $row['mpsAddr1'];

			$pensionPriceInfo = $this->pension_model->pensionMinPrice($row['mpIdx']);		

			$ret['lists'][$no]["idx"] = $row['mpIdx'];			// 펜션키
			$ret['lists'][$no]["image"] = 'http://img.yapen.co.kr/pension/etc/'.$row['mpIdx'].'/'.$row['ppbImage'];		// 이미지경로
			$ret['lists'][$no]["image_cnt"]	= $this->pension_model->pensionImageCount( $row['mpIdx'] );
			$ret['lists'][$no]["location"] = $addr;	// 지역정보
			$ret['lists'][$no]["name"] = $row['mpsName'];		// 펜션명
			$ret['lists'][$no]["content"] = $this->pension_lib->themeInfo($row['mpsIdx']);	// 테마정보
			$ret['lists'][$no]["price"] = $pensionPriceInfo->minPrice;	// 이용요금
			$ret['lists'][$no]["review"] = $this->basket_model->getPensionBasketCountByMpIdx($row['mpIdx']);					// 리뷰
			$ret['lists'][$no]["sales"] = $pensionPriceInfo->maxSalePercent;			// 세일요금
			$ret['lists'][$no]["reserve"] = $row['ppbReserve'];			// 예약방식
			$ret['lists'][$no]["eventFlag"] = $row['ppbEventFlag'];
			

			$no++;
		}

		echo json_encode( $ret );
	}
}
?>
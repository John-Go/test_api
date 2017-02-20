<?php
###################################################################################################
# 펜션 인기지역 리스트																																						#
###################################################################################################
class Main_loc_list extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');        
        $this->load->config('yps/_constants');
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key가 없을경우

		// $this->error->getError('0005');	// 정보가 없을경우
        
		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);
        $limit = 20;
		$offset = ($page - 1) * $limit;

		$result = $this->pension_model->locBannerList(array(
															'idx'=>$idx,
															'page'=>$page,
															'limit'=>$limit,
															'offset'=>$offset
														));

		if(!$result['count'])
			$this->error->getError('0005');	// 정보가 없을경우

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $result['count']."";

		$no = 0;
		foreach ($result['obj']->result_array() as $row) {
			//주소를 시/구/군 으로 자름
			$addr = @explode(' ',$row['mpsAddr1']);
			if( isset( $addr[0] ) && isset( $addr[1] ) ) $addr = $addr[0].' '.$addr[1];
			else $addr = $row['mpsAddr1'];

			$ret['lists'][$no]["idx"]		= $row['mpIdx'];			// 펜션키
			$ret['lists'][$no]["image"]		= 'http://img.yapen.co.kr/pension/etc/'.$row['mpIdx'].'/'.$row['ppbImage'];		// 이미지경로
			$ret['lists'][$no]["location"]	= $addr;	// 지역정보
			$ret['lists'][$no]["name"]		= $row['mpsName'];		// 펜션명
			$ret['lists'][$no]["content"]	= $this->pension_lib->themeInfo($row['mpsIdx']);	// 테마정보
			$ret['lists'][$no]["reserve"]   = $row['ppbReserve'];
			
		    $priceArray = $this->pension_model->pensionMinPrice($row['mpIdx']);
            $ret['lists'][$no]["price"]     = (string)$priceArray->minPrice."원";   // 이용요금
		    $ret['lists'][$no]["review"] = (string)$this->basket_model->getPensionBasketCountByMpIdx($row['mpIdx']);
            $ret['lists'][$no]["sales"]     = (string)$priceArray->maxSalePercent;
            
				
			

			$no++;
		}
		
		echo json_encode( $ret );
	}
}
?>
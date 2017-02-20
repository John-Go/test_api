<?php
/*

가보고싶어요 개발

*/
class Travel_read extends CI_Controller {
	function __construct() {
		parent::__construct();

//		$CI =& get_instance();
//		$CI->dbHTS = $this->load->database('hts', TRUE);

		$this->load->library('pension_lib');
		$this->load->model('_yps/travel/travel_model');
		define('IMG_PATH', 'http://img.yapen.co.kr');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key가 없을경우

		$infoResult = $this->travel_model->travelGetInfo($idx); // 여행정보

		if( !$infoResult->num_rows() )
			$this->error->getError('0005');	// 정보가 없을경우

		$infoRow = $infoResult->row_array();

		$ret = array();
		$ret['status'] = 1;
		$ret['failed_message'] = "";

		$ret['info']['name'] = $infoRow['dniTitle'];						// 여행지명
		$ret['info']['address'] = $infoRow['dniSi'].' '.$infoRow['dniGugun'].' '.$infoRow['dniAdress'];					// 주소
		$ret['info']['theme'] = $this->travel_model->travelThemes( $idx );						// 테마명
		$ret['info']['tel'] = $infoRow['dniTel'];							// 전화번호
		$ret['info']['business'] = $infoRow['dniBusinessTime'];		// 운영시간
		$ret['info']['expense'] = $this->pension_lib->travelExpense($infoRow['dniExpenseMin'],$infoRow['dniExpenseMax']);		// 예산
		$ret['info']['parking'] = $infoRow['dniParking'];				// 주차
		$ret['info']['dayoff'] = $infoRow['dniDayOff'];				// 휴일
		$ret['info']['homepage'] = $infoRow['dniHomepage'];				// 홈페이지
		$ret['info']['content'] = $infoRow['dniiContent'];	// 업체소개
		$ret['info']['tip_info'] = $infoRow['dniiTip'];	// TIP
		$ret['info']['reporter'] = $infoRow['dniReporter'];				// 리포터한마디
		$ret['info']['latitude'] = $infoRow['dniGoogleY'];					// 위도
		$ret['info']['longitude'] = $infoRow['dniGoogleX'];					// 경도


		$ret['info']['basket_num'] = $this->travel_model->travelTipCount($idx).'';						// 가보고싶어요 갯수


		// ********************************************* 사진정보 **************************************************

		$arrayImages = array();
		$checkNum = 0;
		for($i=1;$i<=100;$i++){
			if(strcmp($infoRow['dniiFileName'.$i],'NoFile') && $infoRow['dniiFileName'.$i]){
				$arrayImages[$checkNum] = $infoRow['dniiFileName'.$i];
				$checkNum++;
			}

			if($checkNum == 5)
				break;
		}

		$ret['info']["images"]['count'] = $checkNum.'';

		$imgNum = 0;
		for ($i=0; $i<sizeof($arrayImages); $i++) {
			$ret['info']['images']['lists'][$imgNum]["image"] = $arrayImages[$i];
			$imgNum++;
		}

		// ********************************************* 사진정보 **************************************************


		// ********************************************* 팁정보 **************************************************
		$tipListResult = $this->travel_model->tipLists($idx, 0, 2); // 팁
		$ret['info']["tip"]['count'] = $tipListResult['count'].'';

		$tipNum = 0;
		foreach ($tipListResult['query'] as $row) {

			$ret['info']["tip"]['lists'][$tipNum]['tip_idx'] = $row['ttIdx'];
			$ret['info']["tip"]['lists'][$tipNum]['tip_name'] = $row['ttName'];
			$ret['info']["tip"]['lists'][$tipNum]['tip_date'] = $row['ttRegDate'];
			$ret['info']["tip"]['lists'][$tipNum]['tip_content'] = $row['ttContent'];
			$tipNum++;
		}
		// ********************************************* 팁정보 **************************************************

		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}
}
?>
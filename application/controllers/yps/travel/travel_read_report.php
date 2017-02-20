<?php

class Travel_read_report extends CI_Controller {
	function __construct() {
		parent::__construct();

//		$CI =& get_instance();
//		$CI->dbHTS = $this->load->database('hts', TRUE);

		$this->load->library('pension_lib');
		$this->load->model('_yps/travel/travel_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key가 없을경우

		$result = $this->travel_model->travelGetInfo($idx); // 여행정보

		if( !$result->num_rows() )
			$this->error->getError('0005');	// 정보가 없을경우

		$row = $result->row();


		$ret = array();
		$ret['status'] = 1;
		$ret['failed_message'] = "";

		$ret['info']['name'] = $row->mpsName;	// 펜션명
		$ret['info']['address'] = $row->mpsAddr1;					// 주소
	
		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}

	function insert() {

		$mpIdx = $this->input->post('mpIdx');
		$mbIdx = $this->input->post('mbIdx');
		$trName = $this->input->post('trName');
		$trTravelName = $this->input->post('trTravelName');
		$trTravelAddress = $this->input->post('trTravelAddress');
		$trContent = $this->input->post('trContent');

/*
<form name="form" method="post" action="/yps/travel/travel_read_report/insert?key=a3b1a551515fb16937f16fcb47e99b4f">

펜션키 : <input type="text" name="mpIdx" value="1"><br />
회원키 : <input type="text" name="mbIdx" value="1"><br />
작성자 : <input type="text" name="trName" value="작성자"><br />
펜션명 : <input type="text" name="trTravelName" value="펜션명"><br />
펜션주소 : <input type="text" name="trTravelAddress" value="펜션주소"><br />
상세내용 : <input type="text" name="trContent" value="상세내용"><br />

<input type="submit" value="입력">
</form>
*/

		if(!$mpIdx || !$mbIdx || !$trName || !$trTravelName || !$trTravelAddress || !$trContent)
			$this->error->getError('0006');	// Key가 없을경우

		$result = $this->travel_model->reportInsert(array(
												"mpIdx"=>$mpIdx,
												"mbIdx"=>$mbIdx,
												"trName"=>$trName,
												"trTravelName"=>$trTravelName,
												"trTravelAddress"=>$trTravelAddress,
												"trContent"=>$trContent
		));
	

		$ret = array();

		if($result){
			$ret['status'] = 1;
			$ret['failed_message'] = "";
		}else{
			$ret['status'] = 0;
			$ret['failed_message'] = "등록실패";
		}


		echo json_encode( $ret );


/*

*/
	}
}
?>
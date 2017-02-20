<?php

class Pension_read_report extends CI_Controller {
	function __construct() {
		parent::__construct();

//		$CI =& get_instance();
//		$CI->dbHTS = $this->load->database('hts', TRUE);

		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key가 없을경우

		$infoResult = $this->pension_model->pensionGetInfo($idx); // 펜션정보

		if( !$infoResult->num_rows() )
			$this->error->getError('0005');	// 정보가 없을경우

		$infoRow = $infoResult->row_array();

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";

		$ret['info']['name'] = $infoRow['mpsName'];	// 펜션명
		$ret['info']['address'] = $infoRow['mpsAddr1']." ".$infoRow['mpsAddr2'];					// 주소
	
		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}

	function insert() {

		$mpIdx = $this->input->post('mpIdx');
		$mbIdx = $this->input->post('mbIdx');
		$prName = $this->input->post('prName');
		$prPensionName = $this->input->post('prPensionName');
		$prPensionAddress = $this->input->post('prPensionAddress');
		$prContent = $this->input->post('prContent');

/*
<form name="form" method="post" action="/yps/pension/pension_read_report/insert?key=a3b1a551515fb16937f16fcb47e99b4f">

펜션키 : <input type="text" name="mpIdx" value="1"><br />
회원키 : <input type="text" name="mbIdx" value="1"><br />
작성자 : <input type="text" name="prName" value="작성자"><br />
펜션명 : <input type="text" name="prPensionName" value="펜션명"><br />
펜션주소 : <input type="text" name="prPensionAddress" value="펜션주소"><br />
상세내용 : <input type="text" name="prContent" value="상세내용"><br />

<input type="submit" value="입력">
</form>
*/


		if(!$mpIdx || !$mbIdx || !$prPensionName || !$prContent){
			$this->error->getError('0006');	// Key가 없을경우
		}

        if(!$prName){
            $prName = "";
        }

		$result = $this->pension_model->reportInsert(array(
												"mpIdx"=>$mpIdx,
												"mbIdx"=>$mbIdx,
												"prName"=>$prName,
												"prPensionName"=>$prPensionName,
												"prPensionAddress"=>$prPensionAddress,
												"prContent"=>$prContent
		));
	
		$ret = array();

		if($result){
			$ret['status'] = 1;
			$ret['failed_message'] = "";
		}else{
			$ret['status'] = 0;
			$ret['failed_message'] = "등록실패";
		}


		$ret['status'] = 1;
		$ret['failed_message'] = "";

		echo json_encode( $ret );
	}
}
?>
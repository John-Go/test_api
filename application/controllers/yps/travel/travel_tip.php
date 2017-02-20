<?php
/*

가보고싶어요 개발

*/
class Travel_tip extends CI_Controller {
	function __construct() {
		parent::__construct();

//		$CI =& get_instance();
//		$CI->dbHTS = $this->load->database('hts', TRUE);

		$this->load->library('pension_lib');
		$this->load->model('_yps/travel/travel_model');
	}

	function index() {

/*
<form name="form" method="post" action="/yps/travel/travel_tip?key=a3b1a551515fb16937f16fcb47e99b4f">
mpIdx <input type="text" name="mpIdx"><br />
mbIdx <input type="text" name="mbIdx"><br />
ttName <input type="text" name="ttName"><br />
ttSector <input type="text" name="ttSector" value="S01"><br />
ttTravelName <input type="text" name="ttTravelName" ><br />
ttContent <input type="text" name="ttContent" ><br />

<input type="submit" value="팁등록">
</form>
*/
		$mpIdx = $this->input->post('mpIdx');
		$mbIdx = $this->input->post('mbIdx');
		$ttSector = $this->input->post('ttSector');
		$ttName = $this->input->post('ttName');
		$ttTravelName = $this->input->post('ttTravelName');
		$ttContent = $this->input->post('ttContent');

		if( strlen(trim($ttName)) == 0 )
		{
			$ttName = '회원'.rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
		}
		
		
		if(!$mpIdx || !$mbIdx || !$ttName || !$ttTravelName || !$ttContent)
			$this->error->getError('0006');	// Key가 없을경우

		$row = $this->travel_model->tipInsert(array(
								'mpIdx'=>$mpIdx,
								'mbIdx'=>$mbIdx,
								'ttSector'=>$ttSector,
								'ttName'=>urldecode($ttName),
								'ttTravelName'=>urldecode($ttTravelName),
								'ttContent'=>urldecode($ttContent)
								));

		$ret = array();

		if($row){
			$ret['status'] = "1";
			$ret['failed_message'] = '';
		}else{
			$ret['status'] = "0";
			$ret['failed_message'] = '등록오류';
		}

		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}

	public function info(){
		$ttIdx = $this->input->get('ttIdx');
		$mbIdx = $this->input->get('mbIdx');
		if( !$ttIdx || !$mbIdx ) 
			$this->error->getError('0006');	// Key가 없을경우		

		$result = $this->travel_model->tipInfo($ttIdx,$mbIdx);

		if(!$result)
			$this->error->getError('0005');	// 정보가 없을경우

		$ret = array();
		$ret['status'] = 1;
		$ret['failed_message'] = "";

		$ret['info']['sector'] = $result['ttSector'];	
		$ret['info']['name'] = $result['ttName'];	
		$ret['info']['travel'] = $result['ttTravelName'];	
		$ret['info']['content'] = $result['ttContent'];	

		echo json_encode( $ret );
	}


	public function update() {

/*

<form name="form" method="post" action="/yps/travel/travel_tip/update?key=a3b1a551515fb16937f16fcb47e99b4f">
ttIdx <input type="text" name="ttIdx"><br />
mbIdx <input type="text" name="mbIdx"><br />
ttName <input type="text" name="ttName"><br />
ttSector <input type="text" name="ttSector" value="S01"><br />
ttTravelName <input type="text" name="ttTravelName" ><br />
ttContent <input type="text" name="ttContent" ><br />

<input type="submit" value="팁등록">
</form>
*/
		$ttIdx = $this->input->post('ttIdx');
		$mbIdx = $this->input->post('mbIdx');
		$ttName = $this->input->post('ttName');
		$ttSector = $this->input->post('ttSector');
		$ttTravelName = $this->input->post('ttTravelName');
		$ttContent = $this->input->post('ttContent');

		if(!$ttIdx || !$mbIdx || !$ttName || !$ttSector || !$ttTravelName || !$ttContent)
			$this->error->getError('0006');	// Key가 없을경우


		$result = $this->travel_model->tipUpdate(array(
								'ttIdx'=>$ttIdx,
								'mbIdx'=>$mbIdx,
								'ttName'=>$ttName,
								'ttSector'=>$ttSector,
								'ttTravelName'=>$ttTravelName,
								'ttContent'=>$ttContent
								));

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = '';

		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}

	public function delete(){
/*
<form name="form" method="post" action="/yps/travel/travel_tip/delete?key=a3b1a551515fb16937f16fcb47e99b4f">
ttIdx <input type="text" name="ttIdx"><br />
mbIdx <input type="text" name="mbIdx"><br />

<input type="submit" value="팁등록">
</form>
*/
		$ttIdx = $this->input->post('ttIdx');
		$mbIdx = $this->input->post('mbIdx');

		if(!$ttIdx || !$mbIdx)
			$this->error->getError('0006');	// Key가 없을경우


		$result = $this->travel_model->tipDelete(array(
								'ttIdx'=>$ttIdx,
								'mbIdx'=>$mbIdx
								));

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = '';

		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}


	public function recommend() {
/*
<form name="form" method="post" action="/yps/travel/travel_tip/recommend?key=a3b1a551515fb16937f16fcb47e99b4f">
ttIdx <input type="text" name="ttIdx"><br />
mbIdx <input type="text" name="mbIdx" ><br />

<input type="submit" value="팁등록">
</form>
*/
		$ttIdx = $this->input->post('ttIdx');
		$mbIdx = $this->input->post('mbIdx');

		if(!$ttIdx || !$mbIdx)
			$this->error->getError('0006');	// Key가 없을경우


		$result = $this->travel_model->tipRecommend(array(
								'ttIdx'=>$ttIdx,
								'mbIdx'=>$mbIdx
								));

		$ret = array();

		if($result == 1){
			$ret['status'] = "0";
			$ret['failed_message'] = '내가 작성한 팁은 추천하실 수 없습니다.';
		}elseif($result == 2){
			$ret['status'] = "0";
			$ret['failed_message'] = '이미추천하였습니다.';
		}else{
			$ret['status'] = "1";
			$ret['failed_message'] = '';
		}

		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}

	//신고
	function complaint() {
		$mpIdx = $this->input->post('mpIdx');
		$ttIdx = $this->input->post('ttIdx');
		$mbIdx = $this->input->post('mbIdx');

		if( !$mpIdx || !$ttIdx || !$mbIdx ) $this->error->getError('0006');	// Key가 없을경우

		$param = array(
			'mpIdx'	=> $mpIdx,
			'ttIdx'	=> $ttIdx,
			'mbIdx'	=> $mbIdx
		);

		//중복체크
		$check	= $this->travel_model->tipComplaintCheck( $param );
		if( $check > 0 ) {
			$ret['status'] = "0";
			$ret['failed_message'] = '이미 신고한 팁';
			echo json_encode( $ret );
			exit;
		}

		//로그 기록
		$result	= $this->travel_model->tipComplaintIns( $param );

		//팁의 카운티를 갱신함
		$result	= $this->travel_model->tipComplaintUpdate( $param );

		if( $result ) {
			$ret['status'] = "1";
			$ret['failed_message'] = '';
		} else {
			$ret['status'] = "0";
			$ret['failed_message'] = '팁 신고하기 오류';
		}
		echo json_encode( $ret );
	}
}
?>
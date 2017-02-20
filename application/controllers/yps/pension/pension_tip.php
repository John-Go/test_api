<?php
class Pension_tip extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
		$this->load->model('_yps/reservation/reservation_model');
		//$this->output->enable_profiler();
	}

	function index() {
		$mpIdx = $this->input->post('mpIdx');
		$mbIdx = $this->input->post('mbIdx');
		$ptSector = $this->input->post('ptSector');
		$ptName = $this->input->post('ptName');
		$ptPensionName = $this->input->post('ptPensionName');
		$ptContent = $this->input->post('ptContent');
		$ptTravelName = $this->input->post('ptTravelName');
		$rIdx_arr = $this->reservation_model->getReservationInfo($mbIdx, $mpIdx);
		
		if(isset($rIdx_arr['rIdx'])){
			$rIdx = $rIdx_arr['rIdx'];
		}else{
			$rIdx = "";
		}		
		if( strlen(trim($ptName)) == 0 )
		{
			$ptName = '회원'.rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
		}
		
		
		if(!isset($mpIdx) || !isset($mbIdx) || !isset($ptName) || !isset($ptPensionName) || !isset($ptContent)){
			$this->error->getError('0006');	// Key가 없을경우
		}			
		
        if(isset($rIdx_arr['rVer'])){
            if($rIdx_arr['rVer'] == '1'){
                $row = $this->pension_model->tipInsert(array( 
                            'mpIdx'=>$mpIdx,
                            'mbIdx'=>$mbIdx,
                            'ptSector'=>$ptSector,
                            'ptName'=>urldecode($ptName),
                            'ptPensionName'=>urldecode($ptPensionName),
                            'ptTravelName'=>urldecode($ptTravelName),
                            'ptContent'=>urldecode($ptContent),
                            'rIdx' => $rIdx
                            ));
            }else{
                $row = $this->pension_model->tipInsert_old(array( 
                            'mpIdx'=>$mpIdx,
                            'mbIdx'=>$mbIdx,
                            'ptSector'=>$ptSector,
                            'ptName'=>urldecode($ptName),
                            'ptPensionName'=>urldecode($ptPensionName),
                            'ptTravelName'=>urldecode($ptTravelName),
                            'ptContent'=>urldecode($ptContent),
                            'rIdx' => $rIdx
                            ));
            }
        }else{
            $row = $this->pension_model->tipInsert_old(array( 
                            'mpIdx'=>$mpIdx,
                            'mbIdx'=>$mbIdx,
                            'ptSector'=>$ptSector,
                            'ptName'=>urldecode($ptName),
                            'ptPensionName'=>urldecode($ptPensionName),
                            'ptTravelName'=>urldecode($ptTravelName),
                            'ptContent'=>urldecode($ptContent),
                            'rIdx' => $rIdx
                            ));
        }
		
		
		$ret = array();

		if($row){
			$ret['status'] = "1";
			$ret['failed_message'] = '';
		}else{
			$ret['status'] = "0";
			$ret['failed_message'] = '등록오류';
		}

		echo json_encode( $ret );

	}

	function recommend() {
		$ptIdx = $this->input->post('ptIdx');
		$mbIdx = $this->input->post('mbIdx');

		if(!$ptIdx || !$mbIdx)
			$this->error->getError('0006');	// Key가 없을경우


		$result = $this->pension_model->tipRecommend(array(
								'ptIdx'=>$ptIdx,
								'mbIdx'=>$mbIdx
								));

		$ret = array();

		if($result == 1){
			$ret['status'] = "0";
			$ret['failed_message'] = '내가 작성한 팁은 추천하실 수 없습니다.';
		}elseif($result == 2){
			$ret['status'] = "0";
			$ret['failed_message'] = '추천이 취소되었습니다.';
            //메세지 자체로 걸러서 문구 변경 불가
		}else{
			$ret['status'] = "1";
			$ret['failed_message'] = '';
		}

		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}

	//수정
	function tipModify() {
		$ptIdx = $this->input->post('ptIdx');
		$mpIdx = $this->input->post('mpIdx');
		$mbIdx = $this->input->post('mbIdx');
		$ptSector = $this->input->post('ptSector');
		$ptTravelName = $this->input->post('ptTravelName');
		$ptContent = $this->input->post('ptContent');

		if(!$ptIdx || !$mpIdx || !$mbIdx || !$ptContent)
			$this->error->getError('0006');	// Key가 없을경우
		
		//팁 수정
		$row = $this->pension_model->tipUpdate(array(
								'ptIdx'=>$ptIdx,
								'mpIdx'=>$mpIdx,
								'mbIdx'=>$mbIdx,
								'ptSector'=>$ptSector,
								'ptTravelName'=>urldecode($ptTravelName),
								'ptContent'=>urldecode($ptContent)
								));

		if( $row ) {		
			$ret['status'] = "1";
			$ret['failed_message'] = '';
		} else {
			$ret['status'] = "0";
			$ret['failed_message'] = '팁 수정하기 오류';
		}
		echo json_encode( $ret );
	}

	//삭제
	function tipDelete() {
		$ptIdx = $this->input->post('ptIdx');
		$mpIdx = $this->input->post('mpIdx');
		$mbIdx = $this->input->post('mbIdx');

		if(!$ptIdx || !$mpIdx || !$mbIdx)
			$this->error->getError('0006');	// Key가 없을경우
		
		//팁 삭제
		$row = $this->pension_model->tipDelete(array(
								'ptIdx'=>$ptIdx,
								'mpIdx'=>$mpIdx,
								'mbIdx'=>$mbIdx
								));

		if( $row ) {		
			$ret['status'] = "1";
			$ret['failed_message'] = '';
		} else {
			$ret['status'] = "0";
			$ret['failed_message'] = '팁 수정하기 오류';
		}
		echo json_encode( $ret );
	}

	//신고
	function complaint() {
		$mpIdx = $this->input->post('mpIdx');
		$ptIdx = $this->input->post('ptIdx');
		$mbIdx = $this->input->post('mbIdx');

		if( !$ptIdx || !$ptIdx || !$mbIdx ) $this->error->getError('0006');	// Key가 없을경우

		$check	= $this->pension_model->tipComplaintCheck(array(
																											'mpIdx'	=> $mpIdx,
																											'ptIdx'	=> $ptIdx,
																											'mbIdx'	=> $mbIdx
																														)
																										);
		if( $check > 0 ) {
			$ret['status'] = "0";
			$ret['failed_message'] = '이미 신고한 팁';
			echo json_encode( $ret );
			exit;
		}

		$result	= $this->pension_model->tipComplaintIns(array(
																										'mpIdx'	=> $mpIdx,
																										'ptIdx'	=> $ptIdx,
																										'mbIdx'	=> $mbIdx
																													)
																										);
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
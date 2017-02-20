<?php
class Pension_room_pic_all extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		$ptIdx		= $this->input->get('ptIdx');
		$prIdx		= $this->input->get('prIdx');

		if( !$ptIdx || !$prIdx ) $this->error->getError('0006');	// Key가 없을경우
		$roomData	= $this->pension_model->getRoomKey(array(
															'ptIdx'	=> $ptIdx,
															'prIdx'	=> $prIdx
															)
														);


		if( empty( $roomData ) ) $this->error->getError('0005');	// 정보가 없을경우

		foreach( $roomData as $r ) {
			$ret['idx']			= $r->pprIdx;
			$ret['name']			= $r->pprName;
			$roomImages	= $this->pension_model->pensionRoomImageLists($r->pprIdx,0,1000);
			$ret['image_cnt']	= $roomImages['count'];
			foreach( $roomImages['query'] as $j => $o ) {
				$ret['lists'][$j]['images']	= 'http://img.yapen.co.kr/pension/room/'.$ptIdx.'/800x0/'.$o['pprpFileName'];
			}
		}

		$ret['status'] = "1";
		$ret['failed_message'] = '';

		echo json_encode( $ret );

		//$this->output->enable_profiler();
	}
}
?>
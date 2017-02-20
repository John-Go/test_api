<?php
class Pension_pic_all extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
		$this->load->model('_yps/pension/room_model');
	}

	function index() {
		$ptIdx		= $this->input->get('ptIdx');

		if( !$ptIdx ) $this->error->getError('0006');	// Key가 없을경우
		$roomData	= $this->pension_model->getRoomKey(array(
																								'ptIdx'	=> $ptIdx,
																								'prIdx'	=> ''
																								)
																							);

		$etcData	= $this->pension_model->getEtcKey(array(
																								'ptIdx'	=> $ptIdx,
																								'prIdx'	=> ''
																								)
																							);


		if( empty( $roomData ) ) $this->error->getError('0005');	// 정보가 없을경우
		
		//$ret['room']['lists'] = array();
		
		foreach( $roomData as $k => $r ) {
			$ret['room']['lists'][$k]['idx']			= $r->pprIdx;
			$ret['room']['lists'][$k]['name']			= $r->pprName;
			$ret['room']['lists'][$k]['inMin']		= $r->pprInMin;
			$ret['room']['lists'][$k]['inMax']		= $r->pprInMax;
			$ret['room']['lists'][$k]['size']			= $r->pprSize;

			//최저가구함
			$minPrice = $this->room_model->getRoomPrice( $ptIdx, $r->pprIdx);
			$ret['room']['lists'][$k]['roomMin']	= number_format( $minPrice );
			//$ret['room']['lists'][$k]['roomMin']	= $r->ppbRoomMin;
			// $ret['lists'][$no]["price"] = number_format($this->room_model->totalPrice( $row['pprIdx'] )['byRoom']['resultPrice']);	// 이용요금
			
			
			$roomImages	= $this->pension_model->pensionRoomImageLists($r->pprIdx,0,1000);
			$ret['room']['lists'][$k]['image_cnt']	= $roomImages['count'];
            if($roomImages['count'] == 0){
                unset($ret['room']['lists'][$k]);
            }
			foreach( $roomImages['query'] as $j => $o ) {
				$ret['room']['lists'][$k]['lists'][$j]['images']	= 'http://img.yapen.co.kr/pension/room/'.$ptIdx.'/800x0/'.$o['pprpFileName'];
			}
		}

		//$ret['etc']['lists'] = array();
		
		foreach( $etcData as $k => $r ) {
			$ret['etc']['lists'][$k]['idx']				= $r->ppeIdx;
			$ret['etc']['lists'][$k]['name']			= $r->ppeName;
			$etcImages	= $this->pension_model->pensionEtcImageLists($r->ppeIdx,0,1000);
			$ret['etc']['lists'][$k]['image_cnt']	= $etcImages['count'];
            if($etcImages['count'] == 0){
                unset($ret['etc']['lists'][$k]);
            }
			foreach( $etcImages['query'] as $j => $o ) {
				$ret['etc']['lists'][$k]['lists'][$j]['images']	= 'http://img.yapen.co.kr/pension/etc/'.$ptIdx.'/800x0/'.$o['ppepFileName'];
			}
		}





		$ret['status'] = "1";
		$ret['failed_message'] = '';

		echo json_encode( $ret );

		//펜션 각 객실키를 가져온다

		//펜션 각 객실별 사진을 가져온다

		//$this->output->enable_profiler();
	}
}
?>
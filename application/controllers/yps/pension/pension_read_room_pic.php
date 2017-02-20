<?php

class Pension_read_room_pic extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');

	    $info = $this->pension_model->getPensionRoom($idx);
		$result = $this->pension_model->pensionRoomImageLists($idx, 0, 100);

		if(!$result['count'])
			$this->error->getError('0005');	// ������ �������

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $result['count']."";
        $ret['roomName'] = rawurldecode($info['pprName']);
        $ret['inMin'] = $info['pprInMin'];
        $ret['inMax'] = $info['pprInMax'];

		$no = 0;
		foreach ($result['query'] as $row) {
			$ret['lists'][$no]['image'] = 'http://img.yapen.co.kr/pension/room/'.$row['mpIdx'].'/800x0/'.$row['pprpFileName'];
			$no++;
		}

		echo json_encode( $ret );

	}
}

?>
<?php
class Mypage_travel_tip extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
		//$this->output->enable_profiler();
	}

	function index() {
		checkMethod('get');	// ���� �޼��带 ����

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key�� �������

		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);
		
		$offset = ($page - 1) * $limit;


		$result = $this->member_model->travelTipLists($idx,$offset,$limit); // ��

		if( !$result['count'] )
			$this->error->getError('0005');	// ������ �������

		$no = 0;
		$ret = array();
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		$ret['tnt_cnt'] = (string)$result['count'];

		foreach ($result['query'] as $row) {

			$ret['lists'][$no]['idx'] = $row['ttIdx'];
			$ret['lists'][$no]['mbIdx'] = $row['mbIdx'];
			$ret['lists'][$no]['name'] = $row['ttName'];
			$ret['lists'][$no]['ttSector'] = $row['ttSector'];
			$ret['lists'][$no]['ttTravelName'] = $row['ttTravelName'];
			$ret['lists'][$no]['content'] = $row['ttContent'];
			$ret['lists'][$no]['date'] = substr($row['ttRegDate'],0,10);
			$ret['lists'][$no]['recommend'] = $row['ttRecommend'];

			$no++;
		}

		echo json_encode( $ret );
	}
}
?>
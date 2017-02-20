<?php

class Mypage_point extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
		$this->config->load('yps/_code');
	}

	function index() {
		checkMethod('get');

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');

		$page = $this->Pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->Pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);
		
		$offset = ($page - 1) * $limit;

		$result = $this->member_model->memberPointLists($idx,$offset,$limit);

		if( !$result['count'] )
			$this->error->getError('0005');

		$no = 0;
		$ret = array();
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		$ret['tnt_cnt'] = $result['count'].'';

		foreach ($result['query'] as $row) {

			$ret['lists'][$no]['code'] = $row['rCode'];
			$ret['lists'][$no]['point_info'] = $this->config->item('pension_member_point_save_info')[$row['mslPointCode']];
			$ret['lists'][$no]['plus_minus'] = (!strcmp($row['mslPlusMinus'],'P')) ? '적립' : '사용';
			$ret['lists'][$no]['point'] = $row['mslPoint'];
			$ret['lists'][$no]['reg_date'] = substr($row['mslPointDate'],0,10);
			$ret['lists'][$no]['expiration_date'] = $row['mslExpirationDate'];

			$no++;
		}

		echo json_encode( $ret );		
	}
}
?>
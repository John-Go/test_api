<?php

class Pension_read_pic extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// ���� �޼��带 ����

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key�� �������

		$result = $this->pension_model->pensionImageLists($idx, 0, 100);

		if(!$result['count'])
			$this->error->getError('0005');	// ������ �������

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $result['count']."";

		$no = 0;
		foreach ($result['query'] as $row) {
			$ret['lists'][$no]['image'] = 'http://img.yapen.co.kr/pension/etc/'.$idx.'/800x0/'.$row['ppepFileName'];
			$no++;
		}

		echo json_encode( $ret );

	}
}
?>
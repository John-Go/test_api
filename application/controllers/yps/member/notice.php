<?php
class Notice extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
	}

	function index() {
		checkMethod('get');

		$code = $this->pension_lib->paramNummCheck($this->input->get('code'), 'S01', array('S01'=>1,'S02'=>1,'S03'=>1));
		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);

		$offset = ($page - 1) * $limit;

		$result = $this->member_model->notice($code,$offset,$limit);

		if( !$result['count'] )
			$this->error->getError('0005');

		$no = 0;
		$ret = array();
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		$ret['tnt_cnt'] = $result['count'].'';

		foreach ($result['query'] as $row) {

			$ret['lists'][$no]['title'] = $row['anTitle'];
			$ret['lists'][$no]['content'] = $row['anContent'];
			$ret['lists'][$no]['date'] = $row['anDate'];

			$no++;
		}

		echo json_encode( $ret );


//		$this->output->enable_profiler();

	}
}
?>
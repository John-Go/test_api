<?php
class Travel_tip_list extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/travel/travel_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key가 없을경우

		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);
		
		$offset = ($page - 1) * $limit;


		$result = $this->travel_model->tipLists($idx,$offset,$limit); // 팁

		if( !$result['count'] )
			$this->error->getError('0005');	// 정보가 없을경우

		$no = 0;
		$ret = array();
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		$ret['tnt_cnt'] = $result['count'].'';
		$ret['mbIdx']	= $this->input->get('mbIdx');

		foreach ($result['query'] as $row) {
			$row['ttRegDate'] = substr( $row['ttRegDate'], 0, 10 );
			$ret['lists'][$no]['idx'] = $row['ttIdx'];
			$ret['lists'][$no]['mbIdx'] = $row['mbIdx'];
			$ret['lists'][$no]['name'] = $row['ttName'];
			$ret['lists'][$no]['content'] = $row['ttContent'];
			$ret['lists'][$no]['date'] = $row['ttRegDate'];
			$ret['lists'][$no]['recommend'] = $row['ttRecommend'];

			$no++;
		}


		echo json_encode( $ret );


//		$this->output->enable_profiler();

	}
}
?>
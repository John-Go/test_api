<?php
class Mypage_pension_tip extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
	}

	function index() {
		checkMethod('get');

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');

		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);
		
		$offset = ($page - 1) * $limit;

		$result = $this->member_model->pensionTipLists($idx,$offset,$limit);

		if( !$result['count'] )
			$this->error->getError('0005');

		$no = 0;
		$ret = array();
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		$ret['tnt_cnt'] = $result['count'].'';

		foreach ($result['query'] as $row) {

			$ret['lists'][$no]['idx'] = $row['ptIdx'];
			$ret['lists'][$no]['mpIdx'] = $row['mpIdx'];
			$ret['lists'][$no]['mbIdx'] = $row['mbIdx'];
			$ret['lists'][$no]['name'] = $row['ptName'];
			$ret['lists'][$no]['ptSector'] = $row['ptSector'];
			$ret['lists'][$no]['ptPensionName'] = $row['ptPensionName'];
			$ret['lists'][$no]['content'] = $row['ptContent'];
            if(isset($row['ptAnswer'])){
                if($row['ptAnswer'] != ""){
                    $ret['lists'][$no]['content'] .= '

▶ [ 사장님 답변 ]
'.$row['ptAnswer'];
                }
            }
			$ret['lists'][$no]['travel'] = $row['ptTravelName'];
			$ret['lists'][$no]['date'] = substr( $row['ptRegDate'], 0, 10 );
			$ret['lists'][$no]['recommend'] = $row['ptRecommend'];
			$ret['lists'][$no]['point_save'] = $row['ptPointSave'];
            $ret['lists'][$no]['blindFlag'] = $row['ptBlindFlag'];

			$no++;
		}

		echo json_encode( $ret );


//		$this->output->enable_profiler();

	}
}
?>
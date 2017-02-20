<?php
class Pension_tip_list extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');

		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);

		$offset = ($page - 1) * $limit;
        
		$result = $this->pension_model->tipLists($idx,$offset,$limit);

		if( !$result['count'] )
			$this->error->getError('0005');

		$no = 0;
		$ret = array();
		$ret['mbIdx']	= $this->input->get('mbIdx');
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		$ret['tnt_cnt'] = $result['count'].'';
		$ret['mbNick'] = '회원'.rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        $name = "";
        
		foreach ($result['query'] as $row) {
		    $name = mb_substr($row['ptName'],0,1)."*".mb_substr($row['ptName'],3);
            
			$ret['lists'][$no]['idx'] = $row['ptIdx'];
			$ret['lists'][$no]['member_key'] = $row['mbIdx'];
			$ret['lists'][$no]['name'] = $name;
			$ret['lists'][$no]['sector']	= $row['ptSector'];
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
<?php

class Local_top_rol_banner extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$idx = $this->input->get('idx');		
		if( !$idx ) $this->error->getError('0006');	// Key가 없을경우
		$topRolResult = $this->pension_model->topLocRolBanner($idx);
	
		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $topRolResult['count']."";
		
		$bannerLists = $topRolResult['lists'];
		$bannerNum = 0;
		if($topRolResult['count'] > 0){
			foreach($bannerLists as $row){
				$ret['lists'][$bannerNum]["idx"]= $row['altrbIdx'];
				$ret['lists'][$bannerNum]["title"]= $row['altrbTitle'];
				$ret['lists'][$bannerNum]["fileName"]= 'http://img.yapen.co.kr/pension/locTop/'.$row['altrbFilename'];
				$ret['lists'][$bannerNum]["mpIdx"]= $row['mpIdx'];
				$ret['lists'][$bannerNum]["pensionName"]= $row['pensionName'];
				$bannerNum++;
			}
		}
		echo json_encode( $ret );
	}
}
?>
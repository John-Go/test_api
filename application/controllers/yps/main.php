<?php

class Main extends CI_Controller {
	function __construct() {
		parent::__construct();

		$CI =& get_instance();
		$CI->dbHTS = $this->load->database('hts', TRUE);

		$this->load->helper('tel_helper');
		$this->load->model('_hts/motel/detail_model', 'detail_model');
	}

	function index() {
		//접근 메서드를 제한
		checkMethod('get');

		$mpsIdx = $this->input->get('idx');
		if( !$mpsIdx ) $this->error->getError('0006');	// 모텔key가 없으면 오류


		//모텔 기본정보(이름,주소,연락처,가고싶어요,좌표 등)
		$result  = $this->detail_model->getDetailInfo( $mpsIdx );

		if( !$result->num_rows() ) $this->error->getError('0005');	// 모텔정보가 없는경우(미게시 처리일 경우도 포함)
		$row = $result->row();

		$ret['status']					= 1;
		$ret['detail_info']['name']		= $row->mpsName;
		$ret['detail_info']['addr1']	= $row->mpsAddr1;
		$ret['detail_info']['addr2']	= $row->mpsAddr2;
		$ret['detail_info']['tel']		= exportPhone( $row->mpsTel , $row->mpsTelOpen);
		$ret['detail_info']['way_info']	= $row->mpsMapInfo;


		//지하철 노선 정보
		$subwayInfo = NULL;
		$result = $this->detail_model->getDetailInfoSubway( $row->mpIdx );
		$no = 0;
		$line_no = 0;
		$mssTrans = 0;
		$ret['detail_info']['subway_info_count'] = 0;
		foreach( $result->result() as $row ){
			if( $mssTrans != $row->mssTrans ){
				$line_no = 0;
				$mssTrans = $row->mssTrans;
				$no++;
			}
			$ret['detail_info']['subway_info'][$row->mssTrans]['name']	= $row->mssName;
			$ret['detail_info']['subway_info'][$row->mssTrans]['info']	= $row->mssInfo;
			$ret['detail_info']['subway_info'][$row->mssTrans]['line'][$line_no]['name']	= $row->mslName;
			$line_no++;
		}
		$ret['detail_info']['subway_info_count'] = $no;

		// 노선정보를 역순으로 정렬하고 index에 들어가있는 mssTrans 값을 없앰
		if( $no ) @rsort( $ret['detail_info']['subway_info'] );


		//테마정보
		$result = $this->detail_model->getDetailTheme( $mpsIdx );
		$no = 0;
		$ret['theme_info_count'] = 0;
		foreach( $result->result() as $row ){
			if( $row->image1 ) $img = IMG_PATH . '/theme/' . $row->image1;
			else $img = '';

			$ret['theme_info'][$no]['name']	= $row->name;
			$ret['theme_info'][$no]['img']	= $img;
			$no++;
		}

		$ret['theme_info_count'] = $no;


		echo json_encode( $ret );
	}

}
?>
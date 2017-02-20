<?php

class Search_theme extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');

		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$ret['status'] = '1';
		$ret['failed_message'] = '';

		// 인기지역 설정
		// 201405121909 pyh : 인기테마 제거, f가 0부터 시작하도록 처리
		//		$result	= $this->pension_model->getThemePlaceList($array = array(
		//				'code'		=> '2.',
		//				'favorite'	=> 1,
		//				'depth'		=> 3
		//			) 
		//		);
		//		
		//		$ret['lists'][0]['code'] = '';
		//		$ret['lists'][0]['name'] = '인기테마';
		//		$ret['lists'][0]['tntcnt'] = $result['count'];
		//		$ret['lists'][0]['popularity'] = '1';
		//
		//		foreach( $result['obj']->result() as $k => $o ) {
		//			$ret['lists'][0]['lists'][$k]['code'] = $o->mtCode;
		//			$ret['lists'][0]['lists'][$k]['name'] = $o->mtName;
		//			$ret['lists'][0]['lists'][$k]['count'] = $o->sCnt;
		//			$ret['lists'][0]['lists'][$k]['popularity'] = '0';
		//		}
		//		$f = 1;

		//인기지역이 아닌지역 설정
		$result	= $this->pension_model->getThemePlaceList($array = array(
				'code'		=> '2.',
				'favorite'	=> '',
				'depth'		=> 2
			) 
		);
        
		$f = 0;
		foreach( $result['obj']->result() as $k => $o ) {
			$ret['lists'][$f]['code'] = $o->mtCode;
			$ret['lists'][$f]['name'] = $o->mtName;
			$ret['lists'][$f]['tntcnt'] = $o->sCnt;
			$ret['lists'][$f]['popularity'] = '0';

			//하위 지역 설정
			$subResult	= $this->pension_model->getThemePlaceList($array = array(
					'code'	   => $o->mtCode,
					'favorite' => '',
					'depth'	   => 3
				) 
			);
			
			foreach( $subResult['obj']->result() as $j => $s ) {
				$ret['lists'][$f]['lists'][$j]['code']	= $s->mtCode;
				$ret['lists'][$f]['lists'][$j]['name']	= $s->mtName;
				$ret['lists'][$f]['lists'][$j]['count']	= $s->sCnt;
				$ret['lists'][$f]['lists'][$j]['popularity'] = ( $s->mtFavorite == 1 ) ? '1' : '0';
			}

			$f++;
		}

		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}
}
?>
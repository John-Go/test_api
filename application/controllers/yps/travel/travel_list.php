<?php

class Travel_list extends CI_Controller {
	function __construct() {
		parent::__construct();

//		$CI =& get_instance();
//		$CI->dbHTS = $this->load->database('hts', TRUE);

		$this->load->config('yps/_constants');
		$this->load->library('travel_lib');
		$this->load->model('_yps/travel/travel_model');
		$this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
		define('IMG_PATH', 'http://img.yapen.co.kr');
	}

	function index() {
		#$this->output->enable_profiler(true);
		checkMethod('get');	// 접근 메서드를 제한

		$locCode		= $this->input->get('locCode');
		$themeCode	= $this->travel_lib->paramNummCheck($this->input->get('themeCode'), 0, array("8"=>1,"10"=>1,"11"=>1,"12"=>1));
		$orderby		= $this->travel_lib->paramNummCheck($this->input->get('orderby'), 1, NULL);
		$page				= $this->travel_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit			= $this->travel_lib->paramNummCheck($this->input->get('limit'), 20, NULL);
		$locCode2		= $this->travel_lib->get_cate_limit($locCode);
		$themeCode2	= $this->travel_lib->get_cate_limit($themeCode);

/*
		echo 'locCode : '.$locCode.'<br />';
		echo 'themeCode : '.$themeCode.'<br />';
		echo 'orderby : '.$orderby.'<br />';
		echo 'page : '.$page.'<br />';
		echo 'limit : '.$limit.'<br /><br /><br />';
*/

		$offset = ($page - 1) * $limit;
		$result = $this->travel_model->travelLists(array(
			'locCode'		=> $locCode,
			'locCode2'	=> $locCode2,
			'themeCode'	=> $themeCode,
			'themeCode2'=> $themeCode2,
			'orderby'		=> $orderby,
			'page'			=> $page,
			'perPage'		=> $limit,
			'offset'		=> $offset
		));

		$no = 0;

		$ret = array();
		$ret['status'] = 1;
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $result['count']->num_rows()."";

		foreach ($result['obj']->result_array() as $row) {

			$basket_cnt	= $this->basket_model->getTravelBasketCountByMpIdx( $row['ci_idx'] );
			$ret['lists'][$no]['idx'] = $row['ci_idx'];
			$ret['lists'][$no]['name'] = $row['dniTitle'];
			$ret['lists'][$no]['address'] = $row['addr'];//.' '.$row['dniAdress'];
			//$ret['lists'][$no]['theme'] = $this->travel_lib->travelThemeInfo($row['ci_idx']); // 테마
			$ret['lists'][$no]['theme'] = $row['themeName']; // 테마
			
			$ret['lists'][$no]['basket_cnt']	= $basket_cnt;
			if( isset( $row['dniiFileName'.$row['dniiCheckImage']] ) )
				$ret['lists'][$no]['filename'] = $row['dniiFileName'.$row['dniiCheckImage']];
			else $ret['lists'][$no]['filename'] = '';

			$ret['lists'][$no]['imageNum'] = (string)$this->travel_lib->travelImageCount($row['ci_idx']);//이미지수

			$ret['lists'][$no]['readnum'] = $row['dniReadnum'];

			$no++;
		}

		//print_re( $ret );
		
		echo json_encode( $ret );

		
		//$this->output->enable_profiler();
/*
update infoDB.category C set 
	ca_count=( 
		select count(*) 
		from infoDB.categoryInfo CI, infoDB.ynjDateNewInfo DNI 
		where CI.ci_idx=DNI.dniIdx and DNI.dniOpen='Y' and CI.ca_type=C.ca_type and CI.ca_code=C.ca_code 
		) 
where 1
*/

/*
SELECT  count(*) as numrows
FROM (`infoDB`.`ynjDateNewInfo` DNI)
JOIN `infoDB`.`ynjDateNewInfoImage` as DNII ON `DNI`.`dniIdx` = `DNII`.`dniIdx`
JOIN `infoDB`.`categoryInfo` as CI ON `DNI`.`dniIdx` = `CI`.`ci_idx` and CI.ci_type='D' and CI.ca_code='2.001' 
WHERE `DNI`.`dniOpen` =  'Y'
GROUP BY `DNI`.`dniIdx`
HAVING `numrows` >= 1
ORDER BY `DNI`.`dniIdx` desc
LIMIT 20 OFFSET 60 
*/
	}
}
?>
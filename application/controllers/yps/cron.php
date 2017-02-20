<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends CI_Controller {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		//$this->output->enable_profiler(TRUE);
		
		if ( ! $this->input->is_cli_request() )
		{
			//exit('No direct script access allowed');
		}
		
	}


	/**
	 * index page
	 * 
	 * @param null
	 * @return String
	 */
	public function index()
	{
	}


	/**
	 * 카테고리 카운트 업데이트
	 * 
	 * @param null
	 * @return String
	 */
	public function update_infoDB_category_count()
	{
		$this->db->query('
			update infoDB.category C set 
				ca_count=( 
					select count(*) 
					from infoDB.categoryInfo CI, infoDB.ynjDateNewInfo DNI 
					where CI.ci_idx=DNI.dniIdx and DNI.dniOpen="Y" and CI.ca_type=C.ca_type and CI.ca_code=C.ca_code 
					) 
			where 1
		');

	}
}

/* End of file */
<?php

class Mobile_change extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('_yps/member/member_model', 'member_m');
		//$this->output->enable_profiler();
	}

	function index() {
		$mbIdx					= $this->input->post('mbIdx');
		$mobile_number	= $this->input->post('mobile_number');

		if( !$mbIdx || !$mobile_number ) $this->error->getError('0006');	// Key가 없을경우

		$mobile_number	= trim( str_replace( '-', '', $mobile_number ) );

		$result = $this->member_m->memberMobileChange(array(
																										'mbIdx'	=> $mbIdx,
																										'mobile_number'	=> $mobile_number
																												)
																									);

		$ret['status'] = '1';
		$ret['failed_message'] = '';
		echo json_encode( $ret );
	}
}
?>
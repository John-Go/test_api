<?php

class Mypage_reservation extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/reservation/reservation_model');
		$this->config->load('yps/_code');

	}

	function index() {
		checkMethod('get');

		$mbIdx = $this->input->get('mbIdx');

		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);

		$gName = $this->input->get('gName');
		$gBirth = $this->input->get('gBirth');
		$rMobile = $this->input->get('rMobile');
		
		$offset = ($page - 1) * $limit;

		$result = $this->reservation_model->myReservation($mbIdx,$offset,$limit,$gName,$gBirth,$rMobile);

		if( !$result['count'] )
			$this->error->getError('0005');

		$no = 0;
		$ret = array();
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		$ret['tnt_cnt'] = $result['count'].'';

		foreach ($result['query'] as $row) {

			$ret['lists'][$no]['idx'] = $row['rIdx'];
			$ret['lists'][$no]['pension'] = $row['rPension'];
			$ret['lists'][$no]['room'] = $row['rPensionRoom'];
			$ret['lists'][$no]['in_date'] = $row['rStartDate'];
			$ret['lists'][$no]['reg_date'] = $row['rRegDate'];
			$ret['lists'][$no]['state'] =  $this->config->item('rPaymentState')[$row['rPaymentState']];
			$ret['lists'][$no]['date'] = $row['rStartDate'];
			$ret['lists'][$no]['mpIdx'] = $row['mpIdx'];
			
			$datetime1 = new DateTime($row['rStartDate']);
			$datetime2 = new DateTime($row['rEndDate']);
			$interval = $datetime1->diff($datetime2);
			$ret['lists'][$no]['dateNum'] = (string)$interval->format('%a');
			

			$no++;
		}

		echo json_encode( $ret );		
	}
}
?>
<?php
/**
 * Pension_read_event_count
 * 
 * @author pyh, 201405231805
 * @desc 현재 진행중인 이벤트 카운트
 */
class Pension_read_event_count extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$idx = $this->input->get('idx');
		if (!$idx) $this->error->getError('0006');	// Key가 없을경우

		$infoResult = $this->pension_model->pensionEventCount($idx); // 펜션정보

		if (!$infoResult->num_rows())
			$this->error->getError('0005');	// 정보가 없을경우

		$infoRow = $infoResult->row_array();

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
		$ret['event_count']	= $infoRow['event_count'];

		//print_re( $ret );
		echo json_encode($ret);
		//$this->output->enable_profiler();
	}
}
?>
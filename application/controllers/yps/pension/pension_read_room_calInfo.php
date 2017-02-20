<?php

class Pension_read_room_calInfo extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/room_model');
		$this->config->load('yps/_code');
	}
    

	function index() {
        
        checkMethod('get'); // 접근 메서드를 제한
        
        $pprIdx = $this->input->get('pprIdx');
        if( !$pprIdx ) $this->error->getError('0006'); // Key가 없을경우
        
        $info = $this->room_model->getRoomCalInfo($pprIdx);
        
        $ret['roomName'] = $info['pprName'];
        $ret['roomMin'] = $info['pprInMin'];
        $ret['roomMax'] = $info['pprInMax'];
        $ret['roomSize'] = $info['pprSize'];
        
        echo json_encode( $ret );
    }
}

?>
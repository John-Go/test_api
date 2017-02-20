<?php
class PensionMainImage extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('_yps/pension/pension_model');
    }
    
    function index(){
        $mpIdx = $this->input->get_post('idx');
        if( !$mpIdx ) $this->error->getError('0006'); // Key가 없을경우

        $info = $this->pension_model->getPensionInfo($mpIdx); // 펜션정보
        
        if(!isset($info['mpIdx'])){
            $this->error->getError('0005'); // 정보가 없을경우
        }            

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['imageUrl'] = "http://img.yapen.co.kr/pension/etc/".$info['mpIdx']."/".$info['ppbImage'];
        
        echo json_encode( $ret );
    }
}
?>
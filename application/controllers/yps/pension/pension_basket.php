<?php
class Pension_basket extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$sector = $this->input->get('sector');
		$mpIdx = $this->input->get('mpIdx'); // 업체키
		$mbIdx = $this->input->get('mbIdx'); // 회원키



		if(!$sector || !$mpIdx || !$mbIdx)
			$this->error->getError('0005');	// Key가 없을경우

		$result = $this->pension_model->pensionBasket($sector, $mpIdx, $mbIdx);

		$ret = array();

		if($result == "1"){
			$ret['status'] = "1";
			$ret['failed_message'] = "";		
		}else{
			$ret['status'] = "0";
			$ret['failed_message'] = $result;		
		}



		echo json_encode( $ret );
	}
    
    function toggle(){
        checkMethod('get'); // 접근 메서드를 제한

        $sector = $this->input->get('sector');
        $mpIdx = $this->input->get('mpIdx'); // 업체키
        $mbIdx = $this->input->get('mbIdx'); // 회원키



        if(!$sector || !$mpIdx || !$mbIdx)
            $this->error->getError('0005'); // Key가 없을경우

        $result = $this->pension_model->pensionBasketToggle($sector, $mpIdx, $mbIdx);
        

        $ret = array();

        if($result == "1"){
            $ret['status'] = "1";
            $ret['failed_message'] = "";        
        }else if($result == "2"){
            $ret['status'] = "1";
            $ret['value'] = "2";
            $ret['failed_message'] = "";        
        }else if($result == "3"){
            $ret['status'] = "1";
            $ret['value'] = "3";
            $ret['failed_message'] = "";        
        }else{
            $ret['status'] = "0";
            $ret['failed_message'] = "ERROR!";
        }
        
        $count = $this->pension_model->pensionBasketCount($mbIdx);
        $ret['count'] = $count;



        echo json_encode( $ret );
    }
    
    function BasketSch(){
        checkMethod('get'); // 접근 메서드를 제한

        $mpIdx = $this->input->get('mpIdx'); // 업체키
        $mbIdx = $this->input->get('mbIdx'); // 회원키



        if(!$mpIdx)
            $this->error->getError('0005'); // Key가 없을경우

        $result = $this->pension_model->pensionBasketSch($mpIdx, $mbIdx);

        $ret = array();

        if($result == "1"){
            $ret['status'] = "1";
            $ret['value'] = "1";
            $ret['failed_message'] = "";        
        }else if($result == "2"){
            $ret['status'] = "1";
            $ret['value'] = "2";
            $ret['failed_message'] = "";        
        }else{
            $ret['status'] = "0";
            $ret['failed_message'] = "ERROR!";       
        }



        echo json_encode( $ret );
    }
}
?>
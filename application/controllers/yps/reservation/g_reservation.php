<?php

class G_reservation extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
		$this->load->model('_yps/pension/room_model');
		$this->load->model('_yps/reservation/reservation_model');
		$this->config->load('yps/_code');
	}
	

	function index() {
        checkMethod('get'); // 접근 메서드를 제한

        $mpidx = $this->input->get('mpIdx');
        
        /* Out URL setting start */
        $outUrl_arr = $this->pension_model->getOutUrl($mpidx);
        if(count($outUrl_arr) > 0){
            $ret['outUrl'] = $outUrl_arr['ppbOutUrl'];
        }       
        /* Out URL setting end */
        
        /* Pension Limit Date setting start */
        $LimitPensionDate = $this->reservation_model->getPensionLimitDate($mpidx);
        $limitDate = "";
        if(count($LimitPensionDate) > 0){
            if($LimitPensionDate['rodLoofDays'] > 0){
                $last_day = date("t", mktime(0,0,0,date('m'),date('d')+$LimitPensionDate['rodLoofDays'],date('Y')));
                $limitDate = date("Y-m-d", mktime(0,0,0,date('m'),date('d')+$LimitPensionDate['rodLoofDays']+$last_day,date('Y')));
            }else{
                if($LimitPensionDate['rodSetdate'] != ""){
                    $limitDate = $LimitPensionDate['rodSetdate'];
                }else{
                    $last_day = date("t", mktime(0,0,0,date('m')+3,date('d'),date('Y')));
                    $limitDate = date("Y-m-d", mktime(0,0,0,date('m')+3,$last_day,date('Y')));
                }
            }           
        }else{
            $last_day = date("t", mktime(0,0,0,date('m')+3,date('d'),date('Y')));
            $limitDate = date("Y-m-d", mktime(0,0,0,date('m')+3,$last_day,date('Y')));
        }
        $ret['LimitDate'] = $limitDate;
        
        /* Pension Limit Date setting end */
        
        echo json_encode( $ret );
    }

    function room_list() {
        checkMethod('get'); // 접근 메서드를 제한

        $mpidx = $this->input->get('mpIdx');
        if( !$mpidx ) $this->error->getError('0006'); // Key가 없을경우
                
        /* Pension Room List setting start */
        $result = $this->pension_model->pensionRoomLists($mpidx);

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";

        $no = 0;
        foreach ($result as $row) {
            $arrImage = array();
            $resultImages = $this->pension_model->pensionRoomImageLists($row['pprIdx'], 0, 9999);
            /* Pension Room List setting end */

            $ret['rooms'][$no]["idx"] = $row['pprIdx']; // 객실키            
            $ret['rooms'][$no]["name"] = rawurlencode($row['pprName']); // 객실명
            $ret['rooms'][$no]["inMin"] = $row['pprInMin']; // 최소수용인원
            $ret['rooms'][$no]["inMax"] = $row['pprInMax']; // 최대수용인원
            $ret['rooms'][$no]["price"] = number_format($this->room_model->totalPrice( $row['pprIdx'] )['byRoom']['resultPrice']);  // 이용요금     

            $no++;
        }
        
        echo json_encode( $ret );
    }

    function price(){
        checkMethod('get'); // 접근 메서드를 제한
        
        /* parameter setting start */
        $mpIdx = $this->input->get('mpIdx');
        if($mpIdx == ""){
            $mpIdx = $this->input->get('ptIdx');
        }
        $pprIdx = $this->input->get('pprIdx');
        $startDate = $this->input->get('startDate');
        $revDate = $this->input->get('revDate');
        /* parameter setting ehd */
        
        // 201406101125 pyh : 옛날 소스라 현재 소스로 수정
        // $price_arr = $this->room_model->totalPrice($pprIdx, $startDate, $revDate);
        $priceArray = $this->room_model->getDateRoomPrice($mpIdx, $pprIdx, $startDate, $revDate);
            
        if ($priceArray['basicPrice'] == $priceArray['resultPrice']){
            $ret['rPrice'] = 0;
            $ret['rSalePrice'] = number_format($priceArray['resultPrice'])."원";
        } else {
            $ret['rPrice'] = "".number_format($priceArray['basicPrice'])."원";
            $ret['rSalePrice'] = number_format($priceArray['resultPrice'])."원";
        }
              
        echo json_encode( $ret );
    }
}
?>
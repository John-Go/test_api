<?php

class Calendar extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->config('yps/_constants');
		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/Calendar_model');		
		$this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
		$this->config->load('yps/_code');
	}

    function index() {
        $mpIdx = $this->input->get_post('mpIdx');        
        $schDate = $this->input->get_post('schDate');
        $idxStrings = $this->input->get_post('idxStrings');
        
        /* date setting Start */
        $schYear = substr($schDate,0,4);
        $schMonth = substr($schDate,5,2);
        $schDay = substr($schDate,8,2);
        
        $sunDate = date('Y-m-d',mktime(0,0,0,$schMonth,$schDay-(date('w',strtotime($schDate))),$schYear));
        $setYear = substr($sunDate,0,4);
        $setMonth = substr($sunDate,5,2);
        $setDay = substr($sunDate,8,2); 
        $satDate = date('Y-m-d',mktime(0,0,0,$schMonth,$schDay+(6-date('w',strtotime($schDate))),$schYear));
        $satYear = substr($satDate,0,4);
        $satMonth = substr($satDate,5,2);
        $satDay = substr($satDate,8,2); 
        $nextDate = date('Y-m-d', mktime(0,0,0,$satMonth, $satDay+1, $satYear));
        $prevDate = date('Y-m-d', mktime(0,0,0,$setMonth,$setDay-6,$setYear));
        $todayDate = date('Y-m-d', mktime(0,0,0,date('m'),date('d')-(date('N',strtotime(date('Y-m-d')))), date('Y')));
        $ret = array();
        /* date setting End */
        
        /* prev button data Start */
        $ret['prevSetDate'] = $prevDate;
        if($prevDate <= $todayDate){
            $ret['prevFlag'] = "N";
        }else{
            $ret['prevFlag'] = "Y";
        }
        /* prev button data End */
        
        /* today data Start */
        $ret['todayMinDate'] = $sunDate;
        $ret['todayMaxDate'] = $satDate;
        /* today data End */
        
        /* next button data Start */
        $ret['nextSetDate'] = $nextDate;
        $nextChkDate = date('Y-m-d', mktime(0,0,0, date('m')+3, date('d')+(6-date('w',strtotime(date('Y-m-d')))), date('Y')));
        
        if($nextDate >= $nextChkDate){
            $ret['nextFlag'] = "N";
        }else{
            $ret['nextFlag'] = "Y";
        }        
        /* next button data End */
        
        /* setDate data Start */
        $ret['setDate'] = array();
        for($i=0; $i<= 6; $i++){
            $ret['setDate'][$i] = substr(date('Y-m-d',mktime(0,0,0,$setMonth,$setDay+$i,$setYear)),8);
        }        
        /* setDate data End */
        $info = $this->Calendar_model->getPensionInfo($mpIdx);
        $roomList = $this->Calendar_model->getRoomList($mpIdx, $idxStrings);
        $reserveList = $this->Calendar_model->getReserveList($mpIdx, $sunDate, $satDate);
        $no = 0;
        $idxStrings = "";
        foreach($roomList as $roomList){
            $ret['lists'][$no]['roomName'] = $roomList['pprName'];
            $ret['lists'][$no]['pprIdx'] = $roomList['pprIdx'];
            $ret['lists'][$no]['flag'] = "";
            for($i=0; $i <=6; $i++){
                $dateFlag = 'N';
                
                for($j=0; $j< count($reserveList); $j++){
                    if(date('Y-m-d',mktime(0,0,0,$setMonth,$setDay+$i,$setYear)) == $reserveList[$j]['ppbDate'] && $roomList['pprIdx'] == $reserveList[$j]['pprIdx']){
                        $dateFlag = 'Y';
                        if($reserveList[$j]['rPaymentState'] == "PS01"){
                            $dateFlag = 'Y';
                        }
                    }
                }
                /*
                if(date('Y-m-d',mktime(0,0,0,$setMonth,$setDay+$i,$setYear)) > date('Y').'-12-31'){                    
                    $dateFlag = 'Y';                    
                }*/
                if($info['ppbReserve'] != "R"){
                    $dateFlag = 'Y';
                }
                $ret['lists'][$no]['flag'] .= "|".$dateFlag;
            }
            $ret['lists'][$no]['flag'] = substr($ret['lists'][$no]['flag'],1);
            $idxStrings .= ",".$roomList['pprIdx'];
            $no++;
        }
        $ret['idxStrings'] = substr($idxStrings,1);
        
        echo json_encode($ret);
    }

    function web() {
        $mpIdx = $this->input->get('mpIdx');        
        $schDate = $this->input->get('schDate');
        $idxStrings = $this->input->get('idxStrings');
        
        /* date setting Start */
        $schYear = substr($schDate,0,4);
        $schMonth = substr($schDate,5,2);
        $schDay = substr($schDate,8,2);
        
        $sunDate = date('Y-m-d',mktime(0,0,0,$schMonth,$schDay-(date('w',strtotime($schDate))),$schYear));
        $setYear = substr($sunDate,0,4);
        $setMonth = substr($sunDate,5,2);
        $setDay = substr($sunDate,8,2); 
        $satDate = date('Y-m-d',mktime(0,0,0,$schMonth,$schDay+(6-date('w',strtotime($schDate))),$schYear));
        $satYear = substr($satDate,0,4);
        $satMonth = substr($satDate,5,2);
        $satDay = substr($satDate,8,2); 
        $nextDate = date('Y-m-d', mktime(0,0,0,$satMonth, $satDay+1, $satYear));
        $prevDate = date('Y-m-d', mktime(0,0,0,$setMonth,$setDay-6,$setYear));
        $todayDate = date('Y-m-d', mktime(0,0,0,date('m'),date('d')-(date('N',strtotime(date('Y-m-d')))), date('Y')));
        
        $ret = array();
        /* date setting End */
        
        /* prev button data Start */
        $ret['prevSetDate'] = $prevDate;
        if($prevDate <= $todayDate){
            $ret['prevFlag'] = "N";
        }else{
            $ret['prevFlag'] = "Y";
        }
        /* prev button data End */
        
        /* today data Start */
        $ret['todayMinDate'] = $sunDate;
        $ret['todayMaxDate'] = $satDate;
        /* today data End */
        
        /* next button data Start */
        $ret['nextSetDate'] = $nextDate;
        $nextChkDate = date('Y-m-d', mktime(0,0,0, date('m')+3, date('d')+(6-date('w',strtotime(date('Y-m-d')))), date('Y')));
        if($nextDate >= $nextChkDate){
            $ret['nextFlag'] = "N";
        }else{
            $ret['nextFlag'] = "Y";
        }        
        /* next button data End */
        
        /* setDate data Start */
        $ret['setDate'] = array();
        for($i=0; $i<= 6; $i++){
            $ret['setDate'][$i] = substr(date('Y-m-d',mktime(0,0,0,$setMonth,$setDay+$i,$setYear)),8);
        }        
        /* setDate data End */
        $info = $this->Calendar_model->getPensionInfo($mpIdx);
        $roomList = $this->Calendar_model->getRoomList($mpIdx, $idxStrings);
        $reserveList = $this->Calendar_model->getReserveList($mpIdx, $sunDate, $satDate);
        $no = 0;
        $idxStrings = "";
        foreach($roomList as $roomList){
            $ret['lists'][$no]['roomName'] = $roomList['pprName'];
            $ret['lists'][$no]['pprIdx'] = $roomList['pprIdx'];
            $ret['lists'][$no]['flag'] = "";
            for($i=0; $i <=6; $i++){
                $dateFlag = 'N';
                
                for($j=0; $j< count($reserveList); $j++){
                    if(date('Y-m-d',mktime(0,0,0,$setMonth,$setDay+$i,$setYear)) == $reserveList[$j]['ppbDate'] && $roomList['pprIdx'] == $reserveList[$j]['pprIdx']){
                        $dateFlag = 'Y';
                        if($reserveList[$j]['rPaymentState'] == "PS01"){
                            $dateFlag = 'W';
                            $dateFlag = 'Y';
                        }
                    }
                }
                if(date('Y-m-d',mktime(0,0,0,$setMonth,$setDay+$i,$setYear)) >= '2015-07-01' && date('Y-m-d',mktime(0,0,0,$setMonth,$setDay+$i,$setYear)) <= '2015-08-31'){
                    if($info['ppbDateCheck'] == "0"){
                        $dateFlag = 'Y';
                    }
                }
                $ret['lists'][$no]['flag'] .= "|".$dateFlag;
            }
            $ret['lists'][$no]['flag'] = substr($ret['lists'][$no]['flag'],1);
            $idxStrings .= ",".$roomList['pprIdx'];
            $no++;
        }
        $ret['idxStrings'] = substr($idxStrings,1);
        
        echo var_dump($ret);
    }
}
?>
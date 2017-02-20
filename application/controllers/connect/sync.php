<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * 개발자 : 김영웅
 * 
 * 파트너 사 펜션정보 Sync 
 */

class Sync extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->model('connect/connect_model');
        $this->reVal = array();
		
		$successIP = array('1.234.38.53','211.119.165.88','222.110.208.78');
		if(!in_array($_SERVER['REMOTE_ADDR'], $successIP)){
			show_404();
			return; exit;
		}
    }
    
	function pension(){
		$mpIdx = $this->input->get_post('index');
		$startDate = $this->input->get_post('start');
		$endDate = $this->input->get_post('end');
		$parnerKey = $this->input->get_post('partner');
		
		if(!$startDate || !$endDate || !$mpIdx || !$parnerKey){
			$this->reVal['ypIdx'] = $mpIdx;
			$this->reVal['message'] = "Missing required value";
			echo json_encode($this->reVal);
			return;
		}
		
		if(!preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $startDate) || !preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $endDate)){ 
		    $this->reVal['ypIdx'] = $mpIdx;
			$this->reVal['message'] = "Date format error";
			echo json_encode($this->reVal);
			return;
		}

		$startDateArray = explode('-', $startDate);
		
		$dayFor = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
		
		if($dayFor > 120){ 
		    $this->reVal['ypIdx'] = $mpIdx;
			$this->reVal['message'] = "Maximum date-out";
			echo json_encode($this->reVal);
			return;
		}
		
		$roomLists = $this->connect_model->getRoomLists($mpIdx, $parnerKey);
		
		$this->reVal['ypIdx'] = (int)$mpIdx;
		$this->reVal['message'] = "";
		$this->reVal['room'] = array();
		$roomNo = 0;
		if(count($roomLists) > 0){
			foreach($roomLists as $roomLists){
				if(!$roomLists[$parnerKey.'Key']){
					$roomLists[$parnerKey.'Key'] = "";
				}
				$this->reVal['room'][$roomNo]['roomIndex'] = (int)$roomLists['pprIdx'];
				$this->reVal['room'][$roomNo]['partnerIndex'] = $roomLists[$parnerKey.'Key'];
				$this->reVal['room'][$roomNo]['data'] = array();
				$blockArray = $this->connect_model->getRoomBlockLists($roomLists['pprIdx'], $startDate, $endDate);
				
				for($i=0; $i< $dayFor; $i++){
					$state = 1;
					$stateMesage = "가능";
					
					$setDate = date('Y-m-d', mktime(0, 0, 0, $startDateArray[1], $startDateArray[2]+$i, $startDateArray[0]));
					
					if(in_array($setDate, $blockArray)){
						$state = 0;
						$stateMesage = "불가";
					}
					$this->reVal['room'][$roomNo]['data'][$i]['date'] = $setDate;
					$this->reVal['room'][$roomNo]['data'][$i]['state'] = (int)$state;
					$this->reVal['room'][$roomNo]['data'][$i]['stateText'] = (string)$stateMesage;
				}
				$roomNo++;
			}
		}else{
			$this->reVal['message'] = "No room available";
		}
		echo json_encode($this->reVal);
	}

	function room(){
		$pprIdx = $this->input->get_post('index');
		$startDate = $this->input->get_post('start');
		$endDate = $this->input->get_post('end');
		$parnerKey = $this->input->get_post('partner');
		
		if(!$startDate || !$endDate || !$pprIdx || !$parnerKey){
			$this->reVal['roomIndex'] = $pprIdx;
			$this->reVal['message'] = "Missing required value";
			echo json_encode($this->reVal);
			return;
		}
		
		if(!preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $startDate) || !preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $endDate)){ 
		    $this->reVal['roomIndex'] = $pprIdx;
			$this->reVal['message'] = "Date format error";
			echo json_encode($this->reVal);
			return;
		}

		$startDateArray = explode('-', $startDate);
		
		$dayFor = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
		
		if($dayFor > 120){ 
		    $this->reVal['roomIndex'] = $pprIdx;
			$this->reVal['message'] = "Maximum date-out";
			echo json_encode($this->reVal);
			return;
		}
		
		$roomInfo = $this->connect_model->getRoomInfo($pprIdx, $parnerKey);
		
		$this->reVal['roomIndex'] = (int)$pprIdx;
		$this->reVal['message'] = "";
		
		if(isset($roomInfo['pprIdx']) && isset($roomInfo[$parnerKey.'Key'])){
			$this->reVal['partnerIndex'] = $roomInfo[$parnerKey.'Key'];
			$this->reVal['data'] = array();
			$blockArray = $this->connect_model->getRoomBlockLists($index, $startDate, $endDate);
			
			for($i=0; $i< $dayFor; $i++){
				$state = 1;
				$stateMesage = "가능";
				
				$setDate = date('Y-m-d', mktime(0, 0, 0, $startDateArray[1], $startDateArray[2]+$i, $startDateArray[0]));
				
				if(in_array($setDate, $blockArray)){
					$state = 0;
					$stateMesage = "불가";
				}
				$this->reVal['data'][$i]['date'] = $setDate;
				$this->reVal['data'][$i]['state'] = (int)$state;
				$this->reVal['data'][$i]['stateText'] = (string)$stateMesage;
			}
		}else{
			$this->reVal['message'] = "Missing required value";
		}
		echo json_encode($this->reVal);
	}
}

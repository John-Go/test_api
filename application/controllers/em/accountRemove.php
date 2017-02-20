<?php
class AccountRemove extends CI_Controller {
	function __construct() {
		parent::__construct();
        $this->load->model('cron/account_remove_model');
        $this->config->load('_msg');
        $this->load->model('em_model');
        $this->load->library('pension_lib');
	}

	function index() {
		/*
			크론탭 설명
			입금 기한이 지난 가상계좌 정보를 찾아,
			그 계좌 정보에 대한 예약정보값을 바꿔주고,
			막힌 방을 다시 풀어줌.
		*/
		$rData = $this->account_remove_model->getList();
        
		if(count($rData) > 0){
			foreach($rData as $rData){
				$this->account_remove_model->uptReservation($rData['rIdx']);
                if($rData['rPriceMileage'] > 0){
                    $this->account_remove_model->MileageReturn($rData['rIdx']);
                }                
                
                $url = "http://api.yapen.co.kr/em/smsSend/rev?rIdx=".$rData['rIdx'];
                    
                $ch = curl_init();    
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $msgText = curl_exec($ch);
                curl_close($ch);
				
				/* 방풀기 start */
				$block_arr = explode("-",$rData['rStartDate']);
                $date_period = round(abs(strtotime($rData['rEndDate'])-strtotime($rData['rStartDate']))/86400);
                for($i=0; $i< $date_period; $i++){
                    $Date = date('Y-m-d',mktime(0,0,0,$block_arr[1],($block_arr[2]+$i),$block_arr[0]));
                    $this->account_remove_model->insBlockConnect($rData['pprIdx'], $Date, "O");
                }
				for($i=0; $i< $date_period; $i++){
				    $Date = date('Y-m-d',mktime(0,0,0,$block_arr[1],($block_arr[2]+$i),$block_arr[0]));
                    $etcCode_arr = $this->account_remove_model->getEtcCode($rData['mpIdx'], $rData['pprIdx'], $Date);
                    $etcCode = $etcCode_arr['ppblEtcCode'];
					$this->account_remove_model->uptPensionBlock($rData['mpIdx'], $rData['pprIdx'], $Date, $etcCode, $rData['rIdx']);
                    
                    $url = "http://www.yapen.co.kr/connect/open";    
                    $fields = array(
                            'pprIdx'=> urlencode($rData['pprIdx']),
                            'Date' => urlencode($Date)
                    );
                    $fields_string = "";
                    foreach($fields as $key=>$value) {
                        $fields_string .= $key.'='.$value.'&';
                    }
                    rtrim($fields_string,'&');
                    
                    $ch = curl_init();
                    
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, count($fields));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    
                    $Engage_Code = curl_exec($ch);            
                    curl_close($ch);
				}
				/* 방풀기 end */
				
				$pInfo = array();
                $pInfo = $this->account_remove_model->getPensionBasicInfo($rData['mpIdx']);
                if($pInfo['ppbMainPension'] == "19" || $pInfo['ppbMainPension'] == "27"){
                    $url = "http://www.yapen.co.kr/connect/room/gPensionBasic";
                    
                    $roomData = array('rCode' => $rData['rCode'], 'type' => 'O');
                    
                    $ch = curl_init();
                
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $roomData);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    
                    curl_exec($ch);            
                    curl_close($ch);
                }
			}
		}
	}
}
?>
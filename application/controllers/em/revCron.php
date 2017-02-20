<?php

class RevCron extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('cron/rev_model');
    }
    
    function feeUpdate1(){        
        $this->rev_model->getRevLists('0');
    }
    
    function feeUpdate2(){
        $this->rev_model->getRevLists('500');
    }
    
    function feeUpdate3(){
        $this->rev_model->getRevLists('1000');
    }
    
    function feeUpdate4(){
        $this->rev_model->getRevLists('1500');
    }
    
    function feeUpdate5(){
        $this->rev_model->getRevLists('2000');
    }
    
    function feeUpdate6(){
        $this->rev_model->getRevLists('2500');
    }
    
    function externalRevCron(){
        $this->load->model('cron/account_remove_model');

		$lists	= $this->rev_model->getReserveState();

		if(count($lists) > 0){
            foreach($lists as $rData){
            	if(isset($rData['ylCancelFlag'])){
            		if($rData['ylCancelFlag'] == "Y" && $rData['ylCancelStart'] <= date('H:i') && date('H:i') <= $rData['ylCancelEnd']){
            			continue;
            		}
            	}
                $this->db->where('rIdx', $rData['rIdx']);
                $this->db->where_in('rRoot', array('RO02', 'RO03'));
                $this->db->where('rPayFlag','Y');
                $this->db->where('rPaymentState','PS01');
                $this->db->set('rPaymentState','PS08');
                $this->db->set('rCancelCheck','1');
                $this->db->set('rCancelDate',date('Y-m-d H:i:s'));
                $this->db->set('rCancelInfo','입금기한 초과로 자동취소');
                $this->db->update('pensionDB.reservation');
				
				if($rData['rVer'] == '1'){
					$this->db->where('rIdx', $rData['rIdx']);
					$this->db->where('rState', 'PS01');
					$infoArrs	= $this->db->get('pensionDB.pensionRevInfo')->result_array();

					foreach($infoArrs as $arr){
						$block_arr = explode("-",$arr['rRevDate']);
						$date_period = 1;

						for($i=0; $i< $date_period; $i++){
							$Date = date('Y-m-d',mktime(0,0,0,$block_arr[1],($block_arr[2]+$i),$block_arr[0]));
							$etcCode_arr = $this->account_remove_model->getEtcCode($arr['mpIdx'], $arr['pprIdx'], $Date);
							$etcCode = $etcCode_arr['ppblEtcCode'];
							$this->account_remove_model->uptPensionBlock($arr['mpIdx'], $arr['pprIdx'], $Date, $etcCode, $arr['rIdx']);
							
							$url = "http://www.yapen.co.kr/connect/open";    
							$fields = array(
									'pprIdx'=> urlencode($arr['pprIdx']),
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
                            
                            if($rData['ppbMainPension'] == "24"){
                                $this->db->where('PPC.pprIdx', $arr['pprIdx']);
                                $this->db->join('pensionDB.placePensionBasic AS PPB','PPB.mpIdx = PPC.mpIdx');
                                $connectInfo = $this->db->get('pensionDB.placePensionConnect AS PPC')->row_array();
                                
                                if($connectInfo['naraKey']){
                                    $url = "http://www.pensionnara.co.kr/change/state.php?key=yapen&room_uid=".$connectInfo['naraKey']."&sdate=".$Date."&edate=".$Date."&state_view=O";
                                            
                                    $ch = curl_init();    
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HEADER, 0);
                                    $resultData = curl_exec($ch);
                                    curl_close($ch);
                                    $resultData = trim(preg_replace("/[^0-9]*/s","",$resultData));
                                    
                                    $returnText = "펜션나라 - 인증코드 없음";
                                    
                                    if($resultData == "4"){
                                        $returnText = "펜션나라 - 객실열기 성공";
                                    }else if($resultData == "1"){
                                        $returnText = "펜션나라 - 시작일 또는 종료일이 오늘날짜보다 이전";
                                    }else if($resultData == "2"){
                                        $returnText = "펜션나라 - 객실번호가 없음";
                                    }else if($resultData == "3"){
                                        $returnText = "펜션나라 - 이미 예약완료";
                                    }else{
                                        $returnText = "펜션나라 - 인증불가(등록된 key값이 아닙니다)";
                                    }
                                    
                                    $this->db->where('rIdx', $arr['rIdx']);
                                    $this->db->where('rlMemo', '펜션나라 로그 : '.$returnText);
                                    $flag = $this->db->count_all_results('pensionDB.reservation_Log');
                                    
                                    if($flag == 0){
                                        $this->db->set('rIdx', $arr['rIdx']);
                                        $this->db->set('mbID','kimyw4');
                                        $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
                                        $this->db->set('rlMemo', '펜션나라 로그 : '.$returnText);
                                        $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
                                        $this->db->insert('pensionDB.reservation_Log');
                                    }else if(substr($_SERVER['REMOTE_ADDR'],0,11) == "211.119.136"){
                                        $this->db->set('rIdx', $arr['rIdx']);
                                        $this->db->set('mbID','kimyw4');
                                        $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
                                        $this->db->set('rlMemo', '펜션나라 로그 : '.$returnText);
                                        $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
                                        $this->db->insert('pensionDB.reservation_Log');
                                    }
                                }
                            }
						}
					}

                    

					// pensionRevInfo update
					$this->db->set('rState', 'PS08');
					$this->db->set('rCancelDate', date('Y-m-d H:i:s'));
					$this->db->set('rCancelInfo','입금기한 초과로 자동취소');
					$this->db->where('rIdx', $rData['rIdx']);
					$this->db->where('rState', 'PS01');
					$this->db->update('pensionDB.pensionRevInfo');
					
					// pensionRevOption update 
					$this->db->set('proState', 'PS08');
					$this->db->set('proCancelDate', date('Y-m-d H:i:s'));
					$this->db->set('proCancelInfo','입금기한 초과로 자동취소');
					$this->db->where('rIdx', $rData['rIdx']);
					$this->db->where('proState', 'PS01');
					$this->db->update('pensionDB.pensionRevOption');

					//if($rData['mpIdx'] != '20107'){
						$this->sendApiSMSNew($rData['rIdx']);
					//}
				}else{
					//if($rData['mpIdx'] != '20107'){
						$this->sendApiSMS($rData['rIdx']);
					//}

					/* ��Ǯ�� start */
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
					/* ��Ǯ�� end */
				}

				
            }
        }
    }

	function sendApiSMS($rIdx, $userSend = '1', $ceoSend = '1')
	{
		$url	= "http://211.119.136.118/em/smsSendNew/rev";
		$fields	= array(
				'rIdx'		=> $rIdx,
				'rUserSend'	=> $userSend,
				'rCeoSend'	=> $ceoSend
		);
		$fields_string	= "";
		foreach($fields as $key => $value) {
			$fields_string	.= $key . '=' . $value . '&';
		}
		rtrim($fields_string,'&');
		
		$ch		= curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$execRes	= curl_exec($ch);
		curl_close($ch);

		return $execRes;
	}

	function sendApiSMSNew($rIdx, $userSend = '1', $ceoSend = '1')
	{
		// ����� �ǿ� ���� �˸��� �߼�
		$url = "http://211.119.136.118/em/send/rev?rIdx=" . $rIdx;
		$smsData = array('rIdx' => $rIdx, 'state' => 'PS08');
		
		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$msgText = curl_exec($ch);
		curl_close($ch);

		return $msgText;
	}
	
	
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * 개발자 : 김영웅
 * 
 * 파트너 사 예약정보 연동
 */

class Rev extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->model('connect/partner_rev_model','rev_model');
        $this->reVal = array();
		
		$this->data = $this->input->post();
		
		//테스트 데이터
		/*
		if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
			$reservation_data = array();
			$reservation_data['partner'] = 8;
			
			//예약 테스트 데이터			
			$reservation_data['data']['revCode'] = date('YmdHis');
			$reservation_data['data']['userName'] = "김영웅";
			$reservation_data['data']['userMobile'] = "01064550315";
			$reservation_data['data']['payPrice'] = 100000;
			$reservation_data['data']['info'] = array();
			$reservation_data['data']['info'][0]['roomIndex'] = "37258";
			$reservation_data['data']['info'][0]['revDate'] = "2017-03-11";
			$reservation_data['data']['info'][1]['roomIndex'] = "37258";
			$reservation_data['data']['info'][1]['revDate'] = "2017-03-12";
			
			
			//취소 테스트 데이터
			//$this->data['data']['revCode'] = '20170210122850';
			
			echo json_encode($reservation_data);
			exit;
		}
		*/
		$this->data = json_decode($this->data, TRUE);
		
		if(isset($this->data['partner'])){
			$this->channel = $this->data['partner'];
		}else{
			$this->channel = 0;
		}
		
		$channelConnectData = array(
			'0' => array(
					'211.119.165.88'
			),
			'8' => array(
					'1.234.38.53',
					'211.119.165.88'
			)
		);
		
		if(!in_array($_SERVER['REMOTE_ADDR'], $channelConnectData[$this->channel])){
			show_404();
			exit; return;
		}
		
		if(!isset($this->data['data']) && $_SERVER['REMOTE_ADDR'] != "211.119.165.88"){
			show_404();
			exit; return;
		}
    }
	
	function success(){
		$revInfo = $this->dataSetting();
		
		if(!isset($revInfo['reservation']) || count($revInfo['pensionRevInfo']) == 0){
			$this->error('No room available');
			exit; return;
		}

		if($revInfo['blockCount'] > 0){
			$this->error('Duplicate reservation');
			exit; return;
		}

		//reservation data insert
		$rIdx = $this->rev_model->insReserve($revInfo);
		
		$url = "http://api.yapen.co.kr/em/send/rev";
		        
        $smsData = array(   'rIdx' => $rIdx,
                            'state' => 'PS02',
                            'rUserSend' => 0,
                            'rCeoSend' => 1,
                            'auth' => 'admin');
            
        $ch = curl_init();    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
		
		$data = array(	"result" => true,
						"message" => "",
						'index' => (string)$rIdx);
						
		echo json_encode($data);
		exit; return;
	}
	
	function cancel(){
		$cancelData = $this->cancelDataSetting();
		
		$rIdx = $this->rev_model->cancelReserve($cancelData);
		
		if(!is_numeric($rIdx)){
			$this->error($rIdx);
		}else{
			$url = "http://api.yapen.co.kr/em/send/rev";
		        
	        $smsData = array(   'rIdx' => $rIdx,
	                            'state' => 'PS07',
	                            'rUserSend' => 0,
	                            'rCeoSend' => 1,
	                            'auth' => 'admin');
	            
	        $ch = curl_init();    
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_exec($ch);
	        curl_close($ch);
			
			$data = array(	'result'=> true,
							"message" => "",
							'index' => (string)$rIdx);
									
			echo json_encode($data);
		}
	}
	
	function error($message){
		$data = array(	"result" => false,
						"message" => $message,
						'index' => (string)$this->data['data']['revCode']);
						
		$message = date('Y-m-d H:i:s')." - [".$this->data['data']['revCode']."] ".$message."
";
		$filename = "/home/site/yanoljaTravel_api/application/logs/partner/ddeonayo/".date("Y-m-d").".log";
        $fp = fopen($filename,"a+");
        fputs($fp,$message);
        fclose($fp);
		
		echo json_encode($data);
		exit; return;
	}
	
	function dataSetting(){
		$result = array();
		$result['blockCount'] = 0;
		
		$reservation = array(
			'mpIdx'				=> '',
			'mbIdx'				=> '',
			'mbID'				=> '',
			'rCode'				=> '',
			'rPension'			=> '',
			'rPersonName'		=> '',
			'rPersonStayName'	=> '',
			'rPersonMobile'		=> '',
			'rPersonEmail'		=> '',
			'rPersonBrithday'	=> '1989-01-01',
			'rRoomingTime'		=> '',
			'rPickupCheck'		=> '0',
			'rPickupTime'		=> '',
			'rRegDate'			=> '',
			'rPersonSi'			=> '서울특별시',
			'rPersonTraffic'	=> 'PT01',
			'rRequestInfo'		=> '',
			'rRegDate'			=> date('Y-m-d H:i:s'),
			'rStatus'			=> 'RS01',
			'rPaymentMethod'	=> 'PM11',
			'rPaymentState'		=> 'PS01',
			'rPrice'			=> '0',
			'rPriceMileage'		=> '0',
			'rPriceCoupon'		=> '0',
			'rAdminFee'			=> '15',
			'rVer'				=> '1',
			'rFee'				=> 'R',
			'rWeekDate'			=> '',
			'rPayFlag'			=> 'N',
			'rReserveFlag'		=> 'M',
			'rRoot'				=> 'RO04',
			'rMainPension'		=> '',
			'rChannel'			=> $this->channel,
			'rPayStep1'			=> '',
			'rPayStep2'			=> ''
		);
		
		$pensionRevInfo = array(
			'mpIdx'				=> '',
			'rCode'				=> '',
			'pprIdx'			=> '',
			'rPensionRoom'		=> '',
			'rBasicPrice'		=> 0,
            'rSalePrice'		=> 0,
            'rCouponPrice'		=> 0,
            'rSerialPrice'		=> 0,
            'rTodayPrice'		=> 0,
            'rEtcPrice'			=> 0,
            'rEventPrice'		=> 0,
            'rPrice'			=> 0,
            'rSitePrice'		=> 0,
            'rState'			=> 'PS01',
            'rRevDate'			=> '',
            'pprInMin'			=> 0,
            'rAddType'			=> 0,
            'rAdult'			=> 0,
            'rYoung'			=> 0,
            'rBaby'				=> 0,
            'rAdultPrice'		=> 0,
            'rYoungPrice'		=> 0,
            'rBabyPrice'		=> 0,
            'ppaIdx'			=> 0,
            'rComm'				=> 0,
            'rPeopleComm'		=> 0,
            'rCommPrice'		=> 0,
            'rPeopleCommPrice'	=> 0,
            'rRegDate'			=> date('Y-m-d H:i:s'),
            'rCancelPrice'		=> 0,
            'rCancelDate'		=> '',
            'rCancelInfo'		=> '',
            'rAff'				=> ''
		);
		
		$pensionRevOption = array(
			'rCode'				=> '',
			'ppoIdx'			=> 0,
			'proName'			=> '',
			'proUnit'			=> '',
			'proBasicPrice'		=> 0,
			'proPrice'			=> 0,
			'proType'			=> 2,
			'proNumber'			=> 0,
			'proComm'			=> 15,
			'proCommPrice'		=> 0,
			'proState'			=> 'PS01',
			'proCancelPrice'	=> 0,
			'proCancelDate'		=> '',
			'proCancelInfo'		=> '',
			'proRefundPrice'	=> 0,
			'proNotCancelFlag'	=> 0,
			'proCancelCheck'	=> 'N'
		);
		
		if($this->channel == 8){
			$result['reservation'] = $reservation;
			$result['reservation']['rCode'] = $this->data['data']['revCode'];
			$result['reservation']['rPersonName'] = trim($this->data['data']['userName']);
			$result['reservation']['rPersonStayName'] = trim($this->data['data']['userName']);
			$result['reservation']['rPersonMobile'] = trim(str_replace('-','',$this->data['data']['userMobile']));
			$result['reservation']['rPaymentState'] = "PS02";
			$result['reservation']['rPrice'] = trim(str_replace(',','',$this->data['data']['payPrice']));
			
			$mpIdx = 0;
			$pensionName = "";
			$payFee = "";
			$weekDate = "";
			
			for($i=0; $i< count($this->data['data']['info']); $i++){
				$roomIndex = $this->data['data']['info'][$i]['roomIndex'];
				$revDate = $this->data['data']['info'][$i]['revDate'];
				
				$result['pensionRevInfo'][$i] = $pensionRevInfo;
				
				$partnerRoomInfo = $this->rev_model->getParnerRoomIndex('ddeonayoKey', $roomIndex);
				
				if(!isset($partnerRoomInfo['pprIdx'])){
					continue;
				}
				
				$info = $this->rev_model->getInfo($partnerRoomInfo['pprIdx']);
				$blockCount = $this->rev_model->getRoomCheck($partnerRoomInfo['pprIdx'], $revDate);
				
				if($blockCount > 0){
					$result['blockCount']++;
					$duplicateData = array(
									'pciIdx' => $this->channel,
									'mpIdx' => $info['mpIdx'],
									'pprIdx' => $info['pprIdx'],
									'rRevDate' => $revDate,
									'partnerCode' => $this->data['data']['revCode'],
									'partnerRoom' => $roomIndex,
									'partnerData' => json_encode($this->data['data']),
									'prpdRegDate' => date('Y-m-d H:i:s'),
									'prpdIP' => $_SERVER['REMOTE_ADDR']
									
					);
					
					$this->rev_model->insPartnerDuplicateLog($duplicateData);
				}
				if($mpIdx == 0){
					$mpIdx = $info['mpIdx'];
					$pensionName = $info['mpsName'];
					$payFee = $info['payFee'];
					$weekDate = date('W', strtotime($revDate));
				}
				
				//객실정보 데이터 삽입
				$result['pensionRevInfo'][$i]['mpIdx'] = $info['mpIdx'];
				$result['pensionRevInfo'][$i]['rCode'] = $this->data['data']['revCode'];
				$result['pensionRevInfo'][$i]['pprIdx'] = $info['pprIdx'];
				$result['pensionRevInfo'][$i]['rPensionRoom'] = $info['pprName'];
				$result['pensionRevInfo'][$i]['rState'] = 'PS02';
				$result['pensionRevInfo'][$i]['rRevDate'] = $revDate;
				$result['pensionRevInfo'][$i]['pprInMin'] = $info['pprInMin'];
				if($info['pprInAddPay'] == "D"){
					$result['pensionRevInfo'][$i]['rAddType'] = 1;
				}else{
					$result['pensionRevInfo'][$i]['rAddType'] = 2;
				}
				
				$result['pensionRevInfo'][$i]['ppaIdx'] = $info['pprAccount'];
				$result['pensionRevInfo'][$i]['rComm'] = $info['payFee'];
				$result['pensionRevInfo'][$i]['rPeopleComm'] = $info['payFee'];
				
				//요금정보 데이터 삽입
				$priceInfo = $this->rev_model->getPriceInfo($info['pprIdx'], $revDate);
				$result['pensionRevInfo'][$i]['rBasicPrice'] = $priceInfo['basicPrice'];
				$result['pensionRevInfo'][$i]['rSalePrice'] = $priceInfo['salePrice'];
				$result['pensionRevInfo'][$i]['rPrice'] = $priceInfo['payPrice'];
				$result['pensionRevInfo'][$i]['rCommPrice'] = ($priceInfo['basicPrice']-$priceInfo['salePrice'])/100*(100-$info['payFee']);
				$result['pensionRevInfo'][$i]['rPeopleCommPrice'] = 0;
			}

			$result['reservation']['mpIdx'] = $mpIdx;
			$result['reservation']['rPension'] = $pensionName;
			$result['reservation']['rAdminFee'] = $payFee;
			$result['reservation']['rWeekDate'] = $rWeekDate;
		}

		return $result;
	}

	function cancelDataSetting(){
		$result = array();
		
		if($this->channel == 8){
			$result['rChannel'] = $this->channel;
			$result['rCode'] = $this->data['data']['revCode'];
			if(isset($this->data['data']['cancelPrice'])){
				if($this->data['data']['cancelPrice'] != ""){
					$result['rCancelPrice'] = $this->data['data']['cancelPrice'];
				}else{
					$result['rCancelPrice'] = "";
				}
			}else{
				$result['rCancelPrice'] = "";
			}
			
			if(isset($this->data['data']['cancelRequest'])){
				$result['rCancelInfo'] = $this->data['data']['cancelRequest'];
			}else{
				$result['rCancelInfo'] = "";
			}
			
			$result['rCacnelDate'] = date('Y-m-d H:i:s');
		}

		return $result;
	}
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pension_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->CI->load->model('_yps/config/lib_model');
	}

	public function paramNummCheck($val1, $val2, $arr){
		if(is_array($arr)){
			if(isset($arr[$val1]))
				return $val1;
			else
				return $val2;
		}else
			return ($val1) ? $val1 : $val2;
	}

	public function travelExpense($expenseMin, $expenseMax){ // 
		if ($expenseMin > 0 && $expenseMax > 0){ 
			if($expenseMin == $expenseMax)
				return number_format($expenseMin)." 원";
			else if($expenseMin == 0 && $expenseMax>0)
				return number_format($expenseMax)." 원";
			else if($expenseMax == 0 && $expenseMin>0)
				return number_format($expenseMin)." 원";
			else
				return number_format($expenseMin)."원 ~ ".number_format($expenseMax)." 원";
		}

		return "";
	}

	public function travelPduParkCheck($val){
		if(!strcmp($val,"F")) return '무료주차';
		if(!strcmp($val,"S")) return '유료주차';
		return '주차불가';
	}

	public function themeInfo($mpsIdx){ // 테마정보 노출

		$result = $this->CI->lib_model->themeInfo($mpsIdx);

		$str = array();
		foreach ($result as $row) {
			$str[] = $row['mtName'];
		}
		
		$str = implode(', ',$str);
		
		return $str;
	}

	public function travelThemeInfo($idx){ // 테마정보 노출

		return $this->CI->lib_model->travelThemeInfo($idx);

	}

	// ************************************************** 실시간방찾기 *****************************************************
	public function reservationDate($date, $num){	// 

		$cd = strtotime($date);
		
		$arrResult = array();
		$arrResult[0] = $date;

		for($i=1; $i<$num; $i++)
			$arrResult[$i] = date('Y-m-d', mktime(0, 0, 0, date('m',$cd), date('d',$cd)+$i, date('Y',$cd))); 
		
		return $arrResult;
	}

	// ************************************************** 실시간방찾기 *****************************************************


	// ************************************************** 공휴일전일 날짜 *****************************************************
	public function reservationHolidayDate($date){	// 
		return $this->CI->lib_model->holidayLists($date[0], $date[sizeof($date)-1]);
		
	}
	// ************************************************** 공휴일전일 날짜 *****************************************************

    public function replacePhone($str){
        $number1 = substr($str,0,3);
        $numberSub1 = 3;
        if(substr($str,0,2) == "02"){
            $number1 = substr($str,0,2);
            $numberSub1 = 2;
        }
        $number2 = substr($str,$numberSub1,4);
        $numberSub2 = 4;
        
        $number3 = substr($str,($numberSub1+$numberSub2));
        if(strlen($number3) == 3){
            $number2 = substr($str,$numberSub1,3);
            $numberSub2 = 3;
            $number3 = substr($str,($numberSub1+$numberSub2));
        }
        
        return $number1."-".$number2."-".$number3;
    }
	
	public function htmlRemove($html){
		$html = str_replace('
','\n', $html);
		$html = str_replace('</div>','\n', $html);
		$html = str_replace('</p>','\n', $html);
		$html = str_replace('\t',' ', $html);
		$html = str_replace('&nbsp;',' ', $html);
		
		$html = strip_tags($html);
		$html = htmlspecialchars_decode($html);
		//$html = nl2br($html);
		
		if(str_replace('\n','',$html) == ""){
			return "";
		}
		
		$html = str_replace('\n\n','\n', $html);
		return $html;
	}
	
	public function htmlRemoveArray($html){
		$html = str_replace('
','\n', $html);
		$html = str_replace('</div>','\n', $html);
		$html = str_replace('</p>','\n', $html);
		$html = str_replace('-','', $html);
		$html = str_replace('*','', $html);
		$html = str_replace('·','', $html);
		$html = str_replace('\t',' ', $html);
		$html = str_replace('&nbsp;',' ', $html);
		$html = strip_tags($html);
		$html = htmlspecialchars_decode($html);
		//$html = nl2br($html);
		if(str_replace('\n','',$html) == ""){
			return array();
		}
		$result = explode('\n', $html);
		$rs = array();
		foreach($result as $key => $val){
			$rs[$key] = trim($val);
		}
		$rs = array_values(array_filter($rs));
		
		return $rs;
	}
	
	function encrypt($plaintext){
		$password = "yanoljaTravel-20131202";
	    // 보안을 최대화하기 위해 비밀번호를 해싱한다.
	    
	    $password = hash('sha256', $password, true);
	    
	    // 용량 절감과 보안 향상을 위해 평문을 압축한다.
	    
	    $plaintext = gzcompress($plaintext);
	    
	    // 초기화 벡터를 생성한다.
	    
	    $iv_source = defined('MCRYPT_DEV_URANDOM') ? MCRYPT_DEV_URANDOM : MCRYPT_RAND;
	    $iv = mcrypt_create_iv(32, $iv_source);
	    
	    // 암호화한다.
	    
	    $ciphertext = mcrypt_encrypt('rijndael-256', $password, $plaintext, 'cbc', $iv);
	    
	    // 위변조 방지를 위한 HMAC 코드를 생성한다. (encrypt-then-MAC)
	    
	    $hmac = hash_hmac('sha256', $ciphertext, $password, true);
	    
	    // 암호문, 초기화 벡터, HMAC 코드를 합하여 반환한다.
	    
	    return base64_encode($ciphertext . $iv . $hmac);
	}
	// 위의 함수로 암호화한 문자열을 복호화한다.
	// 복호화 과정에서 오류가 발생하거나 위변조가 의심되는 경우 false를 반환한다.
	function decrypt($ciphertext){
		$password = "yanoljaTravel-20131202";
	    // 초기화 벡터와 HMAC 코드를 암호문에서 분리하고 각각의 길이를 체크한다.
	    
	    $ciphertext = @base64_decode($ciphertext, true);
	    if ($ciphertext === false) return false;
	    $len = strlen($ciphertext);
	    if ($len < 64) return false;
	    $iv = substr($ciphertext, $len - 64, 32);
	    $hmac = substr($ciphertext, $len - 32, 32);
	    $ciphertext = substr($ciphertext, 0, $len - 64);
	    
	    // 암호화 함수와 같이 비밀번호를 해싱한다.
	    
	    $password = hash('sha256', $password, true);
	    
	    // HMAC 코드를 사용하여 위변조 여부를 체크한다.
	    
	    $hmac_check = hash_hmac('sha256', $ciphertext, $password, true);
	    if ($hmac !== $hmac_check) return false;
	    
	    // 복호화한다.
	    
	    $plaintext = @mcrypt_decrypt('rijndael-256', $password, $ciphertext, 'cbc', $iv);
	    if ($plaintext === false) return false;
	    
	    // 압축을 해제하여 평문을 얻는다.
	    
	    $plaintext = @gzuncompress($plaintext);
	    if ($plaintext === false) return false;
	    
	    // 이상이 없는 경우 평문을 반환한다.
	    
	    return $plaintext;
	}

	// curl 비동기(background에서 동작)
	function curl_post_async($uri, $params = array())
	{
		$command = "curl ";
		if(count($params) > 0){
			foreach ($params as $key => &$val)
				$command .= "-F '$key=$val' ";
		}
		$command .= "$uri -s > /dev/null 2>&1 &";
		passthru($command);
		
		$logData = "
".$uri." - ".$params;
		$filename = "/home/site/yanoljaTravel_api/application/logs/sync/".date("Y-m-d").".log";
		$fp = fopen($filename,"a+");
		fputs($fp,$logData);
		fclose($fp);
	}

	// curl 동기
	function curl_sync($method = "GET", $url, $data = ""){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if($method == "POST"){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$resultData = curl_exec($ch);
		curl_close($ch);

		return $resultData;
	}

	// 제휴사 방막기/열기 연동 (type - O : 열기(예약취소), C : 막기(예약대기/완료), W : G펜션(대기 -> 완료)
	function partner_sync_lib($partner, $pprIdx, $setDate, $type, $revIdx = 0){
		if($partner == "" || $pprIdx == "" || $setDate == "" || $type == ""){
			return;
		}

		$this->CI->load->model('connect/connect_model');

		$nowTime = date("Y-m-d H:i:s");
		$logData = "
".$nowTime."
";
		$info = $this->CI->connect_model->getRoomConnectInfo($pprIdx);

		/* pensionNara Start */
		if($type != "W"){
			if($partner != "YA20151207130233" && isset($info['naraKey'])){
					if(trim($info['naraKey']) != ""){
							$url = "http://www.pensionnara.co.kr/change/state.php?key=yapen&room_uid=".$info['naraKey']."&sdate=".$setDate."&edate=".$setDate."&state_view=".$type;

							$resultData = $this->curl_sync("GET", $url, "");
							$returnData = trim(preg_replace("/[^0-9]*/s","",$resultData));

							$partnerName = "펜션나라";
							if($returnData == "4"){
									$msg = "성공";
									$returnText = $partnerName." : ".$pprIdx." / ".$setDate." / ".$type." / ".$msg;
									$revLog = $partnerName." : ".$info['pprName']." / ".$setDate." / ".$type." / ".$msg;
							}else if($returnData == "1"){
									$msg = "시작일 또는 종료일이 오늘날짜보다 이전";
									$returnText = $partnerName." : ".$pprIdx." / ".$setDate." / ".$type." / ".$msg;
									$revLog = $partnerName." : ".$info['pprName']." / ".$setDate." / ".$type." / ".$msg;
							}else if($returnData == "2"){
									$msg = "객실번호가 없음";
									$returnText = $partnerName." : ".$pprIdx." / ".$setDate." / ".$type." / ".$msg;
									$revLog = $partnerName." : ".$info['pprName']." / ".$setDate." / ".$type." / ".$msg;
							}else if($returnData == "3"){
									$msg = "이미 예약완료";
									$returnText = $partnerName." : ".$pprIdx." / ".$setDate." / ".$type." / ".$msg;
									$revLog = $partnerName." : ".$info['pprName']." / ".$setDate." / ".$type." / ".$msg;
							}else{
									$msg = "인증불가(등록된 key값이 아닙니다)";
									$returnText = $partnerName." : ".$pprIdx." / ".$setDate." / ".$type." / ".$msg;
									$revLog = $partnerName." : ".$info['pprName']." / ".$setDate." / ".$type." / ".$msg;
							}

							$logData .= $returnText;

							// 펜션나라 예약건의 대한 로그
							if($revIdx) $logRs = $this->CI->connect_model->setPartnerRevLog($revIdx, $revLog);
					}
			}
		}
		/* pensionNara End */

		/* G Pension Start */
		if($revIdx){
			if(trim($info['gpKey']) != "" && ($info['ppbMainPension'] == '19' || $info['ppbMainPension'] == '27')){
				// 예약정보
				$revInfo = $this->CI->connect_model->getPartnerRevInfo($revIdx, $pprIdx, $setDate);
				
				// 예약대기 / 완료 (완료의 경우 무통장이 아닐때만)
				if($type == "C"){
					$gCode = "예약성공 API";
					$userMobile = $this->replacePhone($revInfo['rPersonMobile']);
					$userMobileArray = explode('-',$userMobile);

					if($revInfo['rState'] == "PS01") $payFlag = "X";
					else if($revInfo['rState'] == "PS02") $payFlag = "O";

					if($revInfo['rPickupCheck'] == "0") $pickUpCheck = "X";
					else $pickUpCheck = "O";

					$requestInfo = "";
					if($revInfo['rSerialPrice'] > 0) $requestInfo .= ",[연박할인]";
					if($revInfo['rTodayPrice'] > 0) $requestInfo .= ",[당일특가]";

					$requestInfo .= $revInfo['rRequestInfo'];
					if($requestInfo != "") $requestInfo = substr($requestInfo, 1);

					$basicPrice = $revInfo['rBasicPrice']-$revInfo['rSalePrice']-$revInfo['rSerialPrice']-$revInfo['rTodayPrice']+$revInfo['rCouponPrice']+$revInfo['rEtcPrice']+$revInfo['rPriceMileage'];
					$resultPrice = $revInfo['rBasicPrice']-$revInfo['rSalePrice']-$revInfo['rSerialPrice']-$revInfo['rTodayPrice'];

					$sendData = array(
						'partner_id' => 'yapen',
						'pension_id' => '',
						'room_id' => trim($info['gpKey']),
						'charge_flag' => $payFlag,
						'startdate' => $revInfo['rRevDate'],
						'daytype' => 1,
						'name' => base64_encode($revInfo['rPersonName']),
						'hp1' => $userMobileArray[0],
						'hp2' => $userMobileArray[1],
						'hp3' => $userMobileArray[2],
						'email' => $revInfo['rPersonEmail'],
						'birthday' => $revInfo['rPersonBrithday'],
						'adult_num' => ((int)$revInfo['rAdult']+(int)$revInfo['pprInMin']),
						'child_num' => ((int)$revInfo['rYoung']+(int)$revInfo['rBaby']),
						'pickup' => $pickUpCheck,
						'ampm' => '',
						'ar_time' => '',
						'room_price' => $basicPrice,
						'total_price' => $resultPrice,
						'memo' => base64_encode($requestInfo)
					);

					$url = "http://reservation1.gpension.kr/_API/YP/join_room.php";
					$resultData = $this->curl_sync("POST", $url, $sendData);
					$returnData = explode('::',$resultData);

					if($returnData[0] == "S"){
						$prepAffIdx = $returnData[2]." - "; // G펜션 예약번호(주문번호)
						$this->CI->connect_model->setRevEtcPoint($revIdx, $revInfo['priIdx'], $revInfo['rPersonName'], 'P', 'Y', $returnData[2]);
					}
				}

				// G펜션 예약대기 -> 완료 변경
				if($type == "W"){
					$gCode = "입금완료 API";
					$url = "http://reservation1.gpension.kr/_API/YP/confirm_room.php";
					if(isset($revInfo['prepAffIdx'])){
						$prepAffIdx = $revInfo['prepAffIdx']." - "; // G펜션 예약번호(주문번호)
						$sendData = array(
							'partner_id' => 'yapen',
							'order_no' => $revInfo['prepAffIdx']
						);

						$resultData = $this->curl_sync("POST", $url, $sendData);
						$returnData = explode('::',$resultData);
					}
				}

				// G펜션 예약 번호 존재시 취소
				if($type == "O"){
					$gCode = "예약취소 API";
					$url = "http://reservation1.gpension.kr/_API/YP/cancel_room.php";
					if(isset($revInfo['prepAffIdx'])){
						$prepAffIdx = $revInfo['prepAffIdx']." - "; // G펜션 예약번호(주문번호)
						$sendData = array(
							'partner_id' => 'yapen',
							'order_no' => $revInfo['prepAffIdx']
						);
						
						$resultData = $this->curl_sync("POST", $url, $sendData);
						$returnData = explode('::',$resultData);
					}
				}

				if($returnData[1]){
					$revLog = "G펜션 [".$gCode."] : ".$prepAffIdx.$returnData[1];

					// G펜션 예약건의 대한 로그
					$logRs = $this->CI->connect_model->setPartnerRevLog($revIdx, $revLog);
				}
			}
		}
		/* G Pension End */
		
		/* log Create Start */
		if($logData != $nowTime){
			// 전체 로그
			$filename = "/home/site/yanoljaTravel_api/application/logs/partner/".date("Y-m-d").".log";
			$fp = fopen($filename,"a+");
			fputs($fp,$logData);
			fclose($fp);
		}
		/* log Create End */
		
		/* 야놀자/호텔나우 Start */
		
		if($type != "W"){
			// 야놀자
			$yanoljaUrl = YANOLJA_CONNECT_URL."/channel/sync/6/roomtypes/".$pprIdx."/prices/".$setDate;
			$this->curl_post_async($yanoljaUrl);
				
			// 호텔나우 요청 데이터
			$hData = array(
				"room_id" => $pprIdx,
				"set_date" => $setDate
			);

			// 제휴사 싱크 URL
			$syncUrl = array(
				"2" => HOTELNOW_CONNECT_URL."/ynjp/product/room/modify"// 호텔나우
			);
				
			// 싱크 채널 리스트
			$channelList = $this->CI->connect_model->syncChannel($pprIdx);
			
			foreach($channelList as $val){
				$this->curl_post_async($syncUrl[$val["pciIdx"]], json_encode($hData));
			}
		}
		/* 야놀자/호텔나우 End */
	}
	
	function revPenalty($mpIdx, $revDate, $cancelRevDay){
		$this->CI->load->model('connect/connect_model');
		return $this->CI->connect_model->getRevPenalty($mpIdx, $revDate, $cancelRevDay);
    }
}

?>
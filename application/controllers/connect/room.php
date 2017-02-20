<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Room extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('connect/connect_model');
        $this->reVal = array();
    }
    function block(){
        /*
         * 1 : 성공
         * 2 : 이미 막힌 객실 선택
         * 3 : 등록된 객실 없음
         * 4 : 객실 수 불일치
         * 5 : 인증키값 누락
         * 6 : 허용 가능 범위 초과 (2년 초과)
        */

        $blockTest = $this->input->get('blockTest');
        $dataArray = $this->input->post('data');
        $data = json_decode($dataArray);
        $key = $this->input->post('key');
		

        $room = $data->room;
        $setDate = $data->setDate;
        $memo = $data->memo;
        $etc = $data->etc;
		
		if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
			$dataArray = '{"room":["13499"],"setDate":["2017-02-25"],"memo":["\ubc29\ub9c9\uae30-\ub5a0\ub098\uc694\uc804\uccb4"],"etc":[""]}';
			$key = "YA20170210145636";
			$data = json_decode($dataArray);
			
			$room = $data->room;
	        $setDate = $data->setDate;
	        $memo = $data->memo;
	        $etc = $data->etc;
		}

        for($i=0; $i< count($setDate); $i++){
            if($setDate[$i] > (date('Y')+2).'-01-01'){
                $this->reVal['state'] = "6";
                $this->reVal['msg'] = "범위 날짜를 초과하였습니다";
                $logData = $key." - ".date("Y-m-d H:i:s")."
state : ".$this->reVal['state']."
log : ".$this->reVal['msg']."
";

                $this->LogCreate($logData);
                echo json_encode($this->reVal);
                exit;
            }
        }


        $keyArray = $this->connect_model->getMatchKey($key);

        if(isset($keyArray['ppcnPensionKey'])){
            $column = $keyArray['ppcnPensionKey'];
            $affName = $keyArray['ppcnPensionName'];

            $infoLists = $this->connect_model->getPensionInfo($column, $room);

            $roomArray = array();
            $mpIdx = "";
            if(count($infoLists) > 0){
                foreach($infoLists as $infoLists){
                    $roomArray[$infoLists[$column]] = $infoLists['pprIdx'];
                    $mpIdx = $infoLists['mpIdx'];
                }
            }else{
                $this->reVal['state'] = "3";
                $this->reVal['msg'] = "등록된 객실 없습니다";
                $logData = $key." - ".date("Y-m-d H:i:s")."
state : ".$this->reVal['state']."
log : ".implode('/',$room)." | ".implode('/', $setDate).": ".$this->reVal['msg']."
";

                $this->LogCreate($logData);
                echo json_encode($this->reVal);
                exit;
                return;
            }

            if(count($room) != count($setDate)){
                $this->reVal['state'] = "4";
                $this->reVal['msg'] = "객실 수 불일치";
                $logData = $key." - ".date("Y-m-d H:i:s")."
state : ".$this->reVal['state']."
log : ".implode('/',$room)." | ".implode('/', $setDate).": ".$this->reVal['msg']."
";

                $this->LogCreate($logData);
                echo json_encode($this->reVal);
                exit;
                return;
            }

            if($key != "YA20150604103215" && $key != "YA20160309115232"){
                $blockCount = 0;
				if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
					//echo var_dump($roomArray);
				}
                for($i=0; $i< count($setDate); $i++){
                    $blockCheck = $this->connect_model->roomBlockCheck($roomArray[$room[$i]], $setDate[$i]);
                    $blockCount += $blockCheck;
                }

                if($blockCount > 0){
                    $this->reVal['state'] = "2";
                    $this->reVal['msg'] = "이미 막힌 객실 선택";
                    $logData = $key." - ".date("Y-m-d H:i:s")."
state : ".$this->reVal['state']."
log : ".implode('/',$room)." | ".implode('/', $setDate).": ".$this->reVal['msg']."
";

                    $this->LogCreate($logData);
                    echo json_encode($this->reVal);

                    exit;
                    return;
                }
            }


            $this->reVal['state'] = "1";
            $this->reVal['msg'] = "성공";
            $logRoom = "";
            for($i=0; $i< count($setDate); $i++){
                if(!isset($etc[$i])){
                    $etc[$i] = "";
                }
                if(!isset($memo[$i])){
                    $memo[$i] = "";
                }
                $logRoom .= $roomArray[$room[$i]]." / ".$setDate[$i]."
";
                $this->connect_model->setRoomBlock($mpIdx, $roomArray[$room[$i]], $setDate[$i], $affName, $memo[$i], $column, $etc[$i]);

					$this->partner_sync($key, $roomArray[$room[$i]], $setDate[$i], 'C', $revIdx);

            }
        }else{
            $this->reVal['state'] = "5";
            $this->reVal['msg'] = "인증 키값 누락";
        }
        $logData = $key." - ".date("Y-m-d H:i:s")."
state : ".$this->reVal['state']."
log : ".$logRoom;

        $this->LogCreate($logData);
        echo json_encode($this->reVal);
    }

    function open(){
        /*
         * 1 : 성공
         * 2 : 이미 막힌 객실 선택
         * 3 : 등록된 객실 없음
         * 4 : 객실 수 불일치
         * 5 : 인증키값 누락
         * 6 : 허용 가능 범위 초과 (2년 초과)
         * 7 : 해당 객실 예약건 존재
        */
		$blockTest = $this->input->get('blockTest');
        $dataArray = $this->input->post('data');
        $data = json_decode($dataArray);
        $key = $this->input->post('key');

        $room = $data->room;
        $setDate = $data->setDate;
        $memo = $data->memo;
        $etc = $data->etc;

        for($i=0; $i< count($setDate); $i++){
            if($setDate[$i] > (date('Y')+2).'-01-01'){
                $this->reVal['state'] = "6";
                $this->reVal['msg'] = "범위 날짜를 초과하였습니다";
                $logData = $key." - ".date("Y-m-d H:i:s")."
state : ".$this->reVal['state']."
log : ".$this->reVal['msg']."
";

                $this->LogCreate($logData);
                echo json_encode($this->reVal);
                exit;
            }
        }


        $keyArray = $this->connect_model->getMatchKey($key);

        if(isset($keyArray['ppcnPensionKey'])){
            $column = $keyArray['ppcnPensionKey'];
            $affName = $keyArray['ppcnPensionName'];

            $infoLists = $this->connect_model->getPensionInfo($column, $room);

            $roomArray = array();
            $openCount = 0;
            $mpIdx = "";
            $notRoomArray = array();
            $notSetDateArray = array();
            if(count($infoLists) > 0){
                foreach($infoLists as $infoLists){
                    $roomArray[$infoLists[$column]] = $infoLists['pprIdx'];
                    $mpIdx = $infoLists['mpIdx'];
                }
            }else{
                $this->reVal['state'] = "3";
                $this->reVal['msg'] = "등록된 객실 없습니다";
                $logData = $key." - ".date("Y-m-d H:i:s")."
state : ".$this->reVal['state']."
log : ".implode('/',$room)." : ".$this->reVal['msg']."
";

                $this->LogCreate($logData);
                echo json_encode($this->reVal);
                exit;
                return;
            }

            for($i=0; $i< count($setDate); $i++){
                $openCheck = $this->connect_model->roomOpenCheck($roomArray[$room[$i]], $setDate[$i]);

                if($openCheck != 0){
                    $openCount++;
                    array_push($notRoomArray, $room[$i]);
                    array_push($notSetDateArray, $setDate[$i]);
                }
            }

            if(count($room) != count($setDate)){
                $this->reVal['state'] = "4";
                $this->reVal['msg'] = "객실 수 불일치";
                $logData = $key." - ".date("Y-m-d H:i:s")."
state : ".$this->reVal['state']."
log : ".implode('/',$room)." : ".$this->reVal['msg']."
";

                $this->LogCreate($logData);
                echo json_encode($this->reVal);
                exit;
                return;
            }

            if($openCount > 0){
                $this->reVal['state'] = "7";
                $this->reVal['msg'] = "해당 객실 예약 존재";
                $this->reVal['room'] = $notRoomArray;
                $this->reVal['setDate'] = $notSetDateArray;
                $logData = $key." - ".date("Y-m-d H:i:s")."
state : ".$this->reVal['state']."
log : ".implode('/',$room)." : ".$this->reVal['msg']."
";

                $this->LogCreate($logData);
                echo json_encode($this->reVal);
                exit;
                return;
            }

            $this->reVal['state'] = "1";
            $this->reVal['msg'] = "성공";
            $logRoom = "";
            for($i=0; $i< count($setDate); $i++){
                if(!isset($etc[$i])){
                    $etc[$i] = "";
                }
                if(!isset($memo[$i])){
                    $memo[$i] = "";
                }
                $logRoom .= $roomArray[$room[$i]]." / ".$setDate[$i]."
";
                $this->connect_model->setRoomOpen($mpIdx, $roomArray[$room[$i]], $setDate[$i], $affName, $memo[$i], $column, $etc[$i]);

								$this->partner_sync($key, $roomArray[$room[$i]], $setDate[$i], 'O', $revIdx);
            }
        }else{
            $this->reVal['state'] = "5";
            $this->reVal['msg'] = "인증 키값 누락";
        }

        $logData = date("Y-m-d H:i:s")."
key : ".$key."
state : ".$this->reVal['state']."
log : ".$logRoom;
        $this->LogCreate($logData);
        echo json_encode($this->reVal);
    }

    function LogCreate($logData){
        $filename = "/home/site/yanoljaTravel_api/application/logs/connect/".date("Y-m-d").".log";
        $fp = fopen($filename,"a+");
        fputs($fp,$logData);
        fclose($fp);
    }


		// 제휴사 방막기/열기 연동 (type - O : 열기(예약취소), C : 막기(예약대기/완료), W : G펜션(대기 -> 완료)
		function partner_sync($partner, $pprIdx, $setDate, $type, $revIdx = 0){
			$this->load->library('pension_lib');

			if($partner == "" || $pprIdx == "" || $setDate == "" || $type == ""){
					return;
			}
			
			$this->pension_lib->partner_sync_lib($partner, $pprIdx, $setDate, $type, $revIdx);
    }

}

<?php
class AccountCheck extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->config->load('_msg');
        $this->load->model('cron/account_model');
        $this->load->model('em_model');
        $this->load->library('pension_lib');
    }
    
    /*
     *      야놀자펜션 ver 1 을 위한 크론탭
     *      limit - 입금기한 체크 및 입금독려 문자 발송
     *      out - 미입금 자동취소 
     */

    function limit(){
        $rLists = $this->account_model->getLimitLists();
        if(count($rLists) > 0){
            foreach($rLists as $rLists){
                $smsCount = $this->account_model->getSendSmsCheck($rLists['rIdx']);
                            
                if($smsCount == 0){
                    if($rLists['limitTime'] >= "3"){
                        $sendDate = date('Y-m-d H:i:s', strtotime($rLists['ipgm_date']." -1 hour"));
                    }else if($rLists['limitTime'] == "2"){
                        $sendDate = date('Y-m-d H:i:s', strtotime($rLists['ipgm_date']." -1 hour"));
                    }else{
                        $sendDate = date('Y-m-d H:i:s', strtotime($rLists['ipgm_date']." -20 minutes"));
                    }
                    
                    $checkSms = $this->account_model->insSmsCheck($rLists['rIdx'], $rLists['rPersonMobile'], '16444816', $sendDate);
                }            
            }
        }

        $sLists = $this->account_model->getSmsCheckLists();
        if(count($sLists) > 0){
            foreach($sLists as $sLists){
                $rData = $this->account_model->getRevInfo($sLists['rIdx']);
                if(isset($rData['rIdx'])){
                    if($rData['rPaymentState'] == "PS01"){
                        $msgTypeArray = $this->config->item('msgType');
                        $msg = $msgTypeArray['YP_RAW'];
                        
                        $this->em_model->setTalk('YP_RAW', $msg, str_replace('-','',$sLists['receiver']),'U');
                        $this->account_model->uptSmsCheckInfo($sLists['pscIdx']);
                    }else{
                        $this->account_model->delSmsCheck($sLists['pscIdx']);
                    }
                }
            }
        }
    }
    
    function out(){
        $rData = $this->account_model->getList();
        
        if(count($rData) > 0){
            foreach($rData as $rData){
                $this->account_model->uptReservation($rData['rIdx']);
                if($rData['rPriceMileage'] > 0){
                    $this->account_model->MileageReturn($rData['rIdx']);
                }
                
                $lists = $this->account_model->getRevInfoLists($rData['rIdx']);
                
                if(count($lists) > 0){
                    foreach($lists as $lists){
                        //$this->account_model->insBlockConnect($lists['pprIdx'], $lists['rRevDate'], "O");
                        
                        $etcCodeData = $this->account_model->getEtcCode($rData['mpIdx'], $lists['pprIdx'], $lists['rRevDate']);
                        if(!isset($etcCodeData['ppblEtcCode'])){
                            $etcCode = "";
                        }else{
                            $etcCode = $etcCode_arr['ppblEtcCode'];
                        }
                        $this->account_model->uptPensionBlock($rData['mpIdx'], $lists['pprIdx'], $lists['rRevDate'], $etcCode, $rData['rIdx']);
                            
                        $url = "http://www.yapen.co.kr/connect/open";    
                        $fields = array(
                                'pprIdx'=> urlencode($lists['pprIdx']),
                                'Date' => urlencode($lists['rRevDate'])
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
                        curl_close($ch);
                        
                        if($rData['ppbMainPension'] == "19" || $rData['ppbMainPension'] == "27"){
                            $url = "http://reservation1.gpension.kr/_API/YP/cancel_room.php";
                            
                            $gCode = $this->account_model->pensionRevEtcPoint($lists['priIdx']);
                            
                            if(isset($gCode['prepAffIdx'])){
                                $sendData = array(
                                                'partner_id' => 'yapen',
                                                'order_no' => $gCode['prepAffIdx']
                                );
                                $ch = curl_init();
                                
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt($ch, CURLOPT_POST, true);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $sendData);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            
                                $returnText = curl_exec($ch);
                                $returnData = explode('::',$returnText);
                                
                                curl_close($ch);
                                
                                if($returnData[0] == "S"){
                                    $this->account_model->setRevError($lists['rIdx'], '0', $returnData[1]);
                                }else{
                                    $this->account_model->setRevError($lists['rIdx'], '2', $returnData[1]);
                                }
                            }
                        }else if($rData['ppbMainPension'] == "24" && date('Y-m-d H') >= '2016-08-29 14'){
                            $this->account_model->pensionNaraConnect($lists['rIdx'], $lists['pprIdx'], $lists['rRevDate'], "O");
                        }
                    }
                }
                
                $smsData = array('rIdx' => $rData['rIdx'], 'state' => 'PS08');
                
                $url = "http://api.yapen.co.kr/em/send/rev";
            
                $ch = curl_init();    
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $msgText = curl_exec($ch);
                curl_close($ch);
            }
        }

		$revInfo = $this->account_model->getArsLists();

        if(count($revInfo) > 0){
            foreach($revInfo as $revInfo){
            	
                $this->account_model->uptReservation($revInfo['rIdx']);
                if($revInfo['rPriceMileage'] > 0){
                    $this->account_model->MileageReturn($revInfo['rIdx']);
                }
                
                $lists = $this->account_model->getRevInfoLists($revInfo['rIdx']);
                
                if(count($lists) > 0){
                    foreach($lists as $lists){
                        //$this->account_model->insBlockConnect($lists['pprIdx'], $lists['rRevDate'], "O");
                        
                        $etcCodeData = $this->account_model->getEtcCode($revInfo['mpIdx'], $lists['pprIdx'], $lists['rRevDate']);
                        if(!isset($etcCodeData['ppblEtcCode'])){
                            $etcCode = "";
                        }else{
                            $etcCode = $etcCode_arr['ppblEtcCode'];
                        }
                        $this->account_model->uptPensionBlock($revInfo['mpIdx'], $lists['pprIdx'], $lists['rRevDate'], $etcCode, $revInfo['rIdx']);
                            
                        $url = "http://www.yapen.co.kr/connect/open";    
                        $fields = array(
                                'pprIdx'=> urlencode($lists['pprIdx']),
                                'Date' => urlencode($lists['rRevDate'])
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
                        curl_close($ch);
                        
                        if($revInfo['ppbMainPension'] == "19" || $revInfo['ppbMainPension'] == "27"){
                            $url = "http://reservation1.gpension.kr/_API/YP/cancel_room.php";
                            
                            $gCode = $this->account_model->pensionRevEtcPoint($lists['priIdx']);
                            
                            if(isset($gCode['prepAffIdx'])){
                                $sendData = array(
                                                'partner_id' => 'yapen',
                                                'order_no' => $gCode['prepAffIdx']
                                );
                                $ch = curl_init();
                                
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt($ch, CURLOPT_POST, true);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $sendData);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            
                                $returnText = curl_exec($ch);
                                $returnData = explode('::',$returnText);
                                
                                curl_close($ch);
                                
                                if($returnData[0] == "S"){
                                    $this->account_model->setRevError($lists['rIdx'], '0', $returnData[1]);
                                }else{
                                    $this->account_model->setRevError($lists['rIdx'], '2', $returnData[1]);
                                }
                            }
                        }else if($revInfo['ppbMainPension'] == "24" && date('Y-m-d H') >= '2016-08-29 14'){
                            $this->account_model->pensionNaraConnect($lists['rIdx'], $lists['pprIdx'], $lists['rRevDate'], "O");
                        }
                    }
                }
                
                $smsData = array('rIdx' => $revInfo['rIdx'], 'state' => 'PS08');
                
                $url = "http://211.119.136.118/em/send/rev";
            
                $ch = curl_init();    
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $msgText = curl_exec($ch);
                curl_close($ch);
            }
        }
    }
}
        
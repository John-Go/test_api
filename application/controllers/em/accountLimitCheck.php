<?php
class AccountLimitCheck extends CI_Controller {
	function __construct() {
		parent::__construct();
        $this->load->model('cron/account_limit_check_model');
        $this->config->load('_msg');
        $this->load->model('em_model');
        $this->load->library('pension_lib');
	}

	function index() {
		$rLists = $this->account_limit_check_model->getList();
        if(count($rLists) > 0){
            foreach($rLists as $rLists){
                $smsCount = $this->account_limit_check_model->getSendSmsCheck($rLists['rIdx']);
                            
                if($smsCount == 0){
                    $dateFor = round(abs(strtotime($rLists['rStartDate'])-strtotime(substr($rLists['rRegDate'],0,10)))/86400);
                    $limitTime = $this->account_limit_check_model->getLimitTime($dateFor);
                    
                    if($limitTime == "3"){
                        $sendDate = date('Y-m-d H:i:s', strtotime($rLists['LGD_LimitDate']." -1 hour"));
                    }else if($limitTime == "2"){
                        $sendDate = date('Y-m-d H:i:s', strtotime($rLists['LGD_LimitDate']." -1 hour"));
                    }else{
                        $sendDate = date('Y-m-d H:i:s', strtotime($rLists['LGD_LimitDate']." -20 minutes"));
                    }
                    
                    $checkSms = $this->account_limit_check_model->insSmsCheck($rLists['rIdx'], $rLists['rPersonMobile'], '16444816', $sendDate);
                }            
            }
        }

        $sLists = $this->account_limit_check_model->getSmsCheckLists();
        if(count($sLists) > 0){
            foreach($sLists as $sLists){
                if($sLists['pscSendDate'] <= date('Y-m-d H:i:s')){
                    $rData = $this->account_limit_check_model->getRevInfo($sLists['rIdx']);
                    if(isset($rData['rIdx'])){
                        if($rData['rPaymentState'] == "PS01"){
                            $msgTypeArray = $this->config->item('msgType');
                            $msg = $msgTypeArray['YP_RAW'];
                            
                            $this->em_model->setTalk('YP_RAW', $msg, str_replace('-','',$sLists['receiver']),'U');
                            $this->account_limit_check_model->uptSmsCheckInfo($sLists['pscIdx']);
                        }else{
                            $this->account_limit_check_model->delSmsCheck($sLists['pscIdx']);
                        }
                    }
                }
            }
        }
	}
}
?>
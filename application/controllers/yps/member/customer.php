<?php

class Customer extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->library('pension_lib');
        $this->load->model('_yps/member/yapen_model');
		$this->load->model('smsnew_model');
        $this->returnVal = array();
        $this->returnVal['status'] = "1";
        $this->returnVal['failed_message'] = "";
    }

    function login() {
        $mbID = $this->input->get_post('id');
        $mbPW = $this->input->get_post('pw');
        $type = $this->input->get_post('type');
        if(!$mbID || !$type){
            $this->error('필수값 누락');
            return;
        }
        $info = $this->yapen_model->memberLogin($mbID, $mbPW, $type);
        
        if(!isset($info['mbIdx'])){
            if($type != "KA" && $type != "FB"){
                $this->error('아이디 또는 비밀번호가 다릅니다.');
                return;
            }
            if($type == "KA" || $type == "FB"){
                $this->returnVal['state'] = "1";
                echo json_encode($this->returnVal);
                return;
            }
        }
        
        if(str_replace("YP.","",$info['mbID']) != $info['mbEmail']){
            $this->yapen_model->setMemberEmail($info['mbIdx']);
        }
        if(isset($info['mbIdx'])){            
            $this->returnVal['state'] = "2";
        }else{
            $socialFlag = $this->yapen_model->getSocialCheck($mbID, $type);
			
            if($socialFlag > 0){
                $this->returnVal['state'] = "1";
                echo json_encode($this->returnVal);
                return;
            }else{
            	$this->error('연동된 계정이 없습니다.');
                return;
            }
        }
        $this->yapen_model->insMemberMileage($info['mbIdx'], $info['mbID']);
        $info = $this->yapen_model->memberLogin($mbID, $mbPW, $type);
        $this->returnVal['idx'] = $info['mbIdx'];
        $this->returnVal['id'] = str_replace("YP.","",$info['mbID']);
        if(!$info['mbNick']){
            $this->returnVal['nick'] = "";
        }else{
            $this->returnVal['nick'] = $info['mbNick'];
        }
		if(!isset($info['mbBirthday'])){
			$this->returnVal['birthday'] = "";
		}else{
			if(!$info['mbBirthday']){
	            $this->returnVal['birthday'] = "";
	        }else{
	            $this->returnVal['birthday'] = $info['mbBirthday'];
	        }
		}
        
        $this->returnVal['mobile'] = $info['mbMobile'];
        $this->returnVal['mobileAuth'] = $info['mbMobileCertify'];
        if(!$info['mbEmailAgree']){
            $info['mbEmailAgree'] = "N";
        }
        $this->returnVal['mailAgree'] = $info['mbEmailAgree'];
        $this->returnVal['facebookID'] = $info['mbFacebook'];
        $this->returnVal['facebookFlag'] = $info['mbFacebookFlag'];
        $this->returnVal['kakaoID'] = $info['mbKakao'];
        $this->returnVal['kakaoFlag'] = $info['mbKakaoFlag'];
        if(!$info['mbPhoto']){
            $this->returnVal['photo'] = "";
        }else{
            $this->returnVal['photo'] = "http://img.yapen.co.kr/member/".$info['mbPhoto'];
        }
        $this->returnVal['grade'] = $info['mbGrade'];
        if(!$info['mpNowPoint']){
            $this->returnVal['point'] = "0";
        }else{
            if($info['mpNowPoint'] < 0){
                $this->returnVal['point'] = "0";
            }else{
                $this->returnVal['point'] = number_format($info['mpNowPoint'])."";
            }
            
        }
        $this->yapen_model->uptVersion($info['mbIdx']);
        echo json_encode($this->returnVal);
    }

	function logout()
	{
		// load
		$this->load->model('_app/device/device_model');

		// init
		$success	= false;

		// param setting
		$pData	= $this->input->post();
		$pData['mbIdx']		= intval($pData['mbIdx']) ? intval($pData['mbIdx']) : '0';
		$pData['deviceKey']	= $pData['deviceKey'] ? trim($pData['deviceKey']) : '';
		
		if($pData['mbIdx'] != '0' && $pData['deviceKey'] != '')
		{
			$success	= $this->device_model->deviceLogout($pData['mbIdx'], $pData['deviceKey']);
		}
		else
		{
			$this->error('필수값 누락');
			exit;
		}

		if(!$success)
		{
			$this->error('DB 오류 발생하였습니다.');
			exit;
		}

		echo json_encode($this->returnVal);
	}
    
    function create(){
        $pData = $this->input->post();
        /*
        if(isset($pData['photo'])){
            if($pData['photoType'] == "FB" || $pData['photoType'] == "KA"){
                $imgUrl = $pData['photo'];
                $path = pathinfo($imgUrl);
                if(isset($path['extension'])){
                    $extension = strtolower($path['extension']);
                }else{
                    $extension = "jpg";
                }
                $newImage = file_get_contents($imgUrl);
                $fileName = md5($pData['idx']).".".$extension;
                
                $myDir      = "/home/site/yanoljaTravel_api/temp/";
                
                file_put_contents($myDir.$fileName, $newImage);
                
                $this->load->config('_ftp');
                $cfFtp = $this->config->item('image');
                $cfFtp['debug']    = FALSE;
                $this->load->library('ftp', $cfFtp );
                $this->ftp->connect();
        
                $uploadPath = '/home/site/admin/member/';
                $config['image_library'] = 'gd2';
                $config['source_image']  = $myDir.$fileName;
                $config['new_image']     = $myDir.$fileName;
                $config['width']         = 204;
                $config['quality']       = 90;
                $config['maintain_ratio']= TRUE;
                $config['master_dim']    = 'width';
                $this->load->library('image_lib', $config);
                $this->image_lib->initialize($config); 
                $this->image_lib->resize();
                $this->image_lib->clear();
        
                $this->ftp->upload($myDir.$fileName, $uploadPath.$fileName, 'auto', 0775);
        
                unlink($myDir.$fileName);
                
                $this->ftp->close();
                $pData['photo'] = $fileName;
            }
        }
        */
        $pData['photo'] = '';
        $mbIdx = $this->yapen_model->insMemberInfo($pData);
        
        $info = $this->yapen_model->getMemberInfo($mbIdx);
        if(!isset($info['mbIdx'])){
            $this->error('잘못된 접근입니다.');
            return;
        }
        $this->yapen_model->insMemberMileage($info['mbIdx'], $info['mbID']);
        $info = $this->yapen_model->getMemberInfo($mbIdx);
        $this->returnVal['idx'] = $info['mbIdx'];
        $this->returnVal['id'] = str_replace("YP.","",$info['mbID']);
        if(!$info['mbNick']){
            $this->returnVal['nick'] = "";
        }else{
            $this->returnVal['nick'] = rawurlencode($info['mbNick']);
        }
        if(!$info['mbBirthday']){
            $this->returnVal['birthday'] = "";
        }else{
            $this->returnVal['birthday'] = $info['mbBirthday'];
        }
        
        $this->returnVal['mobile'] = $info['mbMobile'];
        $this->returnVal['mobileAuth'] = $info['mbMobileCertify'];
        if(!$info['mbEmailAgree']){
            $info['mbEmailAgree'] = "N";
        }
        $this->returnVal['mailAgree'] = $info['mbEmailAgree'];
        $this->returnVal['facebookID'] = $info['mbFacebook'];
        $this->returnVal['facebookFlag'] = $info['mbFacebookFlag'];
        if(isset($pData['facebookEmail']) && $info['mbFacebook'] != "" && $info['mbFacebookFlag'] == "Y" && $info['mbVer'] == "1" && $info['mbFacebookMileage'] == "N"){
            $this->connectSocial($info['mbIdx'], $info['mbID'], $pData['facebookEmail'], 'FB');
        }
        $this->returnVal['kakaoID'] = $info['mbKakao'];
        $this->returnVal['kakaoFlag'] = $info['mbKakaoFlag'];
        if(isset($pData['kakaoID']) && $info['mbKakao'] != "" && $info['mbKakaoFlag'] == "Y" && $info['mbVer'] == "1" && $info['mbKakaoMileage'] == "N"){
            $this->connectSocial($info['mbIdx'], $info['mbID'], $pData['kakaoID'], 'KA');
        }
        if(!$info['mbPhoto']){
            $this->returnVal['photo'] = "";
        }else{
            $this->returnVal['photo'] = "http://img.yapen.co.kr/member/".$info['mbPhoto'];
        }
        $this->returnVal['grade'] = $info['mbGrade'];
        if(!$info['mpNowPoint']){
            $this->returnVal['point'] = "0";
        }else{
            if($info['mpNowPoint'] < 0){
                $this->returnVal['point'] = "0";
            }else{
                $this->returnVal['point'] = number_format($info['mpNowPoint'])."";
            }
            
        }
        
        
        
        echo json_encode($this->returnVal);
    }

    function createTest(){
        $pData = $this->input->get();
        /*
        if(isset($pData['photo'])){
            if($pData['photoType'] == "FB" || $pData['photoType'] == "KA"){
                $imgUrl = $pData['photo'];
                $path = pathinfo($imgUrl);
                if(isset($path['extension'])){
                    $extension = strtolower($path['extension']);
                }else{
                    $extension = "jpg";
                }
                $newImage = file_get_contents($imgUrl);
                $fileName = md5($pData['idx']).".".$extension;
                
                $myDir      = "/home/site/yanoljaTravel_api/temp/";
                
                file_put_contents($myDir.$fileName, $newImage);
                
                $this->load->config('_ftp');
                $cfFtp = $this->config->item('image');
                $cfFtp['debug']    = FALSE;
                $this->load->library('ftp', $cfFtp );
                $this->ftp->connect();
        
                $uploadPath = '/home/site/admin/member/';
                $config['image_library'] = 'gd2';
                $config['source_image']  = $myDir.$fileName;
                $config['new_image']     = $myDir.$fileName;
                $config['width']         = 204;
                $config['quality']       = 90;
                $config['maintain_ratio']= TRUE;
                $config['master_dim']    = 'width';
                $this->load->library('image_lib', $config);
                $this->image_lib->initialize($config); 
                $this->image_lib->resize();
                $this->image_lib->clear();
        
                $this->ftp->upload($myDir.$fileName, $uploadPath.$fileName, 'auto', 0775);
        
                unlink($myDir.$fileName);
                
                $this->ftp->close();
                $pData['photo'] = $fileName;
            }
        }
        */
        $pData['photo'] = '';
        
        $mbIdx = $this->yapen_model->insMemberInfo($pData);
        
        $info = $this->yapen_model->getMemberInfo($mbIdx);
        if(!isset($info['mbIdx'])){
            $this->error('잘못된 접근입니다.');
            return;
        }
        $this->yapen_model->insMemberMileage($info['mbIdx'], $info['mbID']);
        $info = $this->yapen_model->getMemberInfo($mbIdx);
        $this->returnVal['idx'] = $info['mbIdx'];
        $this->returnVal['id'] = str_replace("YP.","",$info['mbID']);
        if(!$info['mbNick']){
            $this->returnVal['nick'] = "";
        }else{
            $this->returnVal['nick'] = rawurlencode($info['mbNick']);
        }
        if(!$info['mbBirthday']){
            $this->returnVal['birthday'] = "";
        }else{
            $this->returnVal['birthday'] = $info['mbBirthday'];
        }
        
        $this->returnVal['mobile'] = $info['mbMobile'];
        $this->returnVal['mobileAuth'] = $info['mbMobileCertify'];
        if(!$info['mbEmailAgree']){
            $info['mbEmailAgree'] = "N";
        }
        $this->returnVal['mailAgree'] = $info['mbEmailAgree'];
        $this->returnVal['facebookID'] = $info['mbFacebook'];
        $this->returnVal['facebookFlag'] = $info['mbFacebookFlag'];
        if(isset($pData['facebookEmail']) && $info['mbFacebook'] != "" && $info['mbFacebookFlag'] == "Y" && $info['mbVer'] == "1" && $info['mbFacebookMileage'] == "N"){
            $this->connectSocial($info['mbIdx'], $info['mbID'], $pData['facebookEmail'], 'FB');
        }
        $this->returnVal['kakaoID'] = $info['mbKakao'];
        $this->returnVal['kakaoFlag'] = $info['mbKakaoFlag'];
        if(isset($pData['kakaoID']) && $info['mbKakao'] != "" && $info['mbKakaoFlag'] == "Y" && $info['mbVer'] == "1" && $info['mbKakaoMileage'] == "N"){
            $this->connectSocial($info['mbIdx'], $info['mbID'], $pData['kakaoID'], 'KA');
        }
        if(!$info['mbPhoto']){
            $this->returnVal['photo'] = "";
        }else{
            $this->returnVal['photo'] = "http://img.yapen.co.kr/member/".$info['mbPhoto'];
        }
        $this->returnVal['grade'] = $info['mbGrade'];
        if(!$info['mpNowPoint']){
            $this->returnVal['point'] = "0";
        }else{
            if($info['mpNowPoint'] < 0){
                $this->returnVal['point'] = "0";
            }else{
                $this->returnVal['point'] = number_format($info['mpNowPoint'])."";
            }
            
        }
        
        
        
        echo json_encode($this->returnVal);
    }
    
    function update(){
        $pData = $this->input->post();
        /*
        if(isset($pData['photo'])){
            if($pData['photoType'] == "FB" || $pData['photoType'] == "KA"){
                $imgUrl = $pData['photo'];
                $path = pathinfo($imgUrl);
                if(isset($path['extension'])){
                    $extension = strtolower($path['extension']);
                }else{
                    $extension = "jpg";
                }
                $newImage = file_get_contents($imgUrl);
                $fileName = md5($pData['idx']).".".$extension;
                
                $myDir      = "/home/site/yanoljaTravel_api/temp/";
                
                file_put_contents($myDir.$fileName, $newImage);
                
                $this->load->config('_ftp');
                $cfFtp = $this->config->item('image');
                $cfFtp['debug']    = FALSE;
                $this->load->library('ftp', $cfFtp );
                $this->ftp->connect();
        
                $uploadPath = '/home/site/admin/member/';
                $config['image_library'] = 'gd2';
                $config['source_image']  = $myDir.$fileName;
                $config['new_image']     = $myDir.$fileName;
                $config['width']         = 204;
                $config['quality']       = 90;
                $config['maintain_ratio']= TRUE;
                $config['master_dim']    = 'width';
                $this->load->library('image_lib', $config);
                $this->image_lib->initialize($config); 
                $this->image_lib->resize();
                $this->image_lib->clear();
        
                $this->ftp->upload($myDir.$fileName, $uploadPath.$fileName, 'auto', 0775);
        
                unlink($myDir.$fileName);
                
                $this->ftp->close();
                $pData['photo'] = $fileName;
            }
        }
        */
        $this->yapen_model->uptMemberInfo($pData);
        
        $info = $this->yapen_model->getMemberInfo($pData['idx']);
        if(!isset($info['mbIdx'])){
            $this->error('잘못된 접근입니다.');
            return;
        }
        
        $this->returnVal['idx'] = $info['mbIdx'];
        $this->returnVal['id'] = str_replace("YP.","",$info['mbID']);
        if(!$info['mbNick']){
            $this->returnVal['nick'] = "";
        }else{
            $this->returnVal['nick'] = rawurlencode($info['mbNick']);
        }
        if(!$info['mbBirthday']){
            $this->returnVal['birthday'] = "";
        }else{
            $this->returnVal['birthday'] = $info['mbBirthday'];
        }
        
        $this->returnVal['mobile'] = $info['mbMobile'];
        $this->returnVal['mobileAuth'] = $info['mbMobileCertify'];
        if(!$info['mbEmailAgree']){
            $info['mbEmailAgree'] = "N";
        }
        $this->returnVal['mailAgree'] = $info['mbEmailAgree'];
        $this->returnVal['facebookID'] = $info['mbFacebook'];
        $this->returnVal['facebookFlag'] = $info['mbFacebookFlag'];
        if(isset($pData['facebookEmail']) && $info['mbFacebook'] != "" && $info['mbFacebookFlag'] == "Y" && $info['mbVer'] == "1" && $info['mbFacebookMileage'] == "N"){
            $this->connectSocial($info['mbIdx'], $info['mbID'], $pData['facebookEmail'], 'FB');
        }
        $this->returnVal['kakaoID'] = $info['mbKakao'];
        $this->returnVal['kakaoFlag'] = $info['mbKakaoFlag'];
        if(isset($pData['kakaoID']) && $info['mbKakao'] != "" && $info['mbKakaoFlag'] == "Y" && $info['mbVer'] == "1" && $info['mbKakaoMileage'] == "N"){
            $this->connectSocial($info['mbIdx'], $info['mbID'], $pData['kakaoID'], 'KA');
        }
        if(!$info['mbPhoto']){
            $this->returnVal['photo'] = "";
        }else{
            $this->returnVal['photo'] = "http://img.yapen.co.kr/member/".$info['mbPhoto'];
        }
        $this->returnVal['grade'] = $info['mbGrade'];
        if(!$info['mpNowPoint']){
            $this->returnVal['point'] = "0";
        }else{
            if($info['mpNowPoint'] < 0){
                $this->returnVal['point'] = "0";
            }else{
                $this->returnVal['point'] = number_format($info['mpNowPoint'])."";
            }
            
        }

        echo json_encode($this->returnVal);
    }
    
    function updatePassword(){
        $mbPW = $this->input->post('pw');
        $mbIdx = $this->input->post('idx');
        
        $this->yapen_model->uptMemberPassword($mbIdx, $mbPW);
        echo json_encode($this->returnVal);
    }
    
    function delete(){
        $mbIdx = $this->input->post('idx');
        $code = $this->input->post('code');
        $reason = $this->input->post('reason');
        
        if(!$mbIdx || !$reason || !$code){
            $this->error('필수값 누락');
            return;
        }
        $this->yapen_model->uptMemberOut($mbIdx, $code, $reason);
        echo json_encode($this->returnVal);
    }
    
    function connect(){
        $pData = $this->input->post();
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            $pData = $this->input->get();
        }
        /*
        if(isset($pData['photo'])){
            if($pData['photoType'] == "FB" || $pData['photoType'] == "KA"){
                $imgUrl = $pData['photo'];
                $path = pathinfo($imgUrl);
                if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
                    echo var_dump($path);
                }
                if(isset($path['extension'])){
                    $extension = strtolower($path['extension']);
                }else{
                    $extension = "jpg";
                }
                $newImage = file_get_contents($imgUrl);
                $fileName = md5($pData['idx']).".".$extension;
                
                $myDir      = "/home/site/yanoljaTravel_api/temp/";
                
                file_put_contents($myDir.$fileName, $newImage);
                
                $this->load->config('_ftp');
                $cfFtp = $this->config->item('image');
                $cfFtp['debug']    = FALSE;
                $this->load->library('ftp', $cfFtp );
                $this->ftp->connect();
        
                $uploadPath = '/home/site/admin/member/';
                $config['image_library'] = 'gd2';
                $config['source_image']  = $myDir.$fileName;
                $config['new_image']     = $myDir.$fileName;
                $config['width']         = 204;
                $config['quality']       = 90;
                $config['maintain_ratio']= TRUE;
                $config['master_dim']    = 'width';
                $this->load->library('image_lib', $config);
                $this->image_lib->initialize($config); 
                $this->image_lib->resize();
                $this->image_lib->clear();
        
                $this->ftp->upload($myDir.$fileName, $uploadPath.$fileName, 'auto', 0775);
                
                unlink($myDir.$fileName);
                
                $this->ftp->close();
                $pData['photo'] = $fileName;
            }
        }
        */
        $state = $this->yapen_model->uptConnect($pData);
        $this->returnVal['state'] = $state;
        
        if($state == "1"){
            $info = $this->yapen_model->getMemberInfo($pData['idx']);
            $this->returnVal['idx'] = $info['mbIdx'];
            $this->returnVal['facebookID'] = $info['mbFacebook'];
            $this->returnVal['facebookFlag'] = $info['mbFacebookFlag'];
            if(isset($pData['facebookEmail']) && $info['mbFacebook'] != "" && $info['mbFacebookFlag'] == "Y" && $info['mbVer'] == "1" && $info['mbFacebookMileage'] == "N"){
                if(trim($pData['facebookEmail']) != ""){
                    $this->connectSocial($info['mbIdx'], $info['mbID'], $pData['facebookEmail'], 'FB');
                }
                
            }
            $this->returnVal['kakaoID'] = $info['mbKakao'];
            $this->returnVal['kakaoFlag'] = $info['mbKakaoFlag'];
            if(isset($pData['kakaoID']) && $info['mbKakao'] != "" && $info['mbKakaoFlag'] == "Y" && $info['mbVer'] == "1" && $info['mbKakaoMileage'] == "N"){
                $this->connectSocial($info['mbIdx'], $info['mbID'], $pData['kakaoID'], 'KA');
            }
            if(!$info['mbPhoto']){
                $this->returnVal['photo'] = "";
            }else{
                $this->returnVal['photo'] = "http://img.yapen.co.kr/member/".$info['mbPhoto'];
            }
        }
        
        echo json_encode($this->returnVal);
    }
    
    function info(){
        $mbIdx = $this->input->get_post('idx');
        
        if(!$mbIdx){
            $this->error('필수값 누락');
            return;
        }
        
        $info = $this->yapen_model->getMemberInfo($mbIdx);
        if(!isset($info['mbIdx'])){
            $this->error('잘못된 접근입니다.');
            return;
        }
        
        $this->returnVal['idx'] = $info['mbIdx'];
        $this->returnVal['id'] = str_replace("YP.","",$info['mbID']);
        if(!$info['mbNick']){
            $this->returnVal['nick'] = "";
        }else{
            $this->returnVal['nick'] = rawurlencode($info['mbNick']);
        }
        if(!isset($info['mbBirthday'])){
			$this->returnVal['birthday'] = "";
		}else{
			if(!$info['mbBirthday']){
	            $this->returnVal['birthday'] = "";
	        }else{
	            $this->returnVal['birthday'] = $info['mbBirthday'];
	        }
		}
        if(!$info['mbMobile']){
        	$this->returnVal['mobile'] = "";
        }else{
        	$this->returnVal['mobile'] = $info['mbMobile'];
        }
        
        $this->returnVal['mobileAuth'] = $info['mbMobileCertify'];
        if(!$info['mbEmailAgree']){
            $info['mbEmailAgree'] = "N";
        }
        $this->returnVal['mailAgree'] = $info['mbEmailAgree'];
        $this->returnVal['facebookID'] = $info['mbFacebook'];
        $this->returnVal['facebookFlag'] = $info['mbFacebookFlag'];
        $this->returnVal['kakaoID'] = $info['mbKakao'];
        $this->returnVal['kakaoFlag'] = $info['mbKakaoFlag'];
        if(!$info['mbPhoto']){
            $this->returnVal['photo'] = "";
        }else{
            $this->returnVal['photo'] = "http://img.yapen.co.kr/member/".$info['mbPhoto'];
        }
        $this->returnVal['grade'] = $info['mbGrade'];
        if(!$info['mpNowPoint']){
            $this->returnVal['point'] = "0";
        }else{
            if($info['mpNowPoint'] < 0){
                $this->returnVal['point'] = "0";
            }else{
                $this->returnVal['point'] = number_format($info['mpNowPoint'])."";
            }
            
        }
        
        echo json_encode($this->returnVal);
    }
    
    function checkID(){
        $mbID = $this->input->get_post('id');
        
        if(!$mbID){
            $this->error('필수값 누락');
            return;
        }
        
        if(filter_var($mbID, FILTER_VALIDATE_EMAIL) == false){
            $this->error('잘못된 이메일 형식입니다.');
            return;
        }
        
        $count = $this->yapen_model->getEmailCheck($mbID);
        if($count > 0){
            $this->error('중복된 이메일이 존재합니다.');
            return;
        }
        
        echo json_encode($this->returnVal);
    }
    
    function phoneCheck(){
	$mbMobile = $this->input->get_post('mobile');
        if(!$mbMobile){
            $this->error('필수값 누락');
            return;
        }
        
        $checkCount = $this->yapen_model->getMaxSMSCount($mbMobile);
        /*
        if($checkCount > 5){
            $this->error('일일 전송횟수(5)를 초과하였습니다.');
            return;
        }        
        */
        $this->returnVal['number'] = rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        
		$curCfg	= $this->smsnew_model->getPensionMsgTemplateInfo('YP_MCN_1');
		$chArray	= array();
		$chArray['certifyKey']		= '[' . $this->returnVal['number'] . ']'; 

		$chKeyArray		= array_keys($chArray);
		$chValArray		= array_values($chArray);


		array_walk($chKeyArray, array($this, 'changeKeyFormat'));
		
		$msg	= str_replace($chKeyArray, $chValArray, $curCfg['pmtUser']);

		$result	= $this->smsnew_model->sendSMS($msg, preg_replace('/[^0-9]/', '', $mbMobile), 'K', $curCfg['pmtCode']);

//        $content = "야놀자펜션 인증번호 [".$this->returnVal['number']."]를 정확히 입력해 주세요.";
//        
//        $this->yapen_model->sendMobile($mbMobile, $content);
        echo json_encode($this->returnVal);
    }

	function changeKeyFormat(&$val, $key)
	{
		$val	= '#{' . $val . '}';
	}
    
    function error($msg){
        $this->returnVal['status'] = "0";
        $this->returnVal['failed_message'] = rawurlencode($msg);
        
        echo json_encode($this->returnVal);
        
        return;
    }
    
    function connectSocial($mbIdx, $mbID, $basicID, $flag){
        //팁, 예약, 가고싶어요, 1:1질문, 마일리지 이관 로직
        $basicInfo = $this->yapen_model->getSocialID($basicID, $flag);
        
        if(isset($basicInfo['mbIdx'])){
            $this->yapen_model->setSocialData($mbIdx, $mbID, $basicInfo['mbIdx'], $flag);
        }
    }
}
        

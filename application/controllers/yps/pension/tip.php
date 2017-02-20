<?php
class Tip extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('pension_lib');
        $this->load->model('_yps/pension/tip_model');
		$this->reVal = array();
		$this->reVal['status'] = "0";
		$this->reVal['failed_message'] = "실패";
		$this->ynConvert = array('1' => 'Y', '0' => 'N');
    }
	
	function lists(){
		checkMethod('post'); // 접근 메서드를 제한

        $mpIdx = $this->input->post('index');
		$mbIdx = $this->input->post('userIndex');
		$limit = $this->input->post('limit');
		$page  = $this->input->post('page');
		
		if(!$page){
			$page = 1;
		}
		
		if(!$limit){
			$limit = 20;
		}
		
		if(!$mbIdx){
			$mbIdx = "";
		}
        
        $offset = ($page-1)*$limit;
		
        if(!$mpIdx){
        	$this->error->getError('0006');
		}
		
		$listArray = $this->tip_model->getPensionTipLists($mpIdx, $mbIdx, $limit, $offset);
		
		$count = $listArray['count'];
		$lists = $listArray['lists'];
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		$this->reVal['count'] = (int)$count;
		$this->reVal['lists'] = array();
		if(count($lists) > 0){
			$i=0;
			foreach($lists as $lists){
				if($lists['pcIdx'] == ""){
					$complaint = "N";
				}else{
					$complaint = "Y";
				}
				
				$userArray = explode('@',$lists['mbID']);
				$userID = substr($userArray[0],3);
				if(strlen($userArray[0]) > 3){
					$userID = substr($userID,0,3).preg_replace("/[0-9a-zA-Z]/s", "*", substr($userID,3));
				}else{
					$userID = substr($userID,0,1).preg_replace("/[0-9a-zA-Z]/s", "*", substr($userID,1));
				}
				
				if($lists['mbIdx'] == $mbIdx && $mbIdx != ""){
					$modify = "Y";
					$isMy = "Y";
				}else{
					$modify = "N";
					$isMy = "N";
				}
				
				$answer = $this->pension_lib->htmlRemove($lists['ptAnswer']);
				
				if($answer != ""){
					$modify = "N";
				}
				
				$recomm = "N";
				
				if($lists['ptrIdx'] == ""){
					$recomm = "N";
				}else{
					$recomm = "Y";
				}
				
				$this->reVal['lists'][$i]['info'] = array();
				$this->reVal['lists'][$i]['info']['index'] = (int)$lists['ptIdx'];
				$this->reVal['lists'][$i]['info']['regUser'] = $userID;
				$this->reVal['lists'][$i]['info']['regDate'] = date('Y.m.d', strtotime($lists['ptRegDate']));
				$this->reVal['lists'][$i]['info']['buy'] = $this->ynConvert[$lists['ptPointSave']];
				$this->reVal['lists'][$i]['info']['blind'] = $this->ynConvert[$lists['ptBlindFlag']];
				$this->reVal['lists'][$i]['info']['recommendCount'] = number_format($lists['recom']);
				$this->reVal['lists'][$i]['info']['recommend'] = $recomm;
				$this->reVal['lists'][$i]['info']['complaint'] = $complaint;
				$this->reVal['lists'][$i]['info']['modify'] = $modify;
				$this->reVal['lists'][$i]['info']['isMy'] = $isMy;
				
				$this->reVal['lists'][$i]['content'] = array();
				$this->reVal['lists'][$i]['content']['user'] = $this->pension_lib->htmlRemove($lists['ptContent']);
				$this->reVal['lists'][$i]['content']['ceo'] = $answer;
				$i++;
			}
		}
		
		echo json_encode($this->reVal);
	}
	
	function view(){
		checkMethod('post'); // 접근 메서드를 제한

        $ptIdx = $this->input->post('tipIndex');
		$mbIdx = $this->input->post('userIndex');
		
		if(!$ptIdx || !$mbIdx){
        	$this->error->getError('0006');
		}
		
		$info = $this->tip_model->getTipInfo($ptIdx, $mbIdx);
		
		if(!isset($info['ptIdx'])){
			$this->error->getError('0006');
		}
		
		$userArray = explode('@',$info['mbID']);
		$userID = substr($userArray[0],3);
		if(strlen($userArray[0]) > 3){
			$userID = substr($userID,0,3).preg_replace("/[0-9a-zA-Z]/s", "*", substr($userID,3));
		}else{
			$userID = substr($userID,0,1).preg_replace("/[0-9a-zA-Z]/s", "*", substr($userID,1));
		}
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		$this->reVal['info'] = array();
		$this->reVal['info']['pensionName'] = $info['mpsName'];
		$this->reVal['info']['index'] = (int)$info['ptIdx'];
		$this->reVal['info']['regUser'] = $userID;
		$this->reVal['info']['regDate'] = date('Y.m.d', strtotime($info['ptRegDate']));
		$this->reVal['info']['buy'] = $this->ynConvert[$info['ptPointSave']];
		$this->reVal['info']['blind'] = $this->ynConvert[$info['ptBlindFlag']];
		
		$this->reVal['content'] = array();
		$this->reVal['content']['user'] = $this->pension_lib->htmlRemove($info['ptContent']);
		$this->reVal['content']['ceo'] = $this->pension_lib->htmlRemove($info['ptAnswer']);
		
		
		echo json_encode($this->reVal);
	}
	
	function insert(){
		checkMethod('post'); // 접근 메서드를 제한
		
		$mpIdx = $this->input->post('index');
		$mbIdx = $this->input->post('userIndex');
		$content = $this->input->post('content');
		
		if(!$mpIdx || !$mbIdx || !$content){
        	$this->error->getError('0006');
		}
		
		$this->tip_model->insTipInfo($mpIdx, $mbIdx, $content);
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		echo json_encode($this->reVal);
	}
	
	function delete(){
		checkMethod('post'); // 접근 메서드를 제한
		
		$ptIdx = $this->input->post('tipIndex');
		$mbIdx = $this->input->post('userIndex');
		
		if(!$ptIdx || !$mbIdx){
        	$this->error->getError('0006');
		}
		
		$info = $this->tip_model->getTipInfo($ptIdx, $mbIdx);
		
		if(!isset($info['ptIdx'])){
			$this->error->getError('0006');
		}
		
		if($info['mbIdx'] != $mbIdx){
			$this->error->getError('0006');
		}
		
		$this->tip_model->delTipInfo($ptIdx);
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		echo json_encode($this->reVal);
	}
	
	function update(){
		$ptIdx = $this->input->post('tipIndex');
		$mbIdx = $this->input->post('userIndex');
		$content = $this->input->post('content');
		
		if(!$ptIdx || !$mbIdx || !$content){
        	$this->error->getError('0006');
		}
		
		$this->tip_model->uptTipInfo($ptIdx, $mbIdx, $content);
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		echo json_encode($this->reVal);
	}
	
	function user(){
		checkMethod('post'); // 접근 메서드를 제한
		
		$mbIdx = $this->input->post('userIndex');
		$limit = $this->input->post('limit');
		$page  = $this->input->post('page');
		
		if(!$page){
			$page = 1;
		}
		
		if(!$limit){
			$limit = 20;
		}
		
		if(!$mbIdx){
			$mbIdx = "";
		}
        
        $offset = ($page-1)*$limit;
		
		if(!$mbIdx){
        	$this->error->getError('0006');
		}
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		$listArray = $this->tip_model->getUserTipLists($mbIdx, $limit, $offset);
		
		$count = $listArray['count'];
		$lists = $listArray['lists'];
		
		$this->reVal['count'] = (int)$count;
		$this->reVal['lists'] = array();
		
		if(count($lists) > 0){
			$i=0;
			foreach($lists as $lists){
				if($lists['pcIdx'] == ""){
					$complaint = "N";
				}else{
					$complaint = "Y";
				}
				
				$userArray = explode('@',$lists['mbID']);
				$userID = substr($userArray[0],3);
				if(strlen($userArray[0]) > 3){
					$userID = substr($userID,0,3).preg_replace("/[0-9a-zA-Z]/s", "*", substr($userID,3));
				}else{
					$userID = substr($userID,0,1).preg_replace("/[0-9a-zA-Z]/s", "*", substr($userID,1));
				}
				
				if($lists['mbIdx'] == $mbIdx && $mbIdx != ""){
					$modify = "Y";
					$isMy = "Y";
				}else{
					$modify = "N";
					$isMy = "N";
				}
				
				$answer = $this->pension_lib->htmlRemove($lists['ptAnswer']);
				
				if($answer != ""){
					$modify = "N";
				}
				
				$recomm = "N";
				
				if($lists['ptrIdx'] == ""){
					$recomm = "N";
				}else{
					$recomm = "Y";
				}
				
				$this->reVal['lists'][$i]['info'] = array();
				$this->reVal['lists'][$i]['info']['index'] = (int)$lists['ptIdx'];
				$this->reVal['lists'][$i]['info']['regUser'] = $userID;
				$this->reVal['lists'][$i]['info']['pensionName'] = $lists['mpsName'];
				$this->reVal['lists'][$i]['info']['regDate'] = date('Y.m.d', strtotime($lists['ptRegDate']));
				$this->reVal['lists'][$i]['info']['buy'] = $this->ynConvert[$lists['ptPointSave']];
				$this->reVal['lists'][$i]['info']['blind'] = $this->ynConvert[$lists['ptBlindFlag']];
				$this->reVal['lists'][$i]['info']['recommendCount'] = number_format($lists['recom']);
				$this->reVal['lists'][$i]['info']['recommend'] = $recomm;
				$this->reVal['lists'][$i]['info']['complaint'] = $complaint;
				$this->reVal['lists'][$i]['info']['modify'] = $modify;
				$this->reVal['lists'][$i]['info']['isMy'] = $isMy;
				
				$this->reVal['lists'][$i]['content'] = array();
				$this->reVal['lists'][$i]['content']['user'] = $this->pension_lib->htmlRemove($lists['ptContent']);
				$this->reVal['lists'][$i]['content']['ceo'] = $answer;
				$i++;
			}
		}
		
		echo json_encode($this->reVal);
	}
	
	function recommend(){
		checkMethod('post'); // 접근 메서드를 제한

        $ptIdx = $this->input->post('tipIndex');
		$mbIdx = $this->input->post('userIndex');
		
		if(!$ptIdx || !$mbIdx){
        	$this->error->getError('0006');
		}

		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		$result = $this->tip_model->setTipRecommend($ptIdx, $mbIdx);
		
		$this->reVal['failed_message'] = $result['message'];
		$this->reVal['count'] = number_format($result['count']);
		
		echo json_encode($this->reVal);
	}
	
	function complaint(){
		checkMethod('post'); // 접근 메서드를 제한
		
		$mpIdx = $this->input->post('index');
        $ptIdx = $this->input->post('tipIndex');
		$mbIdx = $this->input->post('userIndex');
		
		if(!$mpIdx || !$ptIdx || !$mbIdx){
        	$this->error->getError('0006');
		}

		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		$result = $this->tip_model->setTipComplaint($mpIdx, $ptIdx, $mbIdx);
		if($result == "I"){
			$this->reVal['complaint'] = "Y";
			$this->reVal['failed_message'] = "신고되었습니다.";
		}else{
			$this->reVal['complaint'] = "N";
			$this->reVal['failed_message'] = "신고가 취소되었습니다.";
		}
		
		
		echo json_encode($this->reVal);
	}
}

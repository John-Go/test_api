<?php

class Magazine extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('_yps/content/magazine_model','magazine_model');
        $this->regUser = array('1' => '야놀자펜션', '2' => '섹시한황금주말', '3' => '게스트하우스 소개소','4' => '서울사람연애하기');
    }

    function index() {
        $locCode = urldecode($this->input->post('locCode'));
        $tag = urldecode($this->input->post('tag'));
        $idxStrings = urldecode($this->input->get_post('idxStrings'));
        
        $reVal = array();
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        
        $idxArray = array();
        if($idxStrings){
            if($idxStrings == str_replace(",","",$idxStrings)){                
                $idxArray[0] = $idxStrings;
            }else{
                $idxArray = explode(',', $idxStrings);
            }
        }
        
        $listArray = $this->magazine_model->getMagLists($locCode, $tag, $idxArray);
        
        $lists = $listArray['lists'];
        $reVal['count'] = $listArray['count'];
        
        if(count($lists) > 0){
            $i=0;
            foreach($lists as $lists){
                $reVal['lists'][$i]['title'] = $lists['pmTitle'];
                $reVal['lists'][$i]['image'] = "http://img.yapen.co.kr/pension/mag/".$lists['pmIdx']."/800x0/".$lists['pmiImage'];
                $reVal['lists'][$i]['width'] = "800";
                $reVal['lists'][$i]['height'] = $lists['pmiHeight'];
                $reVal['lists'][$i]['idx'] = $lists['pmIdx'];
                $i++;
                array_push($idxArray, $lists['pmIdx']);
            }
        }
        $reVal['idxStrings'] = implode(",",$idxArray);
        if(substr($reVal['idxStrings'],0,1) == ","){
            $reVal['idxStrings'] = substr($reVal['idxStrings'],1);
        }
        echo json_encode($reVal);
    }

    function location(){
        $reVal = array();
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        
        $lists = $this->magazine_model->getMagLocation();
        
        $i=0;
        foreach($lists as $lists){
            $reVal['lists'][$i]['code'] = $lists['mtCode'];
            $reVal['lists'][$i]['name'] = $lists['mtName'];
            $reVal['lists'][$i]['count'] = $lists['cnt'];
            $i++;
        }
        
        echo json_encode($reVal);
    }
    
    function view(){
        $pmIdx = $this->input->get('idx');
        $reVal = array();
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        if(!$pmIdx){
            $reVal['status'] = "0";
            $reVal['failed_message'] = "필수값 누락";
            echo json_encode($reVal);
            return;
        }
        
        
        $info = $this->magazine_model->getMagInfo($pmIdx);
        $locInfo = $this->magazine_model->getMagLocationInfo($pmIdx);
        $imageLists = $this->magazine_model->getMagImageLists($pmIdx);
        $adLists = $this->magazine_model->gatAdLists();
        
        $reVal['location'] = rawurlencode($locInfo);
        $reVal['title'] = rawurlencode($info['pmTitle']);
        $reVal['content'] = rawurlencode($info['pmContent']);
        $reVal['regUser'] = rawurlencode($this->regUser[$info['pmRegUser']]);
        $reVal['regDate'] = date('Y.m.d H:i:s', strtotime($info['pmRegDate']));
        $reVal['regImage'] = "http://img.yapen.co.kr/pension/mag/images/user_".$info['pmRegUser'].".png";
        $reVal['tag'] = array();
        if($info['pmTag'] == str_replace(",","",$info['pmTag'])){
            $reVal['tag'][0]['tagName'] = rawurlencode($info['pmTag']);
        }else{
            $tagArray = explode(',', $info['pmTag']);
            for($i=0; $i< count($tagArray); $i++){
                $reVal['tag'][$i]['tagName'] = rawurlencode($tagArray[$i]);
            }            
        }
        $reVal['imgCount'] = count($imageLists);
        $reVal['imgLists'] = array();
        if(count($imageLists) > 0){
            $i=0;
            foreach($imageLists as $imageLists){
                $reVal['imgLists'][$i]['url'] = "http://img.yapen.co.kr/pension/mag/".$pmIdx."/800x0/".$imageLists['pmiImage'];
                $reVal['imgLists'][$i]['width'] = "800";
                $reVal['imgLists'][$i]['height'] = $imageLists['pmiHeight'];
                $i++;
            }
        }
        $i=0;
        foreach($adLists as $adLists){
            $reVal['ad'][$i]['pensionName'] = $adLists['pensionName'];
            $reVal['ad'][$i]['idx'] = $adLists['mpIdx'];
            $reVal['ad'][$i]['image'] = 'http://img.yapen.co.kr/pension/basic/'.$adLists['mpIdx'].'/480x0/'.$adLists['ppbImage'];
            
            $i++;
        }
        
        echo json_encode($reVal);
    }
}
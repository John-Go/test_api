<?php

class Pension_exit extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('_yps/cast_model','cast_model');
		$this->load->model('_yps/pension_exit_model','cast_model');
    }

    function index() {
        $dataArray = $this->pension_exit_model->getPensionLists();
		
		
        $lists = $dataArray['lists'];
        $totalCount = $dataArray['count'];
        $reVal = array();
        
        $i=0;
        $reVal['totalCount'] = $totalCount;
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        
        foreach($lists as $lists){
            $reVal['lists'][$i]['idx'] = $lists['pcIdx'];
            $reVal['lists'][$i]['title'] = $lists['pcTitle'];            
            $reVal['lists'][$i]['imageCount'] = $lists['imgCount'];
            $reVal['lists'][$i]['imageUrl'] = "http://img.yapen.co.kr/pension/cast/".$lists['pcIdx']."/800x0/".$lists['pciImage'];
            $reVal['lists'][$i]['imgWidth'] = $lists['pciWidth'];
            $reVal['lists'][$i]['imgHeight'] = $lists['pciHeight'];
            $i++;
        }
        $reVal['count'] = $i;
        echo json_encode( $reVal );
    }

    function view(){
        $pcIdx = $this->input->get('pcIdx');
        $reVal = array();
        
        if(!$pcIdx){
            $reVal['status'] = "0";
            $reVal['failed_message'] = "필수값 누락";
            echo json_encode($reVal);
            return;
        }
        $info = $this->cast_model->getCastInfo($pcIdx);
        
        $imageArray = $this->cast_model->getCastImageLists($pcIdx, '1000');
        $imageLists = $imageArray['lists'];
        $imageCount = $imageArray['count'];
        
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        $reVal['title'] = $info['pcTitle'];        
        $reVal['imgCount'] = $imageCount;
        
        $i=0;
        foreach($imageLists as $imageLists){
            if(isset($imageLists['pciImage'])){
                $reVal['lists'][$i]['imageUrl'] = "http://img.yapen.co.kr/pension/cast/".$pcIdx."/800x0/".$imageLists['pciImage'];                
            }else{
                $reVal['lists'][$i]['imageUrl'] = "http://image2.yanolja.com/pension/new/waterpark/imageReady.png";                
            }
            if($imageLists['pciComment']){
                $reVal['lists'][$i]['content'] = $imageLists['pciComment'];
            }else{
                $reVal['lists'][$i]['content'] = "";
            }
            
            if($imageLists['mpIdx']){
                $reVal['lists'][$i]['mpIdx'] = $imageLists['mpIdx'];
            }else{
                $reVal['lists'][$i]['mpIdx'] = "";
            }
            if($imageLists['pciPensionName']){
                $reVal['lists'][$i]['pensionName'] = $imageLists['pciPensionName'];
            }else{
                $reVal['lists'][$i]['pensionName'] = "";
            }
            $reVal['lists'][$i]['imgWidth'] = $imageLists['pciWidth'];
            $reVal['lists'][$i]['imgHeight'] = $imageLists['pciHeight'];
            $i++;
        }
        
        echo json_encode($reVal);
    }
    
    function more(){
        $reVal = array();
        
        $page = $this->input->get('page');
        if( !$page ) $page = 1;

        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $lists = $this->cast_model->getCastMoreLists($limit, $offset);
        
        $i=0;
        
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        $castType = "N";
        foreach($lists as $lists){
            $reVal['lists'][$i]['idx'] = $lists['pcIdx'];
            $reVal['lists'][$i]['title'] = $lists['pcTitle'];            
            $reVal['lists'][$i]['imageCount'] = $lists['imgCount'];
            if($lists['pciImage']){
                $reVal['lists'][$i]['imageUrl'] = "http://img.yapen.co.kr/pension/cast/".$lists['pcIdx']."/800x0/".$lists['pciImage'];
            }else{
                $reVal['lists'][$i]['imageUrl'] = "http://image2.yanolja.com/pension/new/waterpark/imageReady.png";
            }
            $reVal['lists'][$i]['imgWidth'] = $lists['pciWidth'];
            $reVal['lists'][$i]['imgHeight'] = $lists['pciHeight'];
            
            $i++;
        }
        $reVal['count'] = $i;
        echo json_encode( $reVal );
    }
}
?>
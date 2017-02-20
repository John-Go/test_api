<?php

class Cast extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('_yps/cast_model','cast_model');
    }

    function index() {
        $lists = $this->cast_model->getBestLists();
        
        $totalCount = count($lists);
        $reVal = array();
        
        $i=0;
        $reVal['totalCount'] = $totalCount;
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        
        foreach($lists as $lists){
            $reVal['lists'][$i]['idx'] = $lists['pbIdx'];
            $reVal['lists'][$i]['title'] = $lists['pensionName'];            
            $reVal['lists'][$i]['imageCount'] = "1";
            $reVal['lists'][$i]['imageUrl'] = "http://img.yapen.co.kr/pension/best/".$lists['pbIdx']."/800x0/".$lists['pbiImage'];
            $reVal['lists'][$i]['imgWidth'] = $lists['pbiWidth'];
            $reVal['lists'][$i]['imgHeight'] = $lists['pbiHeight'];
            $i++;
        }
        $reVal['count'] = $i;
        echo json_encode( $reVal );
    }

    function view(){
        $pbIdx = $this->input->get('pcIdx');
        $reVal = array();
        
        if(!$pbIdx){
            $reVal['status'] = "0";
            $reVal['failed_message'] = "필수값 누락";
            echo json_encode($reVal);
            return;
        }
        $lists = $this->cast_model->getBestInfo($pbIdx);
        
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        $reVal['title'] = $lists[0]['pensionName'];        
        $reVal['imgCount'] = count($lists);
        
        $i=0;
        foreach($lists as $lists){
            if(isset($lists['pbiImage'])){
                $reVal['lists'][$i]['imageUrl'] = "http://img.yapen.co.kr/pension/best/".$pbIdx."/800x0/".$lists['pbiImage'];                
            }else{
                $reVal['lists'][$i]['imageUrl'] = "http://image2.yanolja.com/pension/new/waterpark/imageReady.png";                
            }            
            $reVal['lists'][$i]['content'] = "";
            
            if($lists['mpIdx']){
                $reVal['lists'][$i]['mpIdx'] = $lists['mpIdx'];
            }else{
                $reVal['lists'][$i]['mpIdx'] = "";
            }
            if($lists['pensionName']){
                $reVal['lists'][$i]['pensionName'] = $lists['pensionName'];
            }else{
                $reVal['lists'][$i]['pensionName'] = "";
            }
            $reVal['lists'][$i]['imgWidth'] = $lists['pbiWidth'];
            $reVal['lists'][$i]['imgHeight'] = $lists['pbiHeight'];
            $i++;
        }
        
        echo json_encode($reVal);
    }
    
    function more(){
        $reVal = array();
        return;
        exit;
        $page = $this->input->get('page');
        if( !$page ) $page = 1;
        if($page > 1){
            return;
        }
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
<?php

class BestShot extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('_yps/content/best_model');
    }

    function index() {
        $reVal = array();
        
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        
        $lists = $this->best_model->getBestLists();
        
        $reVal['lists'] = array();
        $reVal['totalCount'] = count($lists);
        if(count($lists) > 0){
            $i=0;
            foreach($lists as $lists){
                $reVal['lists'][$i]['idx'] = $lists['pbIdx'];
                $reVal['lists'][$i]['pensionName'] = rawurlencode($lists['pensionName']);
                $reVal['lists'][$i]['mainTitle'] = rawurlencode($lists['pbMainTitle']);
                $reVal['lists'][$i]['mainImage'] = "http://img.yapen.co.kr/pension/best/".$lists['pbIdx']."/".$lists['pbMainImage'];
                $reVal['lists'][$i]['tag'] = explode("|", $lists['pbTag']);
				if($lists['ppbOnline'] == "1"){
					$reVal['lists'][$i]['badge_md'] = "Y";
				}else{
					$reVal['lists'][$i]['badge_md'] = "N";
				}
                $i++;
            }
        }
        
        echo json_encode($reVal);
    }

    function view(){
        $pbIdx = $this->input->get('idx');
        $mbIdx = $this->input->get('mbIdx');
        
        $reVal = array();
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        
        if(!$pbIdx){
            $reVal['status'] = "0";
            $reVal['failed_message'] = "필수값 누락";
            echo json_encode($reVal);
            return;
        }
        
        $info = $this->best_model->getBestInfo($pbIdx);
        if($mbIdx){
            $likeCheck = $this->best_model->getPensionLinkCheck($info['mpIdx'], $mbIdx);
        }else{
            $likeCheck = 0; 
        }
        
        
        $reVal['mpIdx'] = $info['mpIdx'];
        $reVal['reserveType'] = $info['ppbReserve'];
        $reVal['pensionName'] = rawurlencode($info['pensionName']);
        $reVal['mainTitle'] = rawurlencode($info['pbMainTitle']);
        $reVal['mainImage'] = "http://img.yapen.co.kr/pension/best/".$pbIdx."/".$info['pbMainImage'];
        $reVal['tag'] = explode("|", $info['pbTag']);
        $reVal['wantCount'] = (int)$info['ppbWantCnt'];
        
        if($likeCheck == 0){
            $reVal['wantCheck'] = "N";
        }else{
            $reVal['wantCheck'] = "Y";
        }
         
        $reVal['title'] = rawurlencode($info['pbTitle']);
        $reVal['content'] = $info['pbContent'];
        
        
        $reVal['secLists'] = array();
        $roomStyle = array();
        $landScape = array();
        $facilities = array();
        $service = array();
        
        $reVal['roomCount'] = $info['ppbRoom'];
        
        $reVal['address'] = $info['mpsAddr1']." ".$info['mpsAddr2'];
        
        $reVal['themeLists'] = explode("|",$this->best_model->getThemeLists($info['mpsIdx']));
        
        $roomLists = $this->best_model->getRoomLists($info['mpIdx'], $pbIdx);
        $checkArray = array();
        if(count($roomLists) > 0 ){
            $i=0;
            $j=0;
            foreach($roomLists as $roomLists){
                if(isset($checkArray[$roomLists['pprIdx']])){
                	$imageCount = count($roomStyle[$checkArray[$roomLists['pprIdx']]]['imageLists']); 
                    $roomStyle[$checkArray[$roomLists['pprIdx']]]['imageLists'][$imageCount]['image'] = "http://img.yapen.co.kr/pension/best/".$pbIdx."/".$roomLists['pbpImage'];
                    $roomStyle[$checkArray[$roomLists['pprIdx']]]['imageLists'][$imageCount]['width'] = $roomLists['pbpImageWidth'];
                    $roomStyle[$checkArray[$roomLists['pprIdx']]]['imageLists'][$imageCount]['height'] = $roomLists['pbpImageHeight'];
                }else{
                    $checkArray[$roomLists['pprIdx']] = $i;
                    $imageCount=0;
                    $roomStyle[$checkArray[$roomLists['pprIdx']]]['name'] = $roomLists['pprName'];
                    $roomStyle[$checkArray[$roomLists['pprIdx']]]['imageLists'][$imageCount]['image'] = "http://img.yapen.co.kr/pension/best/".$pbIdx."/".$roomLists['pbpImage'];
                    $roomStyle[$checkArray[$roomLists['pprIdx']]]['imageLists'][$imageCount]['width'] = $roomLists['pbpImageWidth'];
                    $roomStyle[$checkArray[$roomLists['pprIdx']]]['imageLists'][$imageCount]['height'] = $roomLists['pbpImageHeight'];
                    $i++;
                }
            }
        }

        $landLists = $this->best_model->getLandLists($pbIdx);
        if(count($landLists) > 0 ){
            $i=0;
            foreach($landLists as $landLists){
                $landScape[0]['name'] = "";
                $landScape[0]['imageLists'][$i]['image'] = "http://img.yapen.co.kr/pension/best/".$pbIdx."/".$landLists['pbpImage'];
                $landScape[0]['imageLists'][$i]['width'] = $landLists['pbpImageWidth'];
                $landScape[0]['imageLists'][$i]['height'] = $landLists['pbpImageHeight'];
                $i++;
            }
        }

        $facLists = $this->best_model->getFacLists($info['mpIdx'], $pbIdx);
        $checkArray = array();
        if(count($facLists) > 0 ){
            $i=0;
            $j=0;
            foreach($facLists as $facLists){
                if(isset($checkArray[$facLists['ppeIdx']])){
                	$imageCount = count($facilities[$checkArray[$facLists['ppeIdx']]]['imageLists']);
					
                    $facilities[$checkArray[$facLists['ppeIdx']]]['imageLists'][$imageCount]['image'] = "http://img.yapen.co.kr/pension/best/".$pbIdx."/".$facLists['pbpImage'];
                    $facilities[$checkArray[$facLists['ppeIdx']]]['imageLists'][$imageCount]['width'] = $facLists['pbpImageWidth'];
                    $facilities[$checkArray[$facLists['ppeIdx']]]['imageLists'][$imageCount]['height'] = $facLists['pbpImageHeight'];
                }else{
                    $checkArray[$facLists['ppeIdx']] = $i;
                    $imageCount = 0;
					
                    $facilities[$checkArray[$facLists['ppeIdx']]]['name'] = $facLists['ppeName'];
                    $facilities[$checkArray[$facLists['ppeIdx']]]['imageLists'][$imageCount]['image'] = "http://img.yapen.co.kr/pension/best/".$pbIdx."/".$facLists['pbpImage'];
                    $facilities[$checkArray[$facLists['ppeIdx']]]['imageLists'][$imageCount]['width'] = $facLists['pbpImageWidth'];
                    $facilities[$checkArray[$facLists['ppeIdx']]]['imageLists'][$imageCount]['height'] = $facLists['pbpImageHeight'];
                    $i++;
                }
            }
        }
        
        $serviceLists = $this->best_model->getService($pbIdx);
        if(count($serviceLists) > 0){
            $i=0;
            foreach($serviceLists as $serviceLists){
                $service[$i] = rawurlencode($serviceLists['pbsContent']);
                $i++;
            }
        }
        
        $secNameArray = array('R' => 'roomStyle', 'L' => 'landScape', 'F' => 'facilities', 'S' => 'service');
        $secTitleArray = array('R' => 'ROOM STYLE', 'L' => 'LANDSCAPE', 'F' => 'FACILITIES', 'S' => 'SERVICE');
        
        $secArray = explode("|", $info['pbSecSort']);
        $no = 0;
        for($i=0; $i< count($secArray); $i++){
            if(count(${$secNameArray[$secArray[$i]]}) > 0){
                if($secArray[$i] == "S"){
                    $reVal['secLists'][$no]['type'] = "T";
                }else{
                    $reVal['secLists'][$no]['type'] = "P";
                }
                $reVal['secLists'][$no]['secTitle'] = $secTitleArray[$secArray[$i]];
                $reVal['secLists'][$no]['lists'] = ${$secNameArray[$secArray[$i]]};
                $no++;
            }
        }
        
        echo json_encode($reVal);
    }
}
?>
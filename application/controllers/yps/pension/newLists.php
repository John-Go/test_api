<?php
class NewLists extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('_yps/pension/Pension_model','pension_model');
    }

    function index(){        
        $idxStrings = $this->input->post('idxStrings');

        if(!$idxStrings){
            $idxString = array();
        }else{
            $idxString = explode(',', $idxStrings);
        }
        
        $reVal['status'] = '1';
        $reVal['failed_message'] = '';
        
        $lists = $this->pension_model->getNewLists($idxString);
        $reVal['count'] = $lists['count'];
        if(count($lists) > 0){
            $i=0;
            foreach($lists['lists'] as $lists){
                $location = explode(' ', $lists['mpsAddr1']);
                $reVal['lists'][$i]['mpIdx'] = $lists['mpIdx'];
                $reVal['lists'][$i]['location'] = rawurlencode($location[0]." ".$location[1]);
                $reVal['lists'][$i]['pensionName'] = rawurlencode($lists['mpsName']);
                $reVal['lists'][$i]['pensionImage'] = "http://img.yapen.co.kr/pension/basic/".$lists['mpIdx']."/800x0/".$lists['ppbImage'];
                $reVal['lists'][$i]['pensionOpenDate'] = date('Y.m', strtotime($lists['ppbOpenDate']));
                $reVal['lists'][$i]['tag'] = "";
                $reVal['lists'][$i]['adFlag'] = "N";
                array_push($idxString, $lists['mpIdx']);
                $i++;
            }
        }
        $reVal['idxStrings'] = implode(',',$idxString);
        
        echo json_encode($reVal);
    }

    function ad(){
        $reVal['status'] = '1';
        $reVal['failed_message'] = '';
        $idxString = array();
        
        $lists = $this->pension_model->getNewAdLists();
        
        $reVal['count'] = $lists['count'];
        if(count($lists) > 0){
            $i=0;
            foreach($lists['lists'] as $lists){
                $location = explode(' ', $lists['mpsAddr1']);
                $reVal['lists'][$i]['idx'] = $lists['pnIdx'];
                $reVal['lists'][$i]['mpIdx'] = $lists['mpIdx'];
                $reVal['lists'][$i]['location'] = rawurlencode($location[0]." ".$location[1]);
                $reVal['lists'][$i]['pensionName'] = rawurlencode($lists['mpsName']);
                $reVal['lists'][$i]['pensionImage'] = "http://img.yapen.co.kr/pension/newPension/".$lists['pnIdx']."/".$lists['pnImage'];
                if($lists['pniImage'] == ""){
                    $reVal['lists'][$i]['newImage'] = "";
                }else{
                    $reVal['lists'][$i]['newImage'] = "http://img.yapen.co.kr/pension/newPension/".$lists['pnIdx']."/".$lists['pniImage'];
                }
                
                $reVal['lists'][$i]['pensionOpenDate'] = date('Y.m', strtotime($lists['ppbOpenDate']));
                $reVal['lists'][$i]['tag'] = rawurlencode($lists['pnTag']);
                //tag array 형태로 변형
                $tagArray = explode(" #", substr(str_replace("  "," ", " ".$lists['pnTag']),2));
                $reVal['lists'][$i]['tagArray'] = $tagArray;
                $reVal['lists'][$i]['adFlag'] = "Y";
                if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $lists['ppbReserve'] == "R") ||
                	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
                    $percent = 100-floor(($lists['resultPrice']-($lists['resultPrice']*0.02))/$lists['basicPrice']*100);
                    $reVal['lists'][$i]['percent'] = $percent;
                }else{
                    $reVal['lists'][$i]['percent'] = round($lists['resultPrice']/$lists['basicPrice']*100,0);
                }
				
				if($lists['ptsSale'] > 0){
					$reVal['lists'][$i]['todaySale'] = "Y";
				}else{
					$reVal['lists'][$i]['todaySale'] = "N";
				}
				
				if($lists['ppbOnline'] == "1"){
					$reVal['lists'][$i]['badge_md'] = "Y";
				}else{
					$reVal['lists'][$i]['badge_md'] = "N";
				}
                
                array_push($idxString, $lists['mpIdx']);
                $i++;
            }
        }

        $reVal['idxStrings'] = implode(',',$idxString);
        
        echo json_encode($reVal);
    }

    function view(){
        $pnIdx = $this->input->get('idx');
        if( !$pnIdx ) $this->error->getError('0006'); // Key가 없을경우
        $reVal = array();
        $reVal['status'] = '1';
        $reVal['failed_message'] = '';
        
        $info = $this->pension_model->getNewAdInfo($pnIdx);
        $image = $this->pension_model->getNewAdImageLists($pnIdx);
        
        $location = explode(' ', $info['mpsAddr1']);
        $reVal['mpIdx'] = $info['mpIdx'];
        $reVal['location'] = rawurlencode($location[0]." ".$location[1]);
        $reVal['pensionName'] = rawurlencode($info['mpsName']);
        $reVal['reserveType'] = $info['ppbReserve'];
        $reVal['pensionOpenDate'] = date('Y.m', strtotime($info['ppbOpenDate']));
        $reVal['tag'] = rawurlencode($info['pnTag']);
        //tag array 형태로 변형
        $tagArray = explode(" #", substr(str_replace("  "," ", " ".$info['pnTag']),2));
        $reVal['tagArray'] = $tagArray;
        $reVal['adFlag'] = "Y";
        $reVal['percent'] = $info['percent'];
        
        if(count($image) > 0){
            $imageNo = 0;
            foreach($image as $image){
                $reVal['imageLists'][$imageNo]['image'] = "http://img.yapen.co.kr/pension/newPension/".$info['pnIdx']."/".$image['pniImage'];
                $reVal['imageLists'][$imageNo]['width'] = "800";
                $reVal['imageLists'][$imageNo]['height'] = $image['pniHeight'];
                $imageNo++;
            }
        }
        
        echo json_encode($reVal);
    }
}
?>
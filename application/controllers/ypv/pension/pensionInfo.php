<?php
class PensionInfo extends CI_Controller {    
    public function __construct(){
        parent::__construct();
        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_ypv/pension_model','pension_model');
    }
    
    function index(){
        $mpIdx = $this->input->get('mpIdx');
        if($mpIdx == ""){
            $reVal['status'] = "2";
            $reVal['failed_message'] = "펜션 Idx 누락";
        }else{
            $reVal = array();
        
            $info = $this->pension_model->getPensionInfo($mpIdx);
            
            $reVal['status'] = "1";
            $reVal['failed_message'] = "";
            $reVal['mpIdx'] = $info['mpIdx'];
            $reVal['pensionName'] = rawurlencode($info['mpsName']);
            $reVal['price'] = number_format($info['price'])."";
            $reVal['percent'] = number_format($info['percent'])."";
            $reVal['reserveFlag'] = $info['ppbReserve'];
            $reVal['location'] = rawurlencode($info['mpsAddr1']." ".$info['mpsAddr2']);
            if(!$info['theme']){
                $themeText = "";
            }else{
                $themeArray = explode(",", $info['theme']);
                $themeText = "";
                for($i=0; $i< count($themeArray); $i++){
                    $themeText .= ", ".$themeArray[$i];
                    if($i == 6){
                        break;
                    }
                }
                
                if($themeText != ""){
                    $themeText = substr($themeText, 2);
                }
            }
            $reVal['themeName'] = rawurlencode($themeText);
            $reVal['pensionUrl'] = urlencode("http://".$info['pvbPageUrl']);
            if(!$info['mpsTelService']){
                $reVal['pensionTel'] = "16444816";
            }else{
                $reVal['pensionTel'] = $info['mpsTelService'];
            }
            
            $reVal['coordX'] = $info['mpsMapX'];
            $reVal['coordY'] = $info['mpsMapY'];
            $reVal['grade'] = $info['pvbGrade'];  
            $photoLists = $this->pension_model->pensionAllPhotoLists($mpIdx);
            $ret['imageCount'] = count($photoLists);
            $ret['imageList'] = array();
            if(count($photoLists) > 0){
                $i = 0;
                foreach($photoLists as $photoLists){
                    if($photoLists['photoType'] == "E"){
                        $reVal['imageList'][$i]['imageUrl'] = 'http://img.yapen.co.kr/pension/etc/'.$mpIdx.'/800x0/'.$photoLists['imageUrl'];
                    }else{
                        $reVal['imageList'][$i]['imageUrl'] = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$photoLists['imageUrl'];
                    }
                    $i++;
                }
            }
            /*
            $reVal['imageCount'] = 0;            
            $imgListResult = $this->pension_model->pensionReprEtcImageLists($mpIdx, 0, 9999);
            $reVal['imageCount'] = $reVal['imageCount'] + $imgListResult['count'];
            $imgNum = 0;
            if(count($imgListResult['query']) > 0){
                foreach ($imgListResult['query'] as $row) {
                    $reVal['imageList'][$imgNum]['imageUrl'] = urlencode('http://img.yapen.co.kr/pension/etc/'.$mpIdx.'/800x0/'.$row['ppepFileName']);
                    $imgNum++;
                }
            }
            
            $imgListResult = $this->pension_model->pensionImageLists($mpIdx, 0, 9999);
            $reVal['imageCount'] = $reVal['imageCount'] + $imgListResult['count'];
            if(count($imgListResult['query']) > 0){
                foreach ($imgListResult['query'] as $row) {
                    $reVal['imageList'][$imgNum]['imageUrl'] = urlencode('http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$row['pprpFileName']);
                    $imgNum++;
                }
            }*/   
        }             
        
        echo json_encode($reVal);        
    }
}

?>
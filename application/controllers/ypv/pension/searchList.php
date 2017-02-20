<?php
class SearchList extends CI_Controller {    
    public function __construct(){
        parent::__construct();
        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_ypv/pension_model','pension_model');
    }
    
    function index(){
        $schText = urldecode($this->input->get_post('schText'));
        $idxStrings = urldecode($this->input->get_post('idxStrings'));
        
        if($idxStrings != ""){
            $idxStrings = explode(",",$idxStrings);
        }else{
            $idxStrings = array();
        }
        
        $themeCode = $this->pension_model->getThemeCode($schText);
        
        $listsArray = $this->pension_model->getSchPensionList($schText, $themeCode, $idxStrings);
        
        $lists = $listsArray['list'];
        
        $reVal = array();
        $reVal['totCount'] = $listsArray['totCount'];
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        
        $i = 0;
        $idxStringVal = "";
        if(count($lists) > 0){
            foreach($lists as $lists){
                $location = explode(" ", $lists['mpsAddr1']);
                $priceArray = explode('|',$lists['price']);
                $reVal['lists'][$i]['mpIdx'] = $lists['mpIdx'];
                $reVal['lists'][$i]['imageUrl'] = "http://img.yapen.co.kr/pension/etc/".$lists['mpIdx']."/".$lists['ppbImage'];
                $reVal['lists'][$i]['pensionName'] = $lists['mpsName'];                
                $reVal['lists'][$i]['location'] = $location[0]." ".$location[1];
                $reVal['lists'][$i]['grade'] = $lists['pvbGrade'];
                $reVal['lists'][$i]['reserveFlag'] = $lists['ppbReserve'];
                $reVal['lists'][$i]['price'] = number_format($priceArray[0])."";
                $reVal['lists'][$i]['percent'] = $priceArray[1]."";
                
                $idxStringVal .= ",".$lists['mpIdx'];
                $i++;
            }
        }
        if(count($idxStrings) > 0){
            $reVal['idxStrings'] = substr($idxStringVal,1).",".implode(',',$idxStrings);
        }else{
            $reVal['idxStrings'] = substr($idxStringVal,1);
        }
        
        
        echo json_encode($reVal);
    }
}

?>
<?php
class MainList extends CI_Controller {    
    public function __construct(){
        parent::__construct();
        $this->load->model('_ypv/pension_model','pension_model');
    }
    
    function index(){
        $schLocation = $this->input->get_post('schLocation');
        $idxStrings = urldecode($this->input->get_post('idxStrings'));
        
        if($idxStrings != ""){
            $idxStrings = explode(",",$idxStrings);
        }else{
            $idxStrings = array();
        }
        
        $listsArray = $this->pension_model->getMainPensionList($schLocation, $idxStrings);
        
        $lists = $listsArray['list'];
        
        $reVal = array();
        $reVal['totCount'] = $listsArray['totCount'];
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        $reVal['lists'] = array();
        $i = (int)0;
        $idxStringVal = "";
        if(count($lists) > 0){
            foreach($lists as $lists){
                $location = explode(" ", $lists['mpsAddr1']);
                $pensionNameArray = explode(' ',$lists['mpsName']);
                
                $tag = "";
                if($lists['pvbTag'] != ""){
                    if($lists['pvbTag'] == str_replace(",","",$lists['pvbTag'])){
                        $tag = $lists['pvbTag'];
                    }else{
                        $tagArray = explode(",", $lists['pvbTag']);
                        for($j=0; $j< count($tagArray); $j++){
                            if($j == 0){
                                $tag .= $tagArray[$j];
                            }else{
                                $tag .= " · ".$tagArray[$j];
                            }
                            
                        }
                    }
                }
                $reVal['lists'][$i]['mpIdx'] = $lists['mpIdx'];
                $reVal['lists'][$i]['imageUrl'] = "http://img.yapen.co.kr/villa/etc/".$lists['mpIdx']."/".$lists['pvbImage'];
                $reVal['lists'][$i]['pensionName'] = $pensionNameArray[1];
                $reVal['lists'][$i]['location'] = $location[0]." ".$location[1];
                $reVal['lists'][$i]['grade'] = $lists['pvbGrade'];
                $reVal['lists'][$i]['tag'] = $tag;
                
                $idxStringVal .= ",".$lists['mpIdx'];
                $i++;
            }
        }
        $reVal['idxStrings'] = substr($idxStringVal,1);
        
        echo json_encode($reVal);
    }
}

?>
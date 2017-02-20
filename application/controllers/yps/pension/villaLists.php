<?php
class VillaLists extends CI_Controller {
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
        
        $lists = $this->pension_model->getVillaLists($idxString);
        $reVal['count'] = $lists['count'];
        if(count($lists) > 0){
            $i=0;
            foreach($lists['lists'] as $lists){
                $location = explode(' ', $lists['mpsAddr1']);
                $reVal['lists'][$i]['mpIdx'] = $lists['mpIdx'];
                $reVal['lists'][$i]['location'] = rawurlencode($location[0]." ".$location[1]);
                $reVal['lists'][$i]['pensionName'] = rawurlencode($lists['mpsName']);
                $reVal['lists'][$i]['pensionImage'] = "http://img.yapen.co.kr/pension/basic/".$lists['mpIdx']."/800x0/".$lists['ppbImage'];
                array_push($idxString, $lists['mpIdx']);
                $i++;
            }
        }
        $reVal['idxStrings'] = implode(',',$idxString);
        
        echo json_encode($reVal);
    }

    
}
?>
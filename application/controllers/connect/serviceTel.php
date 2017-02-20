<?php
class ServiceTel extends CI_Controller {
    function __construct() {
        parent::__construct();
    }
    
    function setData(){
        $telNumber = $this->input->post('privateNum');
        $serviceTel = $this->input->post('serviceTel');
        $type = $this->input->post('type');
        
        if($type == "I"){
            $url = "http://infra.yanolja.com/phonebook/public?privateNum=".$telNumber."&type=3";
            $arrowType = "POST";
        }else if($type == "D"){
            $url = "http://infra.yanolja.com/phonebook/public?publicNum=".$serviceTel."&type=3";
            $arrowType = "DELETE";
        }else if($type == "S"){
            $url = "http://infra.yanolja.com/phonebook/private?publicNum=".$serviceTel;
            $arrowType = "GET";
        }else{
            return;
        }
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $arrowType);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            'Content-Type: application/json; charset=UTF-8',
                                            'Connection: Keep-Alive',
                                            "access-control-allow-origin:*"
                                            ));
        $connectData = curl_exec($ch);
        
        curl_close($ch);
        
        print_r($connectData);
    }
}
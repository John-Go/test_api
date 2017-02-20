<?php

class Search_random extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_yps/pension/pension_model');
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
    }

    function index() {
        checkMethod('get'); // 접근 메서드를 제한

        // $idx = $this->input->get('idx');
        // if( !$idx ) $this->error->getError('0006');  // Key가 없을경우
        // $this->error->getError('0005');  // 정보가 없을경우

        $page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 5, NULL);

        $offset = ($page - 1) * $limit;

        $result = $this->pension_model->randomBannerList(array(
                                                            'limit'=>$limit,
                                                            'offset'=>$offset
                                                        ));

        if(!$result['count'])
            $this->error->getError('0005'); // 정보가 없을경우


        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $result['count']."";

        $no = 0;
        foreach ($result['query'] as $row) {
            $ret['lists'][$no]["idx"] = $row['mpIdx'];          // 펜션키
            $ret['lists'][$no]["image"] = 'http://img.yapen.co.kr/pension/mobile/'.$row['arbFilename'];     // 이미지경로
            $ret['lists'][$no]["location"] = $row['mpsAddrSi'].' '.$row['mpsAddrGun'];// $row['mpsAddr1'];       // 지역
            $ret['lists'][$no]["name"] = $row['mpsName'];       // 펜션명
            $ret['lists'][$no]["content"] = ""; // 테마정보
            $ret['lists'][$no]["basket_cnt"] = $row['ppbWantCnt'];  // 가보고 싶어요          
            $ret['lists'][$no]["review"] = "";                  // 리뷰
            $ret['lists'][$no]["imgWidth"] = $row['arbWidth'];
            $ret['lists'][$no]["imgHeight"] = $row['arbHeight'];
            $ret['lists'][$no]["tag"] = rawurlencode(str_replace("|"," · ",$row['arbTag']));
            $ret['lists'][$no]["basicPrice"] = number_format($row['basicPrice']);
            if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $row['ppbReserve'] == "R") ||
            	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
                $ret['lists'][$no]["resultPrice"] = number_format(floor(($row['resultPrice']-($row['resultPrice']*0.02))/10*10));
                $percent = 100-floor(($row['resultPrice']-($row['resultPrice']*0.02))/$row['basicPrice']*100);
                $ret['lists'][$no]["percent"] = $percent;
            }else{
                $ret['lists'][$no]["resultPrice"] = number_format($row['resultPrice']);
                $ret['lists'][$no]["percent"] = round(100-($row['resultPrice']/$row['basicPrice']*100),0);
            }
             
			if($row['ptsSale'] > 0){
			 	$ret['lists'][$no]["todaySale"] = "Y";
			}else{
				$ret['lists'][$no]["todaySale"] = "N";
			}
			
			if($row['ppbOnline'] == "1"){
				$ret['lists'][$no]["badge_md"] = "Y";
			}else{
				$ret['lists'][$no]["badge_md"] = "N";
			}
            

            $no++;
        }
        
        echo json_encode( $ret );
        //$this->output->enable_profiler();

    }
}
?>
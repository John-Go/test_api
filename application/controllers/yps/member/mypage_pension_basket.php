<?php
class Mypage_pension_basket extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_yps/member/member_model');
        $this->load->model('_yps/pension/pension_model');
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
    }

    function test() {
        checkMethod('get'); // ���� �޼��带 ����

        $mbIdx = $this->input->get('mbIdx');
        if( !$mbIdx ) $this->error->getError('0006');   // Key�� �������

        $page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 1000, NULL);
        
        $offset = ($page - 1) * $limit;


        $result = $this->member_model->pensionBasketList(array(
                        'mbIdx'     => $mbIdx,
                        'offset'    => $offset,
                        'limit'     => $limit
                    )
        ); // ��

        if( empty( $result ) ) $this->error->getError('0005');  // ������ �������

        $no = 0;
        $ret = array();
        $ret['status'] = '1';
        $ret['failed_message'] = '';
        $ret['tnt_cnt'] = $result['count'];

        foreach( $result['obj'] as $k => $o ) {
            
            $pensionPriceInfo = $this->pension_model->pensionMinPrice($o->mpIdx);
            $address = explode(" ",$o->mpsAddr1);
            $ret['lists'][$k]["idx"]                = $o->mpIdx;            // ���Ű
            $ret['lists'][$k]["image"]          = 'http://img.yapen.co.kr/pension/etc/'.$o->mpIdx.'/'.$o->ppbImage;     // �̹������
            $ret['lists'][$k]["image_cnt"]  = $this->pension_model->pensionImageCount( $o->mpIdx );
            $ret['lists'][$k]["location"]       = $address[0]." ".$address[1];  // ��������
            $ret['lists'][$k]["name"]               = $o->mpsName;      // ��Ǹ�
            $ret['lists'][$k]["content"]        = $this->pension_lib->themeInfo($o->mpsIdx);    // �׸�����
            $ret['lists'][$k]["price"]          = $pensionPriceInfo->minPrice;  // �̿���
            $ret['lists'][$k]["review"]         = $this->basket_model->getPensionBasketCountByMpIdx($o->mpIdx);                 // ���� 
            $ret['lists'][$k]["sales"]          = $pensionPriceInfo->maxSalePercent;                    // ���Ͽ�� ??????????????????????????????????????????????????????????
        }

        echo json_encode( $ret );


        //$this->output->enable_profiler();

    }

    function index(){
        checkMethod('get');

        $mbIdx = $this->input->get('mbIdx');
        if(!$mbIdx){
            $this->error->getError('0006');
        }
        
        $page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 1000, NULL);
        
        $offset = ($page - 1) * $limit;
        
        $data = $this->member_model->memberBasketLists($mbIdx, $limit, $offset);
        
        $ret = array();
        $ret['status'] = '1';
        $ret['failed_message'] = '';
        $ret['tnt_cnt'] = $data['count'];

        $no = 0;
        $ret['lists'] = array();
        foreach($data['lists'] as $lists) {
            $address = explode(" ",$lists['mpsAddr1']); 
            $ret['lists'][$no]["idx"]            = $lists['mpIdx'];
            $ret['lists'][$no]["image"]          = 'http://img.yapen.co.kr/pension/etc/'.$lists['mpIdx'].'/'.$lists['ppbImage'];
            $ret['lists'][$no]["image_cnt"]      = 1;
            $ret['lists'][$no]["location"]       = $address[0]." ".$address[1];
            $ret['lists'][$no]["name"]           = $lists['mpsName'];
            $ret['lists'][$no]["content"]        = "";
			if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $lists['ppbReserve'] == "R") ||
				($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
                $ret['lists'][$no]["price"] = 		number_format(floor(($lists['resultPrice']-($lists['resultPrice']*0.02))/10)*10)."";  // 이용요금
                $percent = 100-floor(($lists['resultPrice']-($lists['resultPrice']*0.02))/$lists['basicPrice']*100);
                $ret['lists'][$no]["sales"]          = $percent."";
            }else{
                $ret['lists'][$no]["price"]          = number_format($lists['resultPrice']); 
                $ret['lists'][$no]["sales"]		     = round(100-($lists['resultPrice']/$lists['basicPrice']*100),0)."";                 // 세일요금
            }
			
            $ret['lists'][$no]["review"]         = $lists['ppbWantCnt'];
            $ret['lists'][$no]["reserve"]        = $lists['ppbReserve'];
			if($lists['ptsSale'] > 0){
            	$ret['lists'][$no]["todaySale"]   = "Y";   //당일특가 여부
            }else{
            	$ret['lists'][$no]["todaySale"]   = "N";   //당일특가 여부
            }
            $no++;
        }

        echo json_encode( $ret );
    }

    function delBasket() {
        $mpIdx  = $this->input->post('mpIdx');
        $mbIdx  = $this->input->post('mbIdx');
        if( !$mpIdx || !$mbIdx ) $this->error->getError('0006');    // Key�� �������

        $result = $this->member_model->pensionBasketDelete(array(
                                                                                                                'mpIdx' => $mpIdx,
                                                                                                                'mbIdx' => $mbIdx
                                                                                                                        )
                                                                                                            );

        
        $ret['status'] = '1';
        $ret['failed_message'] = '';

        echo json_encode( $ret );

    }
}
?>
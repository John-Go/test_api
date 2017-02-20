<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//
// version 1.0.10
// 


class Donut_Book {
	private $URL;
	private $COOPER_ID;
	private $COOPER_PW;
	private $SITE_ID;
	
	//초기화시 계정정보를 가져옴
	public function init( $param=NULL ){
		$this->URL			= 'https://cms.donutbook.co.kr/b2ccoupon/b2cservice.aspx';
		//$this->URL			= 'https://cms.donutbook.co.kr/b2ccoupon/b2cservice.aspx';
		$this->COOPER_ID	= $param['COOPER_ID'];
		$this->COOPER_PW	= $param['COOPER_PW'];
		$this->SITE_ID		= $param['SITE_ID'];
	}

	private function curl( $data=array() ){
		$querystring = '?';
		$querystring .= 'COOPER_ID='.$this->COOPER_ID;
		$querystring .= '&COOPER_PW='.$this->COOPER_PW;
		$querystring .= '&SITE_ID='.$this->SITE_ID;

		//URL을 만듬
		foreach( $data as $key => $row ){
			$querystring .= '&'.$key.'='.$row;
		}

		//CURL GET방식으로 긁어옴
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->URL . $querystring);
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$read = curl_exec($ch);

		//CURL 통신 실패시
		if( curl_error($ch) ){
			return FALSE;			
		}else curl_close($ch);

		//배열형태로 가공함
		return json_decode( json_encode( simplexml_load_string($read) ));
	}

	//상품다운로드
	public function downloadGoods( $param=NULL ){
		//if( !$param ){
		//	echo '도넛북통신에 필요한 값 들이 없습니다.';
		//	return false;
		//}

		//초기화
		$ret = array();

		//다운로드 액션
		$param['action'] = 'CC01_DOWN_ALL_GOODSINFO';

		//통신
		$data = $this->curl( $param );

		//검증
		if( !isset($data->RTMSG) ){
			//echo '통신 실패';
			return false;
		}

		//검증
		if( $data->RTMSG != 'SUCCESS' ){
			//echo '통신 실패';
			return false;
		}

		//상품수를 비교하여 일치하지 않으면 폐기
		if( $data->LIST_COUNT != count($data->GOODS_LIST->GOODS_INFO) ){
			//echo '상품의 수가 일치하지 않아 폐기됨.';
			return false;
		}

		//상품목록
		foreach( $data->GOODS_LIST->GOODS_INFO as $row ){
			$obj =& $ret[];
			$obj['NO_REQ']					= $row->NO_REQ;
			$obj['NM_REQ']					= $row->NM_REQ;
			$obj['VALID_START']				= $row->VALID_START;
			$obj['VALID_END']				= $row->VALID_END;
			$obj['NO_GOODS']				= $row->NO_GOODS;
			$obj['NM_GOODS']				= $row->NM_GOODS;
			$obj['SITE_ID']					= $row->SITE_ID;
			$obj['GOODS_COMPANY']			= $row->GOODS_COMPANY;
			$obj['NM_GOODS_COMPANY']		= $row->NM_GOODS_COMPANY;
			$obj['GOODS_COMPANY_CHARGE']	= $row->GOODS_COMPANY_CHARGE;
			$obj['GOODS_PRICE']				= $row->GOODS_PRICE;
			$obj['GOODS_CNT']				= $row->GOODS_CNT;
			$obj['CPN_PRICE']				= $row->CPN_PRICE;
			$obj['DISCOUNT_PRICE']			= $row->DISCOUNT_PRICE;
			$obj['GOODS_DISCOUNT']			= $row->GOODS_DISCOUNT;
			$obj['YN_CHANGED']				= $row->YN_CHANGED;
			$obj['CHANGED_DATE']			= $row->CHANGED_DATE;
			$obj['REG_DATE']				= $row->REG_DATE;
			$obj['GOODS_IMAGE']				= $row->GOODS_IMAGE;
		}


		return $ret;
	}

	//쿠폰발송
	public function sendCoupon( $param=NULL ){
		if( !$param ){
			//echo '도넛북통신에 필요한 값 들이 없습니다.';
			return false;
		}

		//초기화
		$ret = array();

		//쿠폰발송
		$param['ACTION'] = 'CI102_ISSUECPN_WITHPAY';

		//통신
		$data = $this->curl( $param );

		//검증
		if( !isset($data->RTMSG) ){
			//echo '통신 실패';
			return false;
		}

		//검증
		if( $data->RTMSG != 'SUCCESS' ){
			//echo '통신 실패';
			return false;
		}

		//카운트를 비교함
		if($data->ISSUE_COUNT != count($data->CPN_LIST)){
			//echo '발급된 쿠폰수와 요청수가 일치하지 않습니다.';
			return false;
		}

		//쿠폰발급한걸 가공하여 반환함
		foreach( $data->CPN_LIST as $row ){
			$obj =& $ret[];
			$obj['NO_CPN']	= $row->NO_CPN;
			$obj['NO_AUTH'] = (is_object($row->NO_AUTH))?@current(@get_object_vars($row->NO_AUTH)):$row->NO_AUTH;
			$obj['CPN_PW']	= $row->CPN_PW;
			$obj['TS_ID']	= $row->TS_ID;
		}

		return $ret;
	}

	//쿠폰재발송
	function resendCoupon( $param=NULL){
		if( !$param ){
			//echo '도넛북통신에 필요한 값 들이 없습니다.';
			return false;
		}

		//쿠폰발송
		$param['ACTION'] = 'CI103_RETRY_TOSENDCPN';

		//통신
		$data = $this->curl( $param );

		//검증
		if( !isset($data->RTMSG) ){
			//echo '통신 실패';
			return false;
		}

		//검증
		if( $data->RTMSG != 'SUCCESS' ){
			//echo '통신 실패';
			return false;
		}

		return true;
	}

	//쿠폰취소
	function cancelCoupon( $param=NULL ){
		if( !$param ){
			//echo '도넛북통신에 필요한 값 들이 없습니다.';
			return false;
		}

		//쿠폰번호로 취소
		$param['ACTION'] = 'CI104_DISUSECPN';

		//취소사유는 URLENCODE 해서 보내야됨
		$param['REASON'] = urlencode($param['REASON']);

		//통신
		$data = $this->curl( $param );

		//검증
		if( $data->RTMSG != 'SUCCESS' ){
			//echo '통신 실패';
			return false;
		}

		return true;

	}

	//쿠폰구매내역 조회(아이디로 조회)
	function listCouponById( $param=NULL ){
		if( !$param ){
			//echo '도넛북통신에 필요한 값 들이 없습니다.';
			return false;
		}

		//쿠폰발송
		$param['ACTION'] = 'CI05_QUERY_CPNLIST';

		//통신
		$data = $this->curl( $param );

		//검증
		if( !isset($data->RTMSG) ){
			//echo '통신 실패';
			return false;
		}

		//검증
		if( $data->RTMSG != 'SUCCESS' ){
			//echo '통신 실패';
			return false;
		}

		
		//카운트를 비교함
		if($data->LIST_COUNT != count($data->CPN_LIST)){
			//echo '발급된 쿠폰수와 요청수가 일치하지 않습니다.';
			return false;
		}

		//쿠폰발급한걸 가공하여 반환함
		foreach( $data->CPN_LIST as $row ){
			$obj =& $ret[];
			$obj['COOPER_ORDER']	= $row->COOPER_ORDER;
			$obj['PAY_MONEY']		= $row->PAY_MONEY;
			$obj['NO_CPN']			= $row->NO_CPN;
			$obj['ISSUE_DATE']		= $row->ISSUE_DATE;
			$obj['CPN_STATUS']		= $row->CPN_STATUS;
			$obj['NO_REQ']			= $row->NO_REQ;
			$obj['NM_GOODS']		= $row->NM_GOODS;
			$obj['CPN_PRICE']		= $row->CPN_PRICE;
			$obj['CALL_CTN']		= $row->CALL_CTN;
			$obj['RCV_CTN']			= $row->RCV_CTN;
			$obj['USE_COMPANY']		= $row->USE_COMPANY;
			$obj['USE_STORE']		= $row->USE_STORE;
			$obj['USE_DATE']		= $row->USE_DATE;
			$obj['EXPIRE_DATE']		= $row->EXPIRE_DATE;
		}


		return $ret;
	}

	//쿠폰구매내역 조회(쿠폰번호)
	function listCouponByNo( $param=NULL ){
		if( !$param ){
			//echo '도넛북통신에 필요한 값 들이 없습니다.';
			return false;
		}

		//쿠폰발송
		$param['ACTION'] = 'CI06_QUERY_NOCPN';

		//통신
		$data = $this->curl( $param );

		//검증
		if( !isset($data->RTMSG) ){
			//echo '통신 실패';
			return false;
		}

		//검증
		if( $data->RTMSG != 'SUCCESS' ){
			//echo '통신 실패';
			return false;
		}

		
		//카운트를 비교함
		if($data->LIST_COUNT != count($data->CPN_LIST)){
			//echo '발급된 쿠폰수와 요청수가 일치하지 않습니다.';
			return false;
		}

		//쿠폰발급한걸 가공하여 반환함
		$row =& $data->CPN_LIST->CPN_INFO;
		$ret['COOPER_ORDER']	= $row->COOPER_ORDER;
		$ret['PAY_MONEY']		= $row->PAY_MONEY;
		$ret['NO_CPN']			= $row->NO_CPN;
		$ret['ISSUE_DATE']		= $row->ISSUE_DATE;
		$ret['CPN_STATUS']		= $row->CPN_STATUS;
		$ret['NO_REQ']			= $row->NO_REQ;
		$ret['NM_GOODS']		= $row->NM_GOODS;
		$ret['CPN_PRICE']		= $row->CPN_PRICE;
		$ret['CALL_CTN']		= $row->CALL_CTN;
		$ret['RCV_CTN']			= $row->RCV_CTN;
		$ret['USE_COMPANY']		= ( @is_object($row->USE_COMPANY) )?@current(@get_object_vars($row->USE_COMPANY)):$row->USE_COMPANY;
		$ret['USE_STORE']		= ( @is_object($row->USE_STORE) )?@current(@get_object_vars($row->USE_STORE)):$row->USE_STORE;
		$ret['USE_DATE']		= ( @is_object($row->USE_DATE) )?@current(@get_object_vars($row->USE_DATE)):$row->USE_DATE;


		return $ret;
	}
}

?>
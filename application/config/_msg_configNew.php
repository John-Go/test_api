<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// 야놀자 펜션 메세지 템플릿
$config['msgType'] = array(

/*-----------------------------------------------------------------
	ver 0
-----------------------------------------------------------------*/

	/*-----------------------------------------------------------------
		야펜
	-----------------------------------------------------------------*/
// 예약대기(고객)
	'YP_RW_2'	=> "[#{pensionName} -입금대기]

펜션명 : #{pension}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약자 : #{user}
인원 : 성인#{adult}명/아동#{young}명/유아#{baby}명
옵션 : #{options}

결제금액 : #{price}원
현장결제 : #{noPrice}원 (펜션 도착 후 결제)
입금계좌 : #{account}
입금기한 : #{limit}

예약확인 : #{URL}

입금기한 내 입금확인이 되지 않으면, 예약이 취소됩니다.
", 

	// 예약대기(사장)
	'YP_CRW_1'	=> "[#{pensionName} - 입금대기]

객실명 : #{room}
입실일 : #{startDate}(#{dayName}) / #{day}박 
인원 : 성인#{adult}명/아동#{young}명/유아#{baby}명 

예약자 : #{user}
휴대폰 : #{phoneNumber}
생년월일 : #{birthday}

예약접수 : #{regDate}
결제금액 : #{price}원 입금대기 중입니다.
현장결제 : #{noPrice}원 (고객 도착 후 결제)

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC", 

	// 예약완료(고객)
	'YP_RS_1'	=> "[#{pensionName} - 예약완료]

예약번호 : #{revCode}

펜션명 : #{pension}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

펜션주소 : #{address}
펜션연락처 : #{phoneNumber}

예약자 : #{user}
인원 : 성인#{adult}명/아동#{young}명/유아#{baby}명
옵션 : #{options}

결제금액 : #{price}원
현장결제 : #{noPrice}원 (펜션 도착 후 결제)

예약확인 : #{URL}

예약이 완료되었습니다. 즐거운 여행 되세요.", 

	// 예약완료(사장님)
	'YP_CRS_1'	=> "[#{pensionName} - 예약완료]

객실명 : #{room}
입실일 : #{startDate}(#{dayName}) / #{day}박 
인원 : 성인#{adult}명/아동#{young}명/유아#{baby}명 

예약자 : #{user}
휴대폰 : #{phoneNumber}
생년월일 : #{birthday}

출발지역 : #{startArea}
도착예정시간 : #{inTime}
픽업신청 : #{pickup}
요청사항 : #{memo}

옵션 : #{options}

예약접수 : #{regDate}
결제금액 : #{price}원
현장결제 : #{noPrice}원 (고객 도착 후 결제)

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC", 

	// 예약취소(고객)
	'YP_RC_1'	=> "[#{pensionName} - 예약취소]

펜션명: #{pension}
객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

예약확인 : #{url}

취소가 완료되었습니다.

* 결제수단 별 환불안내
 - 카드 : 3~5일 내 승인취소
 - 가상계좌 : 2~3일 내 환불
 - 실시간계좌이체 : 1~2일 내 환불
* 단, 객실 중복 예약으로 인한 취소 시 100% 환불됩니다.",

	// 예약취소(사장님)
	'YP_CRC_1'	=> "[#{pensionName} - 예약취소]

객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박
예약자 : #{user}

예약이 취소되었습니다.

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	// 예약취소접수(고객)
	'YP_RCW_1'	=> "[#{pensionName} - 취소접수]

펜션명: #{pension}
객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

예약확인 : #{url}

취소 접수가 완료되었습니다.

* 결제수단 별 환불안내
 - 카드 : 3~5일 내 승인취소
 - 가상계좌 : 2~3일 내 환불
 - 실시간계좌이체 : 1~2일 내 환불
* 단, 객실 중복 예약으로 인한 취소 시 100% 환불됩니다.",

	// 예약취소접수(사장)
	'YP_CRCW_1'	=> "[#{pensionName} - 취소접수]

객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

예약이 취소되었습니다.

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC",

	// 미입금취소(고객)
	'YP_RLC_1'	=> "[#{pensionName} - 미입금취소]

펜션명: #{pension}
객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

입금기한 내 입금확인이 되지 않아,
예약이 취소되었습니다.", 

	// 미입금취소(사장님)
	'YP_CRLC_1'	=> "[#{pensionName} - 미입금취소]

객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

입금이 되지 않아 예약이 취소되었습니다.

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC", 

	// 예약취소(고객)
	'YBS_RC_2'	=> "[#{pensionName} - 예약취소]

펜션명: #{pension}
객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

예약확인 : #{url}

취소가 완료되었습니다.

* 카드결제 후 예약취소 시 약 3~5일 내 승인취소됩니다.
* 객실 중복 예약으로 인한 취소 시 100% 환불됩니다.", 
	
	// 예약취소(사장님)
	'YBS_CRC_1'	=> "[#{pensionName} - 예약취소]

객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

예약이 취소되었습니다.

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC", 

	// 예약취소접수(고객)
	'YBS_RCW_3'	=> "[#{pensionName} - 취소접수]

펜션명: #{pension}
객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

예약확인 : #{url}

취소 접수가 완료되었습니다.

* 객실 중복 예약으로 인한 취소 시 100% 환불됩니다.",

	// 예약취소접수(사장)
	'YBS_CRCW_1'	=> "[#{pensionName} - 취소대기(환불요청)]

객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

취소 접수 건이 있습니다.
확인 후 고객에게 환불해 주세요.

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	// 미입금취소(고객)
	'YBS_RLC_1'	=> "[#{pensionName} - 미입금취소]

객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

입금이 되지 않아 예약이 취소되었습니다.

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC", 

	// YBS 예약대기
	'YBS_DW'	=> "[입금대기]
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약자 : #{user}

결제금액 : #{price}원
입금계좌 : #{account}",

	// 예약대기(사장님)
	'YPC_DW'	=> "[#{pensionName} - 입금대기]
#{room} #{startDate}(#{dayName})/#{day}박 #{user} #{people}명 #{price}원 입금대기중입니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	// YBS 예약완료
	'YBS_DS'	=> "[예약완료]
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

주소 : #{address}
연락처 : #{phoneNumber}

예약이 완료되었습니다. 즐거운 여행 되세요.", 

	// 예약완료(사장님)
	'YPC_DS'	=> "[#{pensionName} - 예약완료]

객실명 : #{room}
입실일 : #{startDate}(#{dayName}) / #{day}박 

예약자 : #{user}
휴대폰 : #{phoneNumber}

예약접수 : #{regDate}
결제금액 : #{price}원

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC", 

	// YBS 예약취소
	'YBS_DC'	=> "[예약취소]
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박
예약자 : #{user}

취소가 완료되었습니다", 

	// 예약취소(사장님)
	'YPC_RC'	=> "[#{pensionName} - 예약취소]
#{room} #{startDate}(#{dayName})/#{day}박
#{user} 예약취소되었습니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	
/*-----------------------------------------------------------------
	ver 1
-----------------------------------------------------------------*/
	/*-----------------------------------------------------------------
		야펜
	-----------------------------------------------------------------*/
	'YP_H_1'	=> "[야놀자펜션 - 해피콜 안내]

아래 예약 건에 대해 펜션주와 예약확인 안심 해피콜이 완료 되었습니다.

펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약확인 : #{url}

안심하시고, 즐거운 여행 되세요.",
	
	'YP_RS_M_1'	=> "[#{pensionName} - 예약완료]

예약번호 : #{revCode}

펜션명 : #{pension}
펜션주소 : #{address}
펜션연락처 : #{phoneNumber}

예약자 : #{user}
옵션 : #{options}

결제금액 : #{price}원
현장결제 : #{noPrice}원 (펜션 도착 후 결제)

예약확인 : #{URL}

예약이 완료되었습니다. 즐거운 여행 되세요.",

	'YP_RW_M_1'		=> "[#{pensionName} -입금대기]

펜션명 : #{pension}

예약자 : #{user}
옵션 : #{options}

결제금액 : #{price}원
현장결제 : #{noPrice}원 (펜션 도착 후 결제)
입금계좌 : #{account}
입금기한 : #{limit}

예약확인 : #{URL}

입금기한 내 입금확인이 되지 않으면, 예약이 취소됩니다.",

	'YP_RCW_M_1'	=> "[#{pensionName} - 취소접수]

펜션명: #{pension}

예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

예약확인 : #{url}

취소 접수가 완료되었습니다.

* 결제수단 별 환불안내
 - 카드 : 3~5일 내 승인취소
 - 가상계좌 : 2~3일 내 환불
 - 실시간계좌이체 : 1~2일 내 환불
* 단, 객실 중복 예약으로 인한 취소 시 100% 환불됩니다.",

	'YP_RC_M_1'		=> "[#{pensionName} - 예약취소]

펜션명: #{pension}

예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

예약확인 : #{url}

취소가 완료되었습니다.

* 결제수단 별 환불안내
 - 카드 : 3~5일 내 승인취소
 - 가상계좌 : 2~3일 내 환불
 - 실시간계좌이체 : 1~2일 내 환불
* 단, 객실 중복 예약으로 인한 취소 시 100% 환불됩니다.",

	'YP_RLC_M_1'	=> "[#{pensionName} - 미입금취소]

펜션명: #{pension}

예약자: #{user}

입금기한 내 입금확인이 되지 않아,
예약이 취소되었습니다.",
	
	
// 예약취소(사장님)
	'YP_CRC_2'	=> "[#{pensionName} - 예약취소]

예약번호 : #{rCode}
예약자 : #{user}

예약이 취소되었습니다.

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

// 예약취소접수(사장)
	'YP_CRCW_2'	=> "[#{pensionName} - 취소접수]

예약번호 : #{rCode}
예약자: #{user}

예약이 취소되었습니다.

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC",
	
	/*-----------------------------------------------------------------
		YBS
	-----------------------------------------------------------------*/
	'YBS_RC_M_1'	=> "[#{pensionName} - 예약취소]

펜션명: #{pension}

예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

예약확인 : #{url}

취소가 완료되었습니다.

* 카드결제 후 예약취소 시 약 3~5일 내 승인취소됩니다.
* 객실 중복 예약으로 인한 취소 시 100% 환불됩니다.",

	'YBS_RCW_M_3'	=> "[#{pensionName} - 취소접수]

펜션명: #{pension}

예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

예약확인 : #{url}

취소 접수가 완료되었습니다.

* 객실 중복 예약으로 인한 취소 시 100% 환불됩니다."
	
	
);


/*-----------------------------------------------------------------
	상태에 따른 설정
-----------------------------------------------------------------*/

/*-----------------------------------------------------------------
	ver 0
-----------------------------------------------------------------*/
// 야펜
$config['YP']['0']['PW']['user']	= 'YP_RW_2';
$config['YP']['0']['PW']['ceo']		= 'YP_CRW_1';

$config['YP']['0']['PS']['user']	= 'YP_RS_1';
$config['YP']['0']['PS']['ceo']		= 'YP_CRS_1';

$config['YP']['0']['CS']['user']	= 'YP_RC_1';
$config['YP']['0']['CS']['ceo']		= 'YP_CRC_1';

$config['YP']['0']['CW']['user']	= 'YP_RCW_1';
$config['YP']['0']['CW']['ceo']		= 'YP_CRCW_1';;

$config['YP']['0']['AC']['user']	= 'YP_RLC_1';
$config['YP']['0']['AC']['ceo']		= 'YP_CRLC_1';

// YBS
$config['YBS']['0']['PW']['user']	= 'YP_RW_2';
$config['YBS']['0']['PW']['ceo']	= 'YP_CRW_1';

$config['YBS']['0']['PS']['user']	= 'YP_RS_1';
$config['YBS']['0']['PS']['ceo']	= 'YP_CRS_1';

$config['YBS']['0']['CS']['user']	= 'YBS_RC_2';
$config['YBS']['0']['CS']['ceo']	= 'YBS_CRC_1';

$config['YBS']['0']['CW']['user']	= 'YBS_RCW_3';
$config['YBS']['0']['CW']['ceo']	= 'YBS_CRCW_1';

$config['YBS']['0']['AC']['user']	= 'YP_RLC_1';
$config['YBS']['0']['AC']['ceo']	= 'YBS_RLC_1';

// CEO
$config['CEO']['0']['PW']['user']	= 'YBS_DW';
$config['CEO']['0']['PW']['ceo']	= 'YPC_DW';

$config['CEO']['0']['PS']['user']	= 'YBS_DS';
$config['CEO']['0']['PS']['ceo']	= 'YPC_DS';

$config['CEO']['0']['CS']['user']	= 'YBS_DC';
$config['CEO']['0']['CS']['ceo']	= 'YPC_RC';

$config['CEO']['0']['AC']['user']	= 'YBS_DC';
$config['CEO']['0']['AC']['ceo']	= 'YPC_RCW';


/*-----------------------------------------------------------------
	ver 1
-----------------------------------------------------------------*/

// 야펜
$config['YP']['1']['PW']['user']	= 'YP_RW_M_1';			// o
$config['YP']['1']['PW']['ceo']		= 'YP_CRW_1';			// o

$config['YP']['1']['PS']['user']	= 'YP_RS_M_1';
$config['YP']['1']['PS']['ceo']		= 'YP_CRS_1';

$config['YP']['1']['CS']['user']	= 'YP_RC_M_1';
$config['YP']['1']['CS']['ceo']		= 'YP_CRC_2';

$config['YP']['1']['CW']['user']	= 'YP_RCW_M_1';
$config['YP']['1']['CW']['ceo']		= 'YP_CRCW_2';

$config['YP']['1']['AC']['user']	= 'YP_RLC_M_1';
$config['YP']['1']['AC']['ceo']		= 'YP_CRLC_1';

// YBS
$config['YBS']['1']['PW']['user']	= 'YP_RW_M_1';
$config['YBS']['1']['PW']['ceo']	= 'YP_CRW_1';

$config['YBS']['1']['PS']['user']	= 'YP_RS_M_1';
$config['YBS']['1']['PS']['ceo']	= 'YP_CRS_1';

$config['YBS']['1']['CS']['user']	= 'YBS_RC_M_1';
$config['YBS']['1']['CS']['ceo']	= 'YBS_CRC_1';

$config['YBS']['1']['CW']['user']	= 'YBS_RCW_M_3';
$config['YBS']['1']['CW']['ceo']	= 'YBS_CRCW_1';

$config['YBS']['1']['AC']['user']	= 'YP_RLC_M_1';
$config['YBS']['1']['AC']['ceo']	= 'YBS_RLC_1';

// CEO
$config['CEO']['1']['PW']['user']	= 'YBS_DW';
$config['CEO']['1']['PW']['ceo']	= 'YPC_DW';

$config['CEO']['1']['PS']['user']	= 'YBS_DS';
$config['CEO']['1']['PS']['ceo']	= 'YPC_DS';

$config['CEO']['1']['CS']['user']	= 'YBS_DC';
$config['CEO']['1']['CS']['ceo']	= 'YPC_RC';

$config['CEO']['1']['AC']['user']	= 'YBS_DC';
$config['CEO']['1']['AC']['ceo']	= 'YPC_RCW';
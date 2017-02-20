<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// 야놀자 펜션 메세지 템플릿
$config['msgType']['YP'] = array(
	// 해피콜(고객)
	'YP_H_1'	=> "[야놀자펜션 - 해피콜 안내]

아래 예약 건에 대해 펜션주와 예약확인 안심 해피콜이 완료 되었습니다.

펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약확인 : #{url}

안심하시고, 즐거운 여행 되세요.",

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

	// 예약대기(고객)
	'YP_RW_1'	=> "[#{pensionName} -입금대기]

펜션명 : #{pension}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약자 : #{user}
인원 : 성인#{adult}명/아동#{young}명/유아#{baby}명
옵션 : #{options}

결제금액 : #{price}원
결제금액 : #{noPrice}원 (펜션 도착 후 결제)
입금계좌 : #{account}
입금기한 : #{limit}

예약확인 : #{URL}

입금기한 내 입금확인이 되지 않으면, 예약이 취소됩니다.", 

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
	'YP_CRW_1'	=> "[#{pensionName} -입금대기]

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

	// 입금독려(고객)
	'YP_RAW_1'	=> "[야놀자펜션 - 입금안내]

입금기한이 얼마남지 않았습니다. 
입금기한 내 입금확인이 되지 않으면, 예약이 취소됩니다.",

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

	// 회원가입 축하 메시지
	'YP_MSC_1'	=> "[야놀자펜션]

회원가입을 축하드립니다!

야놀자펜션과 함께 여행을 떠날 준비가 끝났습니다.
힐링이 필요한 당신, 지금 여행을 떠나보세요.

그럼, 즐거운 여행 되세요!
(야놀자펜션의 카카오톡 친구가 되어 주시면, 실시간으로 1:1 문의가 가능합니다)", 

	// 고객문자10(고객)
	'YP_SMS10_1'	=> "[야놀자펜션]

펜션 예약 관련하여 연락 드렸습니다.
확인되시면 연락 부탁드립니다.

1644-4816", 

	// 고객문자11(고객)
	'YP_SMS11_1'	=> "[야놀자펜션]

예약하신 객실이 중복 예약되어 입실이 불가능하오니,
연락 부탁드립니다.

1644-4816",

	// 할인 알림문자
	'YP_SALE'	=> "[할인종료 알림]
#{pension} 할인 종료 1주일 전입니다.
연락처 : #{phone}", 

	// 오류 문자
	'YP_ERROR1'	=> "[오류 알림]
#{pension}
예약 시 금액 오류 발생", 
	
	// 예약완료(고객)
	'YP_RS'		=> "[야놀자펜션-펜션예약 완료]
펜션명: #{pension}
객실명: #{room}
이용일: #{startDate}(#{dayName}) #{day}박
예약자명: #{user}
인원: 총 #{people}명
결제금액: #{price}원

펜션주소: #{address}
펜션연락처: #{pensionTel}

예약번호: #{revCode}
(예약내역 조회 시 필요한 번호입니다)

반드시 출발 전 펜션과 통화하여 예약확정여부 확인부탁드립니다.", 
	
	// 예약취소(고객)
	'YP_RC'		=> "[야놀자펜션-예약취소]
펜션명: #{pension}
객실명: #{room}
이용일: #{startDate}(#{dayName}) #{day}박
예약자명: #{user}

예약이 취소되었습니다.
고객센터 : 1644-4816", 

	// 예약대기(고객)
	'YP_RW'		=> "[야놀자펜션-입금대기]
펜션명: #{pension}
객실명: #{room}
이용일: #{startDate}(#{dayName}) #{day}박
예약자명: #{user}
인원: 총 #{people}명
결제금액: #{price}원
입금기한: #{limitDate}",

	// 예약취소접수(고객)
	'YP_RCW'	=> "[야놀자펜션-예약취소]
펜션명: #{pension}
객실명: #{room}
이용일: #{startDate}(#{dayName}) #{day}박
예약자명: #{user}

취소 접수가 완료되었습니다
고객센터 : 1644-4816",

	// 미입금취소안내(고객)
	'YP_RAW'	=> "[야놀자펜션]
입금기한이 얼마남지 않았습니다. 기한 내 미 입금 시 예약이 취소됩니다.",

	// 해피콜(고객)
	'YP_H'		=> "[야놀자펜션] 고객님께서 예약하신 #{pension} #{room} #{startDate}(#{dayName}) / #{people}명 에 대한 예약 건에 대해 펜션주와 예약확인 해피콜이 완료 되었습니다.
안심하시고, 즐거운 여행 되세요!",

	// 고객문자1(고객)
	'YP_SMS1'	=> "[야놀자펜션] 카드사에서 약 3~5일 내 승인취소 처리됩니다", 

	// 고객문자2(고객)
	'YP_SMS2'	=> "[야놀자펜션] 무통장입금 환불은 취소접수일+2영업일에 결제대행사를 통해 환불됩니다",

	// 고객문자3(고객)
	'YP_SMS3'	=> "[야놀자펜션] 계좌이체는 당일 환불되나 부분취소 등 일부의 경우 1~2일 이내 환불됩니다",

	// 고객문자4(고객)
	'YP_SMS4'	=> "[야놀자펜션] 객실 이용 당일 취소로 환불이 불가합니다",

	// 고객문자5(고객)
	'YP_SMS5'	=> "[야놀자펜션] 객실 이용 1일 전 취소로 판매금액의 70% 공제 후 환불됩니다",

	// 고객문자6(고객)
	'YP_SMS6'	=> "[야놀자펜션] 객실 이용 2일 전 취소로 판매금액의 50% 공제 후 환불됩니다",

	// 고객문자7(고객)
	'YP_SMS7'	=> "[야놀자펜션] 객실 이용 3일 전 취소로 판매금액의 30% 공제 후 환불됩니다",

	// 고객문자8(고객)
	'YP_SMS8'	=> "[야놀자펜션] 객실 이용 4일 전 취소로 판매금액의 20% 공제 후 환불됩니다", 

	// 고객문자9(고객)
	'YP_SMS9'	=> "[야놀자펜션] 예약 8일후부터 객실 이용 5일 전 취소로 판매금액의 10% 공제 후 환불됩니다",

	// 고객문자10(고객)
	'YP_SMS10'	=> "[야놀자펜션] 펜션 예약 관련하여 연락 드렸습니다. 확인되시면 연락 부탁드립니다.",

	// 고객문자11(고객)
	'YP_SMS11'	=> "[야놀자펜션] 예약하신 객실이 중복예약되어 입실이 불가능하오니, 연락 부탁드립니다.",

	// 안내문자 1(사장님)
	'YP_CALL1'	=> "[야놀자펜션-안내]
사장님 페이지 주소입니다.(인터넷 창에 입력)
http://ceo.yapen.co.kr", 

	// 안내문자 2(사장님)
	'YP_CALL2'	=> "[야놀자펜션-안내]
요금이나 기간 등 펜션 정보 수정이 필요하시면 언제든지 전화 주세요.",

	// 안내문자 3(사장님)
	'YP_CALL3'	=> "[야놀자펜션-안내]
성수기시즌 중복예약 방지를 위해 바쁘시더라도 달력관리 부탁드립니다.", 

	// 안내문자 4(사장님)
	'YP_CALL4'	=> "[야놀자펜션-안내]
광고비 입금계좌번호 안내드립니다.
국민은행 445701-01-249118 (주)야놀자트래블", 

	// 안내문자 5(사장님)
	'YP_CALL5'	=> "[야놀자펜션-안내]
계좌번호 안내드립니다.
국민은행 445701-01-243880 (주)야놀자트래블", 

	// 안내문자 6(사장님)
	'YP_CALL6'	=> "[야놀자펜션-안내]
야놀자펜션 메일주소 : ps@yapen.kr
팩스번호 : 02-511-4815
http://goo.gl/scHffC", 

	// 안내문자 7(고객)
	'YP_CALL7'	=> "[야놀자펜션-안내]
카드사에서 약 3~5일 내 승인취소 처리됩니다", 

	// 안내문자 8(고객)
	'YP_CALL8'	=> "[야놀자펜션-안내]
무통장입금 환불은 약 3~5일 내 결제 대행사를 통해 환불계좌로 입금됩니다.", 

	// 안내문자 9(고객)
	'YP_CALL9'	=> "[야놀자펜션-안내]
계좌이체는 당일 환불되나 부분취소 등 일부의 경우 1~2일 이내 환불됩니다.", 

	// 안내문자10(고객)
	'YP_CALL10'	=> "[야놀자펜션-안내]
#{pension} 인원추가 #{people}명
#{price}원 현장결제 부탁드립니다.", 

	// 안내문자11(고객)
	'YP_CALL11'	=> "[야놀자펜션]
중복예약 발생 쿠폰지급
#{code}
이용에 불편을드려 죄송합니다.
감사합니다."
);

// CEO 페이지 메세지 템플릿
$config['msgType']['YPC']	= array(
	
	// 예약대기(사장님)
	'YPC_DW'	=> "[#{pensionName} - 입금대기]
#{room} #{startDate}(#{dayName})/#{day}박 #{people}명 #{price}원 입금대기중입니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

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

	// 예약완료(사장님)
	'YPC_RSS'	=> "[야놀자펜션-예약완료] 

객실명 : #{room}
입실일 : #{startDate}(#{dayName}) / #{day}박 
인원 : 성인#{adult}명/소아#{young}명/유아#{baby}명 

예약자명 : #{user}
휴대폰 : #{phoneNumber}
생년월일 : #{birthday}
입실예정 : #{inTime}
픽업신청 : #{pickup}
요청사항 : #{memo}

예약접수: #{regDate}
판매금액 : #{price}원

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC ",

	// 예약취소(사장님)
	'YPC_RC'	=> "[#{pensionName} - 예약취소]
#{room} #{startDate}(#{dayName})/#{day}박
#{user} 예약취소되었습니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	// 예약대기(사장님)
	'YPC_RW'	=> "[야놀자펜션-입금대기]
#{room} #{startDate}(#{dayName})/#{day}박 #{people}명 #{price}원 입금대기중입니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	// 예약취소대기(사장님)
	'YPC_RCW'	=> "[#{pensionName} - 예약취소]
#{room} #{startDate}(#{dayName})/#{day}박
#{user} 예약취소되었습니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC", 

	// 예약완료(사장님)
	'YPC_RS1'	=> "[야놀자펜션-예약완료] 

객실명 : #{room}
입실일 : #{startDate}(#{dayName}) / #{day}박 
인원 : #{people}명

예약자명 : #{user}
휴대폰 : #{phoneNumber}
픽업신청 : #{pickup}
요청사항 : #{memo}

예약접수: #{regDate}
판매금액 : #{price}원

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC"
);

// YBS 페이지 메세지 템플릿
$config['msgType']['YBS']	= array(
	
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

	// 예약취소접수(고객)
	'YBS_RCW_1'	=> "[#{pensionName} - 취소접수]

펜션명: #{pension}
객실명: #{roomName}
이용일: #{startDate}(#{dayName}) / #{day}박
예약자: #{user}

결제금액 : #{price}원
위약금 : #{penalty}

예약확인 : #{url}

취소 접수가 완료되었습니다.

* 객실 중복 예약으로 인한 취소 시 100% 환불됩니다.
",

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

	// YBS 예약대기
	'YBS_W'		=> "[입금대기]
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약자 : #{user}
인원 : #{people}명
옵션 : #{options}

결제금액 : #{price}원
입금계좌 : #{account}
입금기한 : #{limit}

예약확인 : #{URL}",

	// YBS 예약완료
	'YBS_S'		=> "[예약완료]
예약번호 : #{revCode}
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

주소 : #{address}
연락처 : #{phoneNumber}

예약자 : #{user}
인원 : #{people}명
옵션 : #{options}

결제금액 : #{price}원
현장결제 : #{noPrice}원 (펜션 도착 후 결제)

예약확인 : #{URL}
예약이 완료되었습니다. 즐거운 여행 되세요.",

	// YBS 예약취소
	'YBS_C'		=> "[예약취소]
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박
예약자 : #{user}

예약확인 : #{URL}
취소가 완료되었습니다", 

	// YBS 예약대기 (사장님)
	'YBS_CW'	=> "[입금대기]
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약자 : #{user}
연락처 : #{phoneNumber}
인원 : #{people}명
옵션 : #{options}

예약접수 : #{regDate}
결제금액 : #{price}원 입금대기 중입니다.

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	// YBS 예약완료 (사장님)
	'YBS_CS'	=> "[예약완료]
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약자 : #{user}
연락처 : #{phoneNumber}
인원 : #{people}명
옵션 : #{options}

픽업여부 : #{pickup}
도착예정시간 : #{inTime}
요청사항 : #{request}

예약접수 : #{regDate}
결제금액 : #{price}원
현장결제 : #{noPrice}원 (고객 도착 후 결제)

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	// YBS 예약취소 (사장님)
	'YBS_CC'	=> "[예약취소]
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박
예약자 : #{user}

예약이 취소되었습니다.

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	// YBS 미입금취소 (사장님)
	'YBS_CWC'	=> "[미입금취소]
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박
예약자 : #{user}

예약이 취소되었습니다.

[사장님 페이지 바로가기]
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

	// YBS 예약취소
	'YBS_DC'	=> "[예약취소]
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박
예약자 : #{user}

취소가 완료되었습니다", 

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
입금계좌 : #{account}"
);


// 상태에 따른 설정
// 야펜
$config['YP']['PS01']['user']	= 'YP_RW_2';
$config['YP']['PS01']['ceo']	= 'YP_CRW_1';

$config['YP']['PS02']['user']	= 'YP_RS_1';
$config['YP']['PS02']['ceo']	= 'YP_CRS_1';

$config['YP']['PS03']['user']	= $config['YP']['PS04']['user'] = $config['YP']['PS05']['user'] = $config['YP']['PS07']['user']									= 'YP_RC_1';
$config['YP']['PS03']['ceo']	= $config['YP']['PS04']['ceo'] = $config['YP']['PS05']['ceo'] = $config['YP']['PS06']['ceo'] = $config['YP']['PS07']['ceo'] = 'YP_CRC_1';

$config['YP']['PS06']['user']	= 'YP_RCW_1';
$config['YP']['PS06']['ceo']	= 'YP_CRCW_1';

$config['YP']['PS08']['user']	= 'YP_RLC_1';
$config['YP']['PS08']['ceo']	= 'YP_CRLC_1';

// YBS
$config['YBS']['PS01']['user']	= 'YP_RW_2';
$config['YBS']['PS01']['ceo']	= 'YP_CRW_1';

$config['YBS']['PS02']['user']	= 'YP_RS_1';
$config['YBS']['PS02']['ceo']	= 'YP_CRS_1';

$config['YBS']['PS04']['user']	= $config['YBS']['PS05']['user'] = 'YBS_RC_2';
$config['YBS']['PS04']['ceo']	= $config['YBS']['PS05']['ceo'] = 'YBS_CRC_1';

$config['YBS']['PS06']['user']	= 'YBS_RCW_3';
$config['YBS']['PS06']['ceo']	= 'YBS_CRCW_1';

$config['YBS']['PS08']['user']	= 'YP_RLC_1';
$config['YBS']['PS08']['ceo']	= 'YBS_RLC_1';

// CEO
$config['CEO']['PS01']['user']	= 'YBS_DW';
$config['CEO']['PS01']['ceo']	= 'YPC_DW';

$config['CEO']['PS02']['user']	= 'YBS_DS';
$config['CEO']['PS02']['ceo']	= 'YPC_DS';

$config['CEO']['PS04']['user']	= 'YBS_DC';
$config['CEO']['PS04']['ceo']	= 'YPC_RC';

$config['CEO']['PS08']['user']	= 'YBS_DC';
$config['CEO']['PS08']['ceo']	= 'YPC_RCW';
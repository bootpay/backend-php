
## Bootpay PHP Server Side Library
부트페이 공식 Ruby 라이브러리 입니다 (서버사이드 용)

PHP 언어로 작성된 어플리케이션, 프레임워크 등에서 사용가능합니다.

* PG 결제창 연동은 클라이언트 라이브러리에서 수행됩니다. (Javascript, Android, iOS, React Native, Flutter 등)
* 결제 검증 및 취소, 빌링키 발급, 본인인증 등의 수행은 서버사이드에서 진행됩니다. (Java, PHP, Python, Ruby, Node.js, Go, ASP.NET 등)


## 기능   
1. (부트페이 통신을 위한) 토큰 발급 요청   
2. 결제 검증   
3. 결제 취소 (전액 취소 / 부분 취소)
4. 빌링키 발급
   
    4-1. 발급된 빌링키로 결제 승인 요청
   
    4-2. 발급된 빌링키로 결제 승인 예약 요청
   
    4-2-1. 발급된 빌링키로 결제 승인 예약 - 취소 요청
   
    4-3. 빌링키 삭제
5. (부트페이 단독) 사용자 토큰 발급   
6. (부트페이 단독) 결제 링크 생성   
7. 서버 승인 요청   
8. 본인 인증 결과 조회

## composer로 설치하기 


```php
composer require bootpay/backend-php
```


## 사용하기 

```php

require 'bootpay'

@api = Bootpay::Api.new(
  application_id: '5b8f6a4d396fa665fdc2b5ea',
  private_key:    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw=',
)
response = @api.request_access_token
if response.success?
  puts  response.data.to_json
end
```
함수 단위의 샘플 코드는 [이곳](https://github.com/bootpay/backend-ruby/tree/main/spec/bootpay)을 참조하세요.


## 1. 토큰 발급 

부트페이와 서버간 통신을 하기 위해서는 부트페이 서버로부터 토큰을 발급받아야 합니다.  
발급된 토큰은 30분간 유효하며, 최초 발급일로부터 30분이 지날 경우 토큰 발급 함수를 재호출 해주셔야 합니다.
```ruby 
def get_token
  response = @api.request_access_token
  if response.success?
    puts  response.data.to_json
  end
end
```


## 2. 결제 검증 
결제창 및 정기결제에서 승인/취소된 결제건에 대하여 올바른 결제건인지 서버간 통신으로 결제검증을 합니다.
```ruby  
def verification
  receipt_id = '612df0250d681b001de61de6'
  if @api.request_access_token.success?
    response = @api.verify(receipt_id)
    puts  response.data.to_json
  end
end
```


## 3. 결제 취소 (전액 취소 / 부분 취소)
price를 지정하지 않으면 전액취소 됩니다. 
* 휴대폰 결제의 경우 이월될 경우 이통사 정책상 취소되지 않습니다
* 정산받으실 금액보다 취소금액이 클 경우 PG사 정책상 취소되지 않을 수 있습니다. 이때 PG사에 문의하시면 되겠습니다.
* 가상계좌의 경우 CMS 특약이 되어있지 않으면 취소되지 않습니다. 그러므로 결제 테스트시에는 가상계좌로 테스트 하지 않길 추천합니다. 

부분취는 카드로 결제된 건만 가능하며, 일부 PG사만 지원합니다. 요청시 price에 금액을 지정하시면 되겠습니다. 
* (지원가능 PG사: 이니시스, kcp, 다날, 페이레터, 나이스페이, 카카오페이, 페이코)

간혹 개발사에서 실수로 여러번 부분취소를 보내서 여러번 취소되는 경우가 있기때문에, 부트페이에서는 부분취소 중복 요청을 막기 위해 cancel_id 라는 필드를 추가했습니다. cancel_id를 지정하시면, 해당 건에 대해 중복 요청방지가 가능합니다.  
```ruby 
def cancel
  if @api.request_access_token.success?
    response = @api.cancel_payment(
      receipt_id:      "612df0250d681b001de61de6",
      cancel_username:        'test',
      cancel_message:         'test',
      cancel_price:     1000, # 부분취소 요청시
      refund: { # 가상계좌 환불 요청시, 단 CMS 특약이 되어있어야만 환불요청이 가능하다.
        account: '675601010124', # 환불계좌
        accountholder: '홍길동', # 환불계좌주
        bankcode: Bootpay::BANKCODE["국민은행"] # 은행코드
      }
    )
    puts  response.data.to_json
  end
end
```

## 4. 빌링키 발급 
REST API 방식으로 고객으로부터 카드 정보를 전달하여, PG사에게 빌링키를 발급받을 수 있습니다. 
발급받은 빌링키를 저장하고 있다가, 원하는 시점, 원하는 금액에 결제 승인 요청하여 좀 더 자유로운 결제시나리오에 적용이 가능합니다.
* 비인증 정기결제(REST API) 방식을 지원하는 PG사만 사용 가능합니다. 
```ruby 
def get_billing_key
  if @api.request_access_token.success?
    response = @api.get_billing_key(
      order_id:         '1234',
      pg:               'nicepay',
      item_name:        '테스트 결제',
      card_no:          '', # 값 할당 필요
      card_pw:          '', # 값 할당 필요
      expire_year:      '', # 값 할당 필요
      expire_month:     '', # 값 할당 필요
      identify_number:  '' # 값 할당 필요
    )
    puts response.data.to_json
  end
end
```

## 4-1. 발급된 빌링키로 결제 승인 요청  
발급된 빌링키로 원하는 시점에 원하는 금액으로 결제 승인 요청을 할 수 있습니다. 잔액이 부족하거나 도난 카드 등의 특별한 건이 아니면 PG사에서 결제를 바로 승인합니다.

```ruby  
def subscribe_billing
  billing_key = '612deb53019943001fb52312'
  if @api.request_access_token.success?
    response = @api.subscribe_billing(
      billing_key:    billing_key,
      item_name:      '테스트 결제',
      price:          1000,
      tax_free:       1000,
      order_id:       '1234'
    )
    puts response.data.to_json
  end
end
```
## 4-2. 발급된 빌링키로 결제 예약 요청
원하는 시점에 4-1로 결제 승인 요청을 보내도 되지만, 빌링키 발급 이후에 바로 결제 예약 할 수 있습니다. (빌링키당 최대 5건)
```ruby  
def subscribe_reserve_billing
  billing_key = '612deb53019943001fb52312'
  if @api.request_access_token.success?
    response = @api.subscribe_reserve_billing(
      billing_key:    billing_key,
      item_name:      '테스트 결제',
      price:          1000,
      tax_free:       1000,
      order_id:       '1234',
      execute_at: (Time.now + 10.seconds).to_i # 10초 뒤 결제
    )
    puts response.data.to_json
  end
end
```
## 4-2-1. 발급된 빌링키로 결제 예약 - 취소 요청 
빌링키로 예약된 결제건을 취소합니다. 
```ruby  
def subscribe_reserve_cancel
  reserve_id = '612deb53019943001fb52312'
  if @api.request_access_token.success?
    response = @api.subscribe_reserve_cancel(reserve_id)
    puts response.data.to_json
  end
end
```
## 4-3. 빌링키 삭제 
발급된 빌링키로 더 이상 사용되지 않도록, 삭제 요청합니다.
```java 
Bootpay bootpay = new Bootpay("5b8f6a4d396fa665fdc2b5ea", "rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw=");

try {
    ResDefault res = bootpay.destroyBillingKey("6100e7ea0d681b001fd4de69");
    System.out.println(res.toJson());
} catch (Exception e) {
    e.printStackTrace();
}
```
## 5. 사용자 토큰 발급 
(부트페이 단독) 부트페이에서 제공하는 간편결제창, 생체인증 기반의 결제 사용을 위해서는 개발사에서 회원 고유번호를 관리해야하며, 해당 회원에 대한 사용자 토큰을 발급합니다.
이 토큰값을 기반으로 클라이언트에서 결제요청 하시면 되겠습니다.
```ruby  
def destroy_billing_key
  if @api.request_access_token.success?
    billing_key = '612debc70d681b0039e6133d'
    response = @api.destroy_billing_key(billing_key)
    puts response.data.to_json
  end
end
```
## 6. 결제 링크 생성 
(부트페이 단독) 요청 하시면 결제링크가 리턴되며, 해당 url을 고객에게 안내, 결제 유도하여 결제를 진행할 수 있습니다. 
```ruby  
def request_link
  if @api.request_access_token.success?
    response = @api.request_link(
      pg:             'nicepay',
      price:          1000,
      tax_free:       1000,
      order_id:       '1234',
      name:           '결제테스트'
    )
    puts response.data.to_json
  end
end
```

## 7. 서버 승인 요청 
결제승인 방식은 클라이언트 승인 방식과, 서버 승인 방식으로 총 2가지가 있습니다.

클라이언트 승인 방식은 javascript나 native 등에서 confirm 함수에서 진행하는 일반적인 방법입니다만, 경우에 따라 서버 승인 방식이 필요할 수 있습니다.

필요한 이유 
1. 100% 안정적인 결제 후 고객 안내를 위해 - 클라이언트에서 PG결제 진행 후 승인 완료될 때 onDone이 수행되지 않아 (인터넷 환경 등), 결제 이후 고객에게 안내하지 못할 수 있습니다  
2. 단일 트랜잭션의 개념이 필요할 경우 - 재고파악이 중요한 커머스를 운영할 경우 트랜잭션 개념이 필요할 수 있겠으며, 이를 위해서는 서버 승인을 사용해야 합니다. 

```ruby  
def submit
  receipt_id = '612e09260d681b0021e61ab9'
  if @api.request_access_token.success?
    response = @api.server_submit(receipt_id)
    puts response.data.to_json
  end
end
```

## 8. 본인 인증 결과 조회 
다날 본인인증 후 결과값을 조회합니다. 
다날 본인인증에서 통신사, 외국인여부, 전화번호 이 3가지 정보는 다날에 추가로 요청하셔야 받으실 수 있습니다.
```ruby 
def certificate
  receipt_id = '612e09260d681b0021e61ab9'
  if @api.request_access_token.success?
    response = @api.certificate(receipt_id)
    puts response.data.to_json
  end
end
```

## Example 프로젝트

[적용한 샘플 프로젝트](https://github.com/bootpay/backend-ruby-example)을 참조해주세요

## Documentation

[부트페이 개발매뉴얼](https://bootpay.gitbook.io/docs/)을 참조해주세요

## 기술문의

[부트페이 홈페이지](https://www.bootpay.co.kr) 우측 하단 채팅을 통해 기술문의 주세요!

## License

[MIT License](https://opensource.org/licenses/MIT).

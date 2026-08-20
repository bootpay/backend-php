<?php
/**
 * wire-format 검증용 로컬 echo 서버 라우터 (php -S 로 실행)
 * 받은 요청의 method/uri/헤더/바디를 JSON 으로 되돌려준다 — 외부 API 호출 없이
 * SDK 공통 계층(curl)이 실제로 전송하는 wire 를 검증하는 데 쓴다.
 */
$files = array();
foreach ($_FILES as $field => $info) {
    $files[$field] = $info['name'];
}

$response = array(
    'echo' => true,
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'headers' => function_exists('getallheaders') ? getallheaders() : array(),
    'raw_body' => file_get_contents('php://input'),
    'post' => $_POST,
    'files' => $files
);

// 일부 endpoint 는 실제 응답의 핵심 필드를 흉내낸다 —
// 기존 라이브 테스트(토큰 발급, 필드 존재 검증)가 echo 환경에서도 돌 수 있게.
if (strpos($_SERVER['REQUEST_URI'], 'request/token') !== false) {
    $response['access_token'] = 'echo-test-token';
    $response['expire_in'] = 3600;
    $response['expired_at'] = date('c', time() + 3600);
} elseif (strpos($_SERVER['REQUEST_URI'], 'request/user/token') !== false) {
    $response['user_token'] = 'echo-user-token';
} elseif (preg_match('#/v2/receipt/([^/?]+)#', $_SERVER['REQUEST_URI'], $matches)) {
    $response['receipt_id'] = $matches[1];
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);

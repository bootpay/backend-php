<?php
/**
 * 기존 라이브 테스트 스위트를 로컬 echo 서버로 실행하기 위한 phpunit 부트스트랩.
 *
 *   vendor/bin/phpunit --bootstrap tests/echo-suite-bootstrap.php tests/commerce/StoreTest.php ...
 *
 * PG/Commerce 의 API_URL 을 **모든 mode(production/stage/development)에 대해** echo 서버로
 * 돌려서, BOOTPAY_ENV 값과 무관하게 외부(Bootpay) 서버로는 단 한 요청도 나가지 않는다.
 * 회귀 스모크 용도 — SDK 호출 경로가 예외/fatal 없이 요청을 만들어 보내는지를 검증한다.
 */

require __DIR__ . '/../vendor/autoload.php';

// TestConfig 의 라이브 환경 가드(requireLiveEnvironment) 우회 —
// 아래에서 모든 mode 의 API_URL 을 echo 서버로 돌리므로 외부 요청은 발생하지 않는다.
putenv('BOOTPAY_TEST_ECHO=1');

call_user_func(function () {
    // 포트 확보
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
    if ($socket === false) {
        fwrite(STDERR, "echo 서버 포트 확보 실패: {$errstr}\n");
        exit(1);
    }
    $name = stream_socket_get_name($socket, false);
    $port = (int)substr($name, strrpos($name, ':') + 1);
    fclose($socket);

    // echo 서버 기동
    $process = proc_open(
        array(PHP_BINARY, '-S', '127.0.0.1:' . $port, __DIR__ . '/wire-echo-server.php'),
        array(1 => array('file', '/dev/null', 'w'), 2 => array('file', '/dev/null', 'w')),
        $pipes
    );
    if (!is_resource($process)) {
        fwrite(STDERR, "echo 서버 실행 실패\n");
        exit(1);
    }
    register_shutdown_function(function () use ($process) {
        if (is_resource($process)) {
            proc_terminate($process);
            proc_close($process);
        }
    });

    $ready = false;
    for ($i = 0; $i < 50; $i++) {
        $conn = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
        if ($conn !== false) {
            fclose($conn);
            $ready = true;
            break;
        }
        usleep(100000);
    }
    if (!$ready) {
        fwrite(STDERR, "echo 서버가 기동되지 않았습니다\n");
        exit(1);
    }

    // 모든 mode 의 API_URL 을 echo 서버로 교체 — production 유출 원천 차단
    $redirect = function ($class, $pathPrefix) use ($port) {
        $prop = new ReflectionProperty($class, 'API_URL');
        if (PHP_VERSION_ID < 80100) {
            $prop->setAccessible(true);
        }
        $urls = $prop->getValue();
        foreach ($urls as $mode => $unused) {
            $urls[$mode] = "http://127.0.0.1:{$port}{$pathPrefix}";
        }
        $prop->setValue(null, $urls);
    };
    $redirect('Bootpay\ServerPhp\BootpayApi', '/v2');
    $redirect('Bootpay\ServerPhp\BootpayCommerceApi', '/v1');

    echo "[echo-suite] 모든 PG/Commerce 요청을 http://127.0.0.1:{$port} 로 리다이렉트 (외부 호출 없음)\n";
});

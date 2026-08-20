<?php

namespace Bootpay\ServerPhp\Test;

use PHPUnit\Framework\TestCase;

/**
 * 로컬 echo 서버(tests/wire-echo-server.php)를 띄우고 SDK 의 API URL 을
 * 그리로 돌려서, mock 이 아닌 실제 curl 전송 wire(method/uri/헤더/바디)를 검증하는 베이스.
 * 외부(Bootpay) 서버는 호출하지 않는다.
 */
abstract class WireEchoTestCase extends TestCase
{
    /** @var resource|null */
    protected static $serverProcess = null;
    protected static $serverPort = 0;

    public static function setUpBeforeClass(): void
    {
        self::$serverPort = self::findFreePort();
        $descriptors = array(
            1 => array('file', '/dev/null', 'w'),
            2 => array('file', '/dev/null', 'w')
        );
        self::$serverProcess = proc_open(
            array(PHP_BINARY, '-S', '127.0.0.1:' . self::$serverPort, __DIR__ . '/wire-echo-server.php'),
            $descriptors,
            $pipes
        );
        if (!is_resource(self::$serverProcess)) {
            self::fail('echo 서버 실행 실패');
        }
        self::waitForServer();
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$serverProcess)) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
            self::$serverProcess = null;
        }
    }

    protected static function echoServerUrl($path)
    {
        return 'http://127.0.0.1:' . self::$serverPort . $path;
    }

    private static function findFreePort()
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($socket === false) {
            self::fail("포트 확보 실패: {$errstr}");
        }
        $name = stream_socket_get_name($socket, false);
        fclose($socket);
        return (int)substr($name, strrpos($name, ':') + 1);
    }

    private static function waitForServer()
    {
        for ($i = 0; $i < 50; $i++) {
            $conn = @fsockopen('127.0.0.1', self::$serverPort, $errno, $errstr, 0.1);
            if ($conn !== false) {
                fclose($conn);
                return;
            }
            usleep(100000);
        }
        self::fail('echo 서버가 기동되지 않았습니다');
    }

    /**
     * echo 응답에서 헤더 값을 찾는다 (이름 대소문자 무시). 없으면 null.
     */
    protected static function echoHeader($response, $name)
    {
        foreach ((array)$response->headers as $headerName => $value) {
            if (strcasecmp($headerName, $name) === 0) {
                return $value;
            }
        }
        return null;
    }
}

<?php

namespace Bootpay\ServerPhp\Test\Commerce;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayCommerceApi;

class UserTest extends TestConfig
{
    /** @var BootpayCommerceApi */
    private static $api;

    public static function setUpBeforeClass(): void
    {
        self::$api = self::createCommerceApiWithToken();
    }

    public function testUserList()
    {
        $response = self::$api->user->getList(array(
            'page' => 1,
            'limit' => 10
        ));
        $this->printResponse('Commerce user.list', $response);

        $this->assertNotNull($response);
    }

    public function testUserListWithKeyword()
    {
        $response = self::$api->user->getList(array(
            'page' => 1,
            'limit' => 5,
            'keyword' => 'test'
        ));
        $this->printResponse('Commerce user.list (keyword)', $response);

        $this->assertNotNull($response);
    }

    public function testUserJoin()
    {
        $uniqueId = 'phpunit_' . time();
        $response = self::$api->user->join(array(
            'login_id' => $uniqueId,
            'login_pw' => 'test1234!',
            'username' => 'PHPUnit Test User',
            'email' => $uniqueId . '@test.com',
            'phone' => '01099998888'
        ));
        $this->printResponse('Commerce user.join', $response);

        $this->assertNotNull($response);
    }

    public function testUserCheckExist()
    {
        $response = self::$api->user->checkExist('login_id', 'phpunit_test');
        $this->printResponse('Commerce user.checkExist', $response);

        $this->assertNotNull($response);
    }

    public function testUserToken()
    {
        $response = self::$api->user->token(self::TEST_USER_ID);
        $this->printResponse('Commerce user.token', $response);

        $this->assertNotNull($response);
    }

    public function testUserLogin()
    {
        $response = self::$api->user->login('test_login_id', 'test_password');
        $this->printResponse('Commerce user.login', $response);

        // Login may fail with test data, but should return a response
        $this->assertNotNull($response);
    }

    public function testUserDetail()
    {
        // First get a user from the list
        $listResponse = self::$api->user->getList(array(
            'page' => 1,
            'limit' => 1
        ));

        if (isset($listResponse->items) && count($listResponse->items) > 0) {
            $userId = $listResponse->items[0]->user_id;
            $response = self::$api->user->detail($userId);
            $this->printResponse('Commerce user.detail', $response);

            $this->assertNotNull($response);
        } else {
            $this->markTestSkipped('No users available for detail test');
        }
    }

    public function testUserUpdate()
    {
        // First get a user from the list
        $listResponse = self::$api->user->getList(array(
            'page' => 1,
            'limit' => 1
        ));

        if (isset($listResponse->items) && count($listResponse->items) > 0) {
            $userId = $listResponse->items[0]->user_id;
            $response = self::$api->user->update(array(
                'user_id' => $userId,
                'username' => 'PHPUnit Updated User'
            ));
            $this->printResponse('Commerce user.update', $response);

            $this->assertNotNull($response);
        } else {
            $this->markTestSkipped('No users available for update test');
        }
    }

    public function testUserGroupList()
    {
        $response = self::$api->userGroup->getList(array(
            'page' => 1,
            'limit' => 10
        ));
        $this->printResponse('Commerce userGroup.list', $response);

        $this->assertNotNull($response);
    }

    public function testUserGroupCreate()
    {
        $response = self::$api->userGroup->create(array(
            'name' => 'PHPUnit Test Group ' . time(),
            'corporate_type' => 0
        ));
        $this->printResponse('Commerce userGroup.create', $response);

        $this->assertNotNull($response);
    }
}

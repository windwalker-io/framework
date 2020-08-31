<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authentication\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Authentication\AuthResult;
use Windwalker\Authentication\Method\ArrayMethod;

/**
 * Test class of ArrayMethod
 *
 * @since 2.0
 */
class LocalMethodTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var ArrayMethod
     */
    protected ?ArrayMethod $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $users = [
            'sakura' => [
                'username' => 'sakura',
                'password' => 'qwer',
            ],
            'flower' => [
                'username' => 'flower',
                'password' => '1234',
            ],
        ];

        $this->instance = new ArrayMethod($users);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * Method to test authenticate().
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $this->instance->setVerifyHandler(fn($pass, $hash) => $pass === $hash);

        $credential = ['username' => 'flower', 'password' => '1234'];

        // Test success
        $result = $this->instance->authenticate($credential);
        self::assertTrue($result->isSuccess());

        self::assertEquals(AuthResult::SUCCESS, $result->status);

        // Test invalid
        $credential['password'] = '5678';

        $result = $this->instance->authenticate($credential);

        self::assertFalse($result->isSuccess());

        self::assertEquals(AuthResult::INVALID_CREDENTIAL, $result->getStatus());

        // Test no user
        $credential['username'] = 'olive';

        $result = $this->instance->authenticate($credential);

        self::assertFalse($result->isSuccess());

        self::assertEquals(AuthResult::USER_NOT_FOUND, $result->getStatus());
    }
}

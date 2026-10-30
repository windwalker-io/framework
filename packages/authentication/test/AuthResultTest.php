<?php

declare(strict_types=1);

namespace Windwalker\Authentication\Test;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Authentication\AuthResult;

class AuthResultTest extends TestCase
{
    public static function statusFactoriesProvider(): array
    {
        return [
            'user not found' => ['userNotFound', AuthResult::USER_NOT_FOUND],
            'empty credential' => ['emptyCredential', AuthResult::EMPTY_CREDENTIAL],
            'success' => ['success', AuthResult::SUCCESS],
            'invalid password' => ['invalidPassword', AuthResult::INVALID_PASSWORD],
            'invalid username' => ['invalidUsername', AuthResult::INVALID_USERNAME],
            'invalid credential' => ['invalidCredential', AuthResult::INVALID_CREDENTIAL],
            'authorize fail' => ['authorizeFail', AuthResult::AUTHORIZE_FAIL]
        ];
    }

    #[DataProvider('statusFactoriesProvider')]
    #[Test]
    public function statusFactoryCreatesResult(string $factory, string $status): void
    {
        $credential = ['username' => 'user', 'password' => 'secret'];
        $exception = new RuntimeException('authentication failed');

        $result = AuthResult::$factory($credential, $exception);

        self::assertSame($status, $result->getStatus());
        self::assertSame($credential, $result->getCredential());
        self::assertSame($exception, $result->getException());
    }

    #[Test]
    public function successAndFailureAreMutuallyExclusive(): void
    {
        $success = new AuthResult(AuthResult::SUCCESS, []);
        $failure = new AuthResult(AuthResult::INVALID_CREDENTIAL, []);

        self::assertTrue($success->isSuccess());
        self::assertFalse($success->isFailure());
        self::assertFalse($failure->isSuccess());
        self::assertTrue($failure->isFailure());
    }

    #[Test]
    public function undefinedStatusFactoryThrowsBadMethodCallException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageIsOrContains('Call to undefined method');

        AuthResult::notAStatus([]);
    }
}

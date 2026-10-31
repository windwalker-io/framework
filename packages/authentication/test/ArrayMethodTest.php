<?php

declare(strict_types=1);

namespace Windwalker\Authentication\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Authentication\AuthResult;
use Windwalker\Authentication\Method\ArrayMethod;

class ArrayMethodTest extends TestCase
{
    public static function emptyCredentialProvider(): array
    {
        return [
            'empty username' => [['username' => '', 'password' => 'secret']],
            'empty password' => [['username' => 'user', 'password' => '']]
        ];
    }

    #[DataProvider('emptyCredentialProvider')]
    #[Test]
    public function authenticateRejectsEmptyCredentials(array $credential): void
    {
        $method = new ArrayMethod([
            'user' => ['password' => 'secret']
        ]);

        $result = $method->authenticate($credential);

        self::assertSame(AuthResult::EMPTY_CREDENTIAL, $result->getStatus());
        self::assertSame($credential, $result->getCredential());
    }

    #[Test]
    public function authenticateReturnsUserNotFoundForUnknownUsername(): void
    {
        $credential = ['username' => 'unknown', 'password' => 'secret'];
        $result = new ArrayMethod()->authenticate($credential);

        self::assertSame(AuthResult::USER_NOT_FOUND, $result->getStatus());
        self::assertTrue($result->isFailure());
        self::assertSame($credential, $result->getCredential());
    }

    #[Test]
    public function authenticateReturnsInvalidCredentialWhenStoredPasswordIsMissing(): void
    {
        $method = new ArrayMethod([
            'user' => ['username' => 'user']
        ]);

        $result = $method->authenticate(['username' => 'user', 'password' => 'secret']);

        self::assertSame(AuthResult::INVALID_CREDENTIAL, $result->getStatus());
    }

    #[Test]
    public function authenticatePassesCredentialsToCustomVerifier(): void
    {
        $method = new ArrayMethod([
            'user' => ['password' => 'stored-hash']
        ]);
        $method->setVerifyHandler(function (string $password, string $hash): bool {
            self::assertSame('submitted-password', $password);
            self::assertSame('stored-hash', $hash);

            return false;
        });

        $result = $method->authenticate([
            'username' => 'user',
            'password' => 'submitted-password'
        ]);

        self::assertSame(AuthResult::INVALID_CREDENTIAL, $result->getStatus());
    }

    #[Test]
    public function authenticateSucceedsWhenCustomVerifierAcceptsPassword(): void
    {
        $credential = ['username' => 'user', 'password' => 'secret'];
        $method = new ArrayMethod([
            'user' => ['password' => 'password-hash']
        ]);

        self::assertSame($method, $method->setVerifyHandler(
            static fn(string $password, string $hash): bool => $password === 'secret' && $hash === 'password-hash'
        ));

        $result = $method->authenticate($credential);

        self::assertSame(AuthResult::SUCCESS, $result->getStatus());
        self::assertTrue($result->isSuccess());
        self::assertSame($credential, $result->getCredential());
    }

    #[Test]
    public function authenticateUsesPasswordVerifyByDefault(): void
    {
        $method = new ArrayMethod([
            'user' => ['password' => password_hash('secret', PASSWORD_DEFAULT)]
        ]);

        self::assertTrue($method->authenticate(['username' => 'user', 'password' => 'secret'])->isSuccess());
        self::assertSame(
            AuthResult::INVALID_CREDENTIAL,
            $method->authenticate(['username' => 'user', 'password' => 'wrong'])->getStatus()
        );
    }

    #[Test]
    public function usersCanBeReplaced(): void
    {
        $method = new ArrayMethod();
        $users = ['user' => ['password' => password_hash('secret', PASSWORD_DEFAULT)]];

        self::assertSame($method, $method->setUsers($users));
        self::assertSame($users, $method->getUsers());
        self::assertTrue($method->authenticate(['username' => 'user', 'password' => 'secret'])->isSuccess());
    }
}

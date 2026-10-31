<?php

declare(strict_types=1);

namespace Windwalker\Authentication\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Authentication\Authentication;
use Windwalker\Authentication\AuthResult;
use Windwalker\Authentication\Method\MethodInterface;

class AuthenticationTest extends TestCase
{
    #[Test]
    public function authenticateReturnsFailureWhenNoMethodsAreRegistered(): void
    {
        $results = new Authentication()->authenticate(['username' => 'user', 'password' => 'secret']);

        self::assertTrue($results->isFailure());
        self::assertCount(0, $results);
        self::assertNull($results->getMatchedMethod());
    }

    #[Test]
    public function authenticateStopsAfterTheFirstSuccessfulMethod(): void
    {
        $credential = ['username' => 'user', 'password' => 'secret'];

        $firstMethod = $this->createMock(MethodInterface::class);
        $firstMethod
            ->expects($this->once())
            ->method('authenticate')
            ->with($credential)
            ->willReturn(new AuthResult(AuthResult::SUCCESS, $credential));

        $laterMethod = $this->createMock(MethodInterface::class);
        $laterMethod->expects($this->never())->method('authenticate');

        $results = new Authentication([
            'first' => $firstMethod,
            'later' => $laterMethod
        ])->authenticate($credential);

        self::assertTrue($results->isSuccess());
        self::assertSame('first', $results->getMatchedMethod());
        self::assertCount(1, $results);
        self::assertSame(AuthResult::SUCCESS, $results->getMatchedResult()->getStatus());
    }

    #[Test]
    public function authenticateTriesEveryMethodWhenAllMethodsFail(): void
    {
        $credential = ['username' => 'user', 'password' => 'wrong'];
        $firstResult = new AuthResult(AuthResult::INVALID_CREDENTIAL, $credential);
        $secondResult = new AuthResult(AuthResult::USER_NOT_FOUND, $credential);

        $firstMethod = $this->createMock(MethodInterface::class);
        $firstMethod
            ->expects($this->once())
            ->method('authenticate')
            ->with($credential)
            ->willReturn($firstResult);

        $secondMethod = $this->createMock(MethodInterface::class);
        $secondMethod
            ->expects($this->once())
            ->method('authenticate')
            ->with($credential)
            ->willReturn($secondResult);

        $results = new Authentication([
            'first' => $firstMethod,
            'second' => $secondMethod
        ])->authenticate($credential);

        self::assertTrue($results->isFailure());
        self::assertNull($results->getMatchedMethod());
        self::assertSame(
            [
                'first' => $firstResult,
                'second' => $secondResult
            ],
            $results->getResults()
        );
    }

    #[Test]
    public function addGetAndRemoveMethods(): void
    {
        $authentication = new Authentication();
        $method = $this->createStub(MethodInterface::class);

        self::assertSame($authentication, $authentication->addMethod('local', $method));
        self::assertSame($method, $authentication->getMethod('local'));
        self::assertNull($authentication->getMethod('missing'));

        self::assertSame($authentication, $authentication->removeMethod('local'));
        self::assertNull($authentication->getMethod('local'));
    }
}

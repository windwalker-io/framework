<?php

declare(strict_types=1);

namespace Windwalker\Authorization\Test;

use OutOfBoundsException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Authorization\Authorization;
use Windwalker\Authorization\AuthorizationInterface;
use Windwalker\Authorization\PolicyProviderInterface;

enum AuthorizationTestPolicy: string
{
    case VIEW = 'view';
}

class AuthorizationTest extends TestCase
{
    #[Test]
    public function authorizeRegisteredCallbackWithUserAndArguments(): void
    {
        $authorization = new Authorization();
        $user = (object) ['id' => 42];

        $authorization->addPolicy(
            'view',
            static fn (object $policyUser, string $resource): bool => $policyUser === $user
                && $resource === 'article'
        );

        self::assertTrue($authorization->authorize('view', $user, 'article'));
        self::assertFalse($authorization->authorize('view', $user, 'comment'));
    }

    #[Test]
    public function registerPolicyByEnumAndResolveItByName(): void
    {
        $authorization = new Authorization();
        $authorization->addPolicy(AuthorizationTestPolicy::VIEW, static fn(): bool => true);

        self::assertTrue($authorization->hasPolicy(AuthorizationTestPolicy::VIEW));
        self::assertTrue($authorization->hasPolicy('view'));
        self::assertTrue($authorization->authorize(AuthorizationTestPolicy::VIEW, null));
        self::assertSame($authorization->getPolicy('view'), $authorization->getPolicy(AuthorizationTestPolicy::VIEW));
    }

    #[Test]
    public function setPoliciesRegistersPoliciesAndSupportsChaining(): void
    {
        $authorization = new Authorization();

        self::assertSame(
            $authorization,
            $authorization->setPolicies(
                [
                    'view' => static fn(): bool => true,
                    'edit' => static fn(): bool => false,
                ]
            )
        );

        self::assertTrue($authorization->authorize('view', null));
        self::assertFalse($authorization->authorize('edit', null));
    }

    #[Test]
    public function registerPolicyProvider(): void
    {
        $authorization = new Authorization();
        $provider = new class implements PolicyProviderInterface {
            public function register(AuthorizationInterface $auth): void
            {
                $auth->addPolicy('provided', static fn(mixed $user, string $role): bool => $role === 'admin');
            }
        };

        self::assertSame($authorization, $authorization->registerPolicyProvider($provider));
        self::assertTrue($authorization->authorize('provided', null, 'admin'));
        self::assertFalse($authorization->authorize('provided', null, 'guest'));
    }

    #[Test]
    public function authorizeThrowsForUnknownPolicy(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessageIsOrContains('Policy "missing" not exists');

        new Authorization()->authorize('missing', null);
    }
}

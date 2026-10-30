<?php

declare(strict_types=1);

namespace Windwalker\Authorization\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Authorization\CallbackPolicy;

class CallbackPolicyTest extends TestCase
{
    #[Test]
    public function authorizeForwardsUserAndArgumentsToHandler(): void
    {
        $user = (object) ['id' => 42];
        $handler = static fn(object $policyUser, string $resource): bool => $policyUser === $user
            && $resource === 'article';
        $policy = new CallbackPolicy($handler);

        self::assertTrue($policy->authorize($user, 'article'));
        self::assertFalse($policy->authorize($user, 'comment'));
    }
}

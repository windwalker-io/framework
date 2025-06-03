<?php

declare(strict_types=1);

namespace Windwalker\Authorization;

/**
 * The AuthorizationInterface class.
 *
 * @since  3.0
 */
interface AuthorizationInterface
{
    public function authorize(string|\UnitEnum $policy, mixed $user, mixed ...$args): bool;

    public function addPolicy(string|\UnitEnum $name, callable|PolicyInterface $handler): static;

    public function hasPolicy(string|\UnitEnum $name): bool;

    public function registerPolicyProvider(PolicyProviderInterface $policy): static;
}

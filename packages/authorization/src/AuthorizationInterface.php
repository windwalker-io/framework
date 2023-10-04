<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authorization;

/**
 * The AuthorizationInterface class.
 *
 * @since  3.0
 */
interface AuthorizationInterface
{
    /**
     * authorise
     *
     * @param  string  $policy
     * @param  mixed   $user
     * @param  mixed   ...$args
     *
     * @return  bool
     */
    public function authorize(string $policy, mixed $user, mixed ...$args): bool;

    /**
     * addPolicy
     *
     * @param  string    $name
     * @param  callable  $handler
     *
     * @return  static
     */
    public function addPolicy(string $name, callable $handler): static;

    /**
     * hasPolicy
     *
     * @param  string  $name
     *
     * @return  bool
     */
    public function hasPolicy(string $name): bool;

    /**
     * registerPolicy
     *
     * @param  PolicyProviderInterface  $policy
     *
     * @return  static
     */
    public function registerPolicyProvider(PolicyProviderInterface $policy): static;
}

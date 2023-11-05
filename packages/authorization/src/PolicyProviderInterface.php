<?php

declare(strict_types=1);

namespace Windwalker\Authorization;

/**
 * Interface PolicyInterface
 *
 * @since  3.0
 */
interface PolicyProviderInterface
{
    /**
     * register
     *
     * @param  AuthorizationInterface  $auth
     *
     * @return  void
     */
    public function register(AuthorizationInterface $auth): void;
}

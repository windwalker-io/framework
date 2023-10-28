<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authentication\Test\Mock;

use Windwalker\Authentication\AuthResult;
use Windwalker\Authentication\Credential;
use Windwalker\Authentication\Method\AbstractMethod;
use Windwalker\Authentication\Method\MethodInterface;

/**
 * The MockMethod class.
 *
 * @since  2.0
 */
class MockMethod implements MethodInterface
{
    /**
     * authenticate
     *
     * @param  array  $credential
     *
     * @return AuthResult
     */
    public function authenticate(array $credential): AuthResult
    {
        if ($credential['username'] === 'flower') {
            if ($credential['password'] === '1234') {
                return new AuthResult(AuthResult::SUCCESS, $credential);
            }

            return new AuthResult(AuthResult::INVALID_CREDENTIAL, $credential);
        }

        return new AuthResult(AuthResult::USER_NOT_FOUND, $credential);
    }
}

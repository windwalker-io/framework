<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authentication;

use Throwable;
use Windwalker\Utilities\StrNormalize;

/**
 * The AuthenticationResult class.
 *
 * @method static $this userNotFound(array $credential, \Throwable $e = null)
 * @method static $this emptyCredential(array $credential, \Throwable $e = null)
 * @method static $this success(array $credential, \Throwable $e = null)
 * @method static $this invalidPassword(array $credential, \Throwable $e = null)
 * @method static $this invalidUsername(array $credential, \Throwable $e = null)
 * @method static $this invalidCredential(array $credential, \Throwable $e = null)
 * @method static $this authorizeFail(array $credential, \Throwable $e = null)
 */
class AuthResult
{
    public const USER_NOT_FOUND = 'USER_NOT_FOUND';

    public const EMPTY_CREDENTIAL = 'EMPTY_CREDENTIAL';

    public const SUCCESS = 'SUCCESS';

    public const INVALID_PASSWORD = 'INVALID_PASSWORD';

    public const INVALID_USERNAME = 'INVALID_USERNAME';

    public const INVALID_CREDENTIAL = 'INVALID_CREDENTIAL';

    public const AUTHORIZE_FAIL = 'AUTHORIZE_FAIL';

    public string $status;

    protected array $credential = [];

    public ?Throwable $exception = null;

    /**
     * AuthResult constructor.
     *
     * @param  string           $status
     * @param  array            $credential
     * @param  Throwable|null  $e
     */
    public function __construct(string $status, array $credential, Throwable $e = null)
    {
        $this->status = $status;
        $this->credential = $credential;
        $this->exception = $e;
    }

    public function isSuccess(): bool
    {
        return $this->status === static::SUCCESS;
    }

    public function isFailure(): bool
    {
        return !$this->isSuccess();
    }

    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    /**
     * @return array
     */
    public function getCredential(): array
    {
        return $this->credential;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    public static function __callStatic(string $name, array $args): mixed
    {
        $status = strtoupper(StrNormalize::toUnderscoreSeparated($name));

        if (defined(static::class . '::' . $status)) {
            return new static($status, ...$args);
        }

        throw new \BadMethodCallException(
            sprintf(
                'Call to undefined method %s::%s()',
                static::class,
                $name
            )
        );
    }
}

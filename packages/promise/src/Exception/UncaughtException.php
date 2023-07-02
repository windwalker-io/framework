<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Exception;

use Exception;
use Throwable;

/**
 * The UncaughtException class.
 */
class UncaughtException extends Exception
{
    private mixed $reason;

    /**
     * UncaughtException constructor.
     *
     * @param  mixed            $reason
     * @param  Throwable|null  $previous
     */
    public function __construct(mixed $reason, ?Throwable $previous = null)
    {
        $this->reason = $reason;

        $message = '';
        $code = 0;

        if ($reason instanceof Throwable) {
            $message = $reason->getMessage();
            $code = $reason->getCode();
            $previous = $reason;
        } else {
            if (is_scalar($reason)) {
                $message = (string) $reason;
            }
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Method to get property Reason
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getReason(): mixed
    {
        return $this->reason;
    }
}

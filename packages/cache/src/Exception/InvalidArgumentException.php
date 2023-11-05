<?php

declare(strict_types=1);

namespace Windwalker\Cache\Exception;

/**
 * The InvalidArgumentException class.
 */
class InvalidArgumentException extends \InvalidArgumentException implements
    \Psr\Cache\InvalidArgumentException,
    \Psr\SimpleCache\InvalidArgumentException
{
    //
}

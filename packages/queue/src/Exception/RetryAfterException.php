<?php

declare(strict_types=1);

namespace Windwalker\Queue\Exception;

class RetryAfterException extends \RuntimeException
{
    public function __construct(public int $delay)
    {
        parent::__construct();
    }
}

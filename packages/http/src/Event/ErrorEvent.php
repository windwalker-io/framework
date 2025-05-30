<?php

declare(strict_types=1);

namespace Windwalker\Http\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Event\AbstractEvent;
use Windwalker\Event\Events\ErrorEventTrait;
use Windwalker\Http\Output\OutputInterface;

/**
 * The ErrorEvent class.
 */
class ErrorEvent extends RequestEvent
{
    use ErrorEventTrait;

    public function __construct(
        \Throwable $exception,
        ServerRequestInterface $request,
        OutputInterface $output,
        ?ResponseInterface $response = null,
        ?\Closure $endHandler = null,
        array $attributes = [],
        int $fd = 0,
    ) {
        $this->exception = $exception;

        parent::__construct(
            request: $request,
            response: $response,
            output: $output,
            endHandler: $endHandler,
            attributes: $attributes,
            fd: $fd
        );
    }
}

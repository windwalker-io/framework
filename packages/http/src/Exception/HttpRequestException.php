<?php

declare(strict_types=1);

namespace Windwalker\Http\Exception;

use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;
use Windwalker\Database\Driver\StatementInterface;

/**
 * The HttpRequestException class.
 *
 * @since  3.5.2
 */
class HttpRequestException extends UnexpectedValueException
{
    public protected(set) ?ResponseInterface $response;

    public StatementInterface|null $body {
        get => $this->response?->getBody();
    }

    public function withResponse(ResponseInterface $response): static
    {
        $new = clone $this;
        $new->response = $response;

        return $new;
    }
}

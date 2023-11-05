<?php

declare(strict_types=1);

namespace Windwalker\Http\Factory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Response\Response;

/**
 * The ResponseFactory class.
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return (new Response('php://memory'))->withStatus($code, $reasonPhrase);
    }
}

<?php

declare(strict_types=1);

namespace Windwalker\Uri;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * The UriFactory class.
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * @inheritDoc
     *
     * @codeCoverageIgnore
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}

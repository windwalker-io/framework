<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Psr\Http\Message\UriInterface;
use Windwalker\Stream\NullStream;

/**
 * The RedirectResponse class.
 *
 * @since  3.0
 */
class RedirectResponse extends Response
{
    /**
     * Constructor.
     *
     * @param  string|UriInterface $uri     The redirect uri.
     * @param  int                 $status  The status code.
     * @param  array               $headers The custom headers.
     */
    public function __construct($uri, $status = 303, array $headers = [])
    {
        if ($uri instanceof UriInterface) {
            $uri = (string) $uri;
        }

        if (!is_string($uri)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid URI type, string or UriInterface required, %s provided.',
                    gettype($uri)
                )
            );
        }

        $headers['location'] = [$uri];

        parent::__construct(new NullStream(), $status, $headers);
    }
}

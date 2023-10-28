<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Stream;

use Psr\Http\Message\StreamInterface;

/**
 * The PhpInputStream class.
 *
 * @since  2.1
 */
class PhpInputStream extends CachingStream
{
    /**
     * Class init.
     *
     * @param  StreamInterface|null  $cache The cache stream.
     */
    public function __construct(?StreamInterface $cache = null)
    {
        parent::__construct(new Stream('php://input', READ_ONLY_FROM_BEGIN), $cache);
    }
}

<?php

declare(strict_types=1);

namespace Windwalker\Stream;

use Psr\Http\Message\StreamInterface;

/**
 * The PhpInputStream class.
 *
 * @since  2.1
 *
 * @internal
 */
class PhpInputStream extends CachingStream
{
    public static self $instance;

    /**
     * Class init.
     *
     * @param  StreamInterface|null  $cache The cache stream.
     */
    protected function __construct(?StreamInterface $cache = null)
    {
        parent::__construct(new Stream('php://input', READ_ONLY_FROM_BEGIN), $cache);
    }

    public static function getInstance(?StreamInterface $cache = null): self
    {
        return self::$instance ?? new PhpInputStream($cache);
    }

    public static function getInstanceAndCached(?StreamInterface $cache = null): self
    {
        $stream = static::getInstance($cache);
        $stream->getContents();
        $stream->rewind();

        return $stream;
    }
}

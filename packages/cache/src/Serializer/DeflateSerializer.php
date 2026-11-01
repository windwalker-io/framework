<?php

declare(strict_types=1);

namespace Windwalker\Cache\Serializer;

use Windwalker\Cache\Exception\RuntimeException;

/**
 * Compresses another serializer's string output using gzdeflate().
 */
class DeflateSerializer implements SerializerInterface
{
    public function __construct(
        private SerializerInterface $serializer = new RawSerializer(),
    ) {
        if (!\function_exists('gzdeflate') || !\function_exists('gzinflate')) {
            throw new RuntimeException('The "zlib" PHP extension is not loaded.');
        }
    }

    /**
     * Encode data through the inner serializer, then compress the resulting string.
     */
    public function serialize(mixed $data): ?string
    {
        $serialized = $this->serializer->serialize($data);

        if ($serialized === null) {
            return null;
        }

        if (!is_string($serialized)) {
            throw new \LogicException(static::class . ' requires the inner serializer to return a string.');
        }

        $compressed = \gzdeflate($serialized);

        if ($compressed === false) {
            throw new RuntimeException('Unable to deflate serialized cache payload.');
        }

        return $compressed;
    }

    /**
     * Inflate data when possible, then delegate decoding to the inner serializer.
     *
     * Falling back to the original payload keeps backward compatibility with
     * values that were stored before compression was enabled.
     */
    public function unserialize(mixed $data): mixed
    {
        if (is_string($data) && false !== $inflated = @gzinflate($data)) {
            $data = $inflated;
        }

        return $this->serializer->unserialize($data);
    }
}

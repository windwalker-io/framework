<?php

declare(strict_types=1);

namespace Windwalker\Cache\Serializer;

/**
 * The RawDataHandler class.
 *
 * @since  2.1.2
 */
class RawSerializer implements SerializerInterface
{
    /**
     * Encode data.
     *
     * @param  mixed  $data
     *
     * @return  mixed
     */
    public function serialize(mixed $data): mixed
    {
        return $data;
    }

    /**
     * Decode data.
     *
     * @param  string  $data
     *
     * @return  mixed
     */
    public function unserialize(mixed $data): mixed
    {
        return $data;
    }
}

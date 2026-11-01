<?php

declare(strict_types=1);

namespace Windwalker\Cache\Serializer;

/**
 * Interface DataHandlerInterface
 */
interface SerializerInterface
{
    /**
     * Encode data.
     *
     * @param  mixed  $data
     *
     * @return  mixed  String for scalar-only storages, or structured data (array, etc.) for storages that support it.
     */
    public function serialize(mixed $data): mixed;

    /**
     * Decode data.
     *
     * @param  mixed  $data
     *
     * @return  mixed
     */
    public function unserialize(mixed $data): mixed;
}

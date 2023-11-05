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
     * @return string|null
     */
    public function serialize(mixed $data): ?string;

    /**
     * Decode data.
     *
     * @param  mixed  $data
     *
     * @return mixed
     */
    public function unserialize(string $data): mixed;
}

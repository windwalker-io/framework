<?php

declare(strict_types=1);

namespace Windwalker\Cache\Serializer;

/**
 * Delegates encoding and decoding to user-provided callbacks.
 *
 * @psalm-type SerializeCallback = \Closure(mixed $data): mixed
 * @psalm-type UnserializeCallback = \Closure(mixed $data): mixed
 */
readonly class CallbackSerializer implements SerializerInterface
{
    /**
     * @param  SerializeCallback    $serializer
     * @param  UnserializeCallback  $unserializer
     */
    public function __construct(
        private \Closure $serializer,
        private \Closure $unserializer,
    ) {}

    public function serialize(mixed $data): mixed
    {
        return ($this->serializer)($data);
    }

    public function unserialize(mixed $data): mixed
    {
        return ($this->unserializer)($data);
    }
}

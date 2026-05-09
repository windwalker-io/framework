<?php

declare(strict_types=1);

namespace Windwalker\Cache\Serializer;

use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\Symmetric\CipherInterface;

use const Windwalker\Crypt\ENCODER_BASE64URLSAFE;

/**
 * Encrypts another serializer's string output using a symmetric cipher.
 */
class CipherSerializer implements SerializerInterface
{
    private string|\Closure $encoder;

    public function __construct(
        private CipherInterface $cipher,
        #[\SensitiveParameter]
        private Key|string $key,
        private SerializerInterface $serializer = new RawSerializer(),
        string|callable $encoder = ENCODER_BASE64URLSAFE,
    ) {
        if (is_string($encoder)) {
            $this->encoder = $encoder;
        } else {
            $this->encoder = $encoder(...);
        }
    }

    public function serialize(#[\SensitiveParameter] mixed $data): ?string
    {
        $serialized = $this->serializer->serialize($data);

        if ($serialized === null) {
            return null;
        }

        return $this->cipher->encrypt($serialized, $this->key, $this->encoder);
    }

    public function unserialize(#[\SensitiveParameter] string $data): mixed
    {
        $decrypted = $this->cipher->decrypt($data, $this->key, $this->encoder);

        // CipherInterface::decrypt returns HiddenString, convert back to plain string for inner serializer.
        return $this->serializer->unserialize(HiddenString::strip($decrypted));
    }
}

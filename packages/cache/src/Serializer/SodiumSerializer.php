<?php

declare(strict_types=1);

namespace Windwalker\Cache\Serializer;

use Windwalker\Cache\Exception\InvalidArgumentException;
use Windwalker\Cache\Exception\RuntimeException;

/**
 * Encrypts another serializer's string output using sodium_crypto_box_seal().
 *
 * Decryption first tries the current keypair, then any rotated legacy keypairs.
 */
class SodiumSerializer implements SerializerInterface
{
    /**
     * @var array<string>
     */
    private array $keys;

    /**
     * @param  string    $key           Active decryption keypair.
     * @param  string[]  $previousKeys  Rotated legacy keypairs, used only for decrypt.
     */
    public function __construct(
        #[\SensitiveParameter]
        string $key,
        #[\SensitiveParameter]
        array $previousKeys = [],
        private SerializerInterface $serializer = new RawSerializer(),
    ) {
        if (!\function_exists('sodium_crypto_box_seal') || !\function_exists('sodium_crypto_box_seal_open')) {
            throw new RuntimeException('The "sodium" PHP extension is not loaded.');
        }

        $this->keys = [$this->assertKeypair($key), ...array_map($this->assertKeypair(...), $previousKeys)];
    }

    public function serialize(#[\SensitiveParameter] mixed $data): ?string
    {
        $serialized = $this->serializer->serialize($data);

        if ($serialized === null) {
            return null;
        }

        $publicKey = sodium_crypto_box_publickey($this->keys[0]);
        $ciphertext = sodium_crypto_box_seal($serialized, $publicKey);

        return base64_encode($ciphertext);
    }

    public function unserialize(#[\SensitiveParameter] string $data): mixed
    {
        $decoded = base64_decode($data, true);

        if ($decoded === false) {
            throw new RuntimeException('Invalid base64 payload for sodium-encrypted cache value.');
        }

        foreach ($this->keys as $keypair) {
            $plaintext = @sodium_crypto_box_seal_open($decoded, $keypair);

            if ($plaintext !== false) {
                return $this->serializer->unserialize($plaintext);
            }
        }

        throw new RuntimeException('Unable to decrypt sodium-encrypted cache payload with any configured key.');
    }

    private function assertKeypair(#[\SensitiveParameter] string $keypair): string
    {
        if (strlen($keypair) !== SODIUM_CRYPTO_BOX_KEYPAIRBYTES) {
            throw new InvalidArgumentException(
                sprintf(
                    'Sodium keypair must be %d bytes, %d given.',
                    SODIUM_CRYPTO_BOX_KEYPAIRBYTES,
                    strlen($keypair)
                )
            );
        }

        return $keypair;
    }
}


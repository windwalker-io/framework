<?php

declare(strict_types=1);

namespace Windwalker\Queue\Enum;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use function Windwalker\to_uuid;

enum DatabaseIdType
{
    case INT;
    case UUID;
    case UUID_BIN;

    public function isUuid(): bool
    {
        return $this === self::UUID || $this === self::UUID_BIN;
    }

    public function isInt(): bool
    {
        return $this === self::INT;
    }

    public function shouldAutoIncrement(): bool
    {
        return $this === self::INT;
    }

    /**
     * @template T of int|string|UuidInterface
     *
     * @param  T  $id
     *
     * @return  T
     */
    public function toWritable(int|string|UuidInterface $id): mixed
    {
        if ($this === self::UUID_BIN) {
            if ($id instanceof UuidInterface) {
                return $id->getBytes();
            }

            if (strlen($id) === 16) {
                return $id;
            }

            if (strlen((string) $id) !== 36) {
                throw new \InvalidArgumentException('Invalid UUID string: ' . (string) $id);
            }

            return Uuid::fromString($id)->getBytes();
        }

        if ($this === self::UUID) {
            if ($id instanceof UuidInterface) {
                return (string) $id;
            }

            if (strlen($id) === 16) {
                return Uuid::fromBytes($id)->toString();
            }

            if (strlen((string) $id) !== 36) {
                throw new \InvalidArgumentException('Invalid UUID string: ' . (string) $id);
            }

            return (string) $id;
        }

        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('ID must be integer: ' . (string) $id);
        }

        return $id;
    }

    public function toReadable(mixed $id): int|string
    {
        if ($this === self::INT) {
            return (string) $id;
        }

        return (string) to_uuid($id);
    }
}

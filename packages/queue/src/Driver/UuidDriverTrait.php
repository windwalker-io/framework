<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Windwalker\Queue\Enum\DatabaseIdType;

trait UuidDriverTrait
{
    public protected(set) DatabaseIdType $idType = DatabaseIdType::INT;

    public function generateUuidString(): string
    {
        $uuid = $this->checkDependencyAndGenerateUuid();

        return $this->idType === DatabaseIdType::UUID_BIN ? $uuid->getBytes() : $uuid->toString();
    }

    public function checkDependencyAndGenerateUuid(): UuidInterface
    {
        if (!class_exists(Uuid::class)) {
            throw new \DomainException('Please install `ramsey/uuid` to use UUID queue driver.');
        }

        return $this->generateUuid();
    }

    public function generateUuid(): UuidInterface
    {
        return Uuid::uuid7();
    }
}

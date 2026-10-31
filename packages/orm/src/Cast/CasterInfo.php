<?php

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Metadata\EntityMember;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;

class CasterInfo
{
    public \ReflectionProperty $property {
        get => $this->column->getProperty();
    }

    public EntityMetadata $metadata {
        get => $this->orm->getMetadata($this->entity::class);
    }

    public EntityMember $member {
        get => $this->metadata->getPropertyMember($this->property->getName());
    }

    public function __construct(
        public readonly object $entity,
        public readonly bool $isNew,
        public readonly string $field,
        public readonly mixed $value,
        public readonly Column $column,
        public readonly ORM $orm,
    ) {}
}

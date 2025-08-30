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
        readonly public object $entity,
        readonly public bool $isNew,
        readonly public string $field,
        readonly public mixed $value,
        readonly public Column $column,
        readonly public ORM $orm,
    ) {
    }
}

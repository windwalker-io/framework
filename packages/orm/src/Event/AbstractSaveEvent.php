<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

/**
 * The AbstractSaveEvent class.
 */
abstract class AbstractSaveEvent extends AbstractEntityEvent
{
    public const string TYPE_CREATE = 'create';

    public const string TYPE_UPDATE = 'update';

    public const string TYPE_COPY = 'copy';

    public function __construct(
        public string $type = '',
        public ?array $oldData = null,
        public int $options = 0,
        public array|object $source = [],
        public array $extra = [],
        array $data = [],
    ) {
        parent::__construct($data);
    }

    public function isCreate(): bool
    {
        return $this->type === static::TYPE_CREATE;
    }

    public function isUpdate(): bool
    {
        return $this->type === static::TYPE_UPDATE;
    }

    public function getTempEntity(): object
    {
        return $this->entityMapper->toEntity($this->data);
    }

    public function getOldEntity(): ?object
    {
        return $this->entityMapper->tryEntity($this->oldData);
    }
}

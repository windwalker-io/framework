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

    public bool $isCreate {
        get => $this->type === self::TYPE_CREATE;
    }

    public bool $isUpdate {
        get => $this->type === self::TYPE_UPDATE;
    }

    public object $tempEntity {
        get => $this->entityMapper->toEntity($this->data);
    }

    public object $oldEntity {
        get => $this->entityMapper->tryEntity($this->oldData);
    }

    public function __construct(
        public string $type = '',
        public array|object $source = [],
        array $data = [],
        public ?array $oldData = null,
        public int $options = 0,
        public array $extra = [],
    ) {
        parent::__construct($data);
    }

    /**
     * @deprecated  Use property instead.
     */
    public function &getOptions(): int
    {
        return $this->options;
    }

    /**
     * @deprecated  Use property instead.
     */
    public function &getExtra(): array
    {
        return $this->extra;
    }

    public function isCreate(): bool
    {
        return $this->type === static::TYPE_CREATE;
    }

    public function isUpdate(): bool
    {
        return $this->type === static::TYPE_UPDATE;
    }

    /**
     * @deprecated  Use property instead.
     */
    public function getTempEntity(): object
    {
        return $this->entityMapper->toEntity($this->data);
    }

    /**
     * @deprecated  Use property instead.
     */
    public function getOldEntity(): ?object
    {
        return $this->entityMapper->tryEntity($this->oldData);
    }
}

<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

/**
 * The AbstractSaveEvent class.
 */
abstract class AbstractSaveEvent extends AbstractEntityEvent
{
    public const TYPE_CREATE = 'create';

    public const TYPE_UPDATE = 'update';

    public const TYPE_COPY = 'copy';

    protected string $type;

    protected ?array $oldData = null;

    protected int $options = 0;

    protected array|object $source = [];

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  string  $type
     *
     * @return  static  Return self to support chaining.
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getOldData(): ?array
    {
        return $this->oldData;
    }

    /**
     * @param  array|null  $oldData
     *
     * @return  static  Return self to support chaining.
     */
    public function setOldData(?array $oldData): static
    {
        $this->oldData = $oldData;

        return $this;
    }

    /**
     * @return array|object
     */
    public function getSource(): object|array
    {
        return $this->source;
    }

    /**
     * @param  array|object  $source
     *
     * @return  static  Return self to support chaining.
     */
    public function setSource(object|array $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function isCreate(): bool
    {
        return $this->getType() === static::TYPE_CREATE;
    }

    public function isUpdate(): bool
    {
        return $this->getType() === static::TYPE_UPDATE;
    }

    /**
     * @return int
     */
    public function &getOptions(): int
    {
        return $this->options;
    }

    /**
     * @param  int  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(int $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getTempEntity(): object
    {
        return $this->getMetadata()->getEntityMapper()->toEntity($this->getData());
    }

    public function getOldEntity(): ?object
    {
        return $this->getMetadata()->getEntityMapper()->tryEntity($this->getOldData());
    }
}

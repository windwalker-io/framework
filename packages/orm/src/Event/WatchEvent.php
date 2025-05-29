<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

/**
 * The WatchEvent class.
 */
class WatchEvent extends AbstractSaveEvent
{
    public function __construct(
        string $type,
        public ?AbstractEntityEvent $originEvent = null,
        array|object $source = [],
        array $data = [],
        public mixed $value = null,
        public mixed $oldValue = null,
        public ?\Closure $afterCallback = null,
        public bool $isUpdateWhere = false,
        ?array $oldData = null,
        int $options = 0,
        array $extra = [],
    ) {
        parent::__construct(
            type: $type,
            source: $source,
            data: $data,
            oldData: $oldData,
            options: $options,
            extra: $extra,
        );
    }

    /**
     * @return bool
     */
    public function isUpdateWhere(): bool
    {
        return $this->isUpdateWhere;
    }

    public function setDataRef(array &$data): static
    {
        $this->data = &$data;

        return $this;
    }
}

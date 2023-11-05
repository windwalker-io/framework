<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;

/**
 * The AfterCopyEvent class.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class AfterCopyEvent extends AbstractSaveEvent
{
    protected object $entity;

    protected array $fullData = [];

    /**
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * @param  object  $entity
     *
     * @return  static  Return self to support chaining.
     */
    public function setEntity(object $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get full data which set from BeforeSaveEvent.
     *
     * @return array
     *
     * @deprecated This is for B/C use.
     */
    public function getFullData(): array
    {
        return $this->fullData;
    }

    /**
     * @param  array  $fullData
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated This is for B/C use.
     */
    public function setFullData(array $fullData): static
    {
        $this->fullData = $fullData;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;

trait ORMEventTrait
{
    protected EntityMetadata $metadata;

    /**
     * @return EntityMetadata
     */
    public function getMetadata(): EntityMetadata
    {
        return $this->metadata;
    }

    /**
     * @param  EntityMetadata  $metadata
     *
     * @return  static  Return self to support chaining.
     */
    public function setMetadata(EntityMetadata $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getORM(): ORM
    {
        return $this->getMetadata()->getORM();
    }

    public function getEntityMapper(): EntityMapper
    {
        return $this->getMetadata()->getEntityMapper();
    }

    public function getDb(): DatabaseAdapter
    {
        return $this->getORM()->getDb();
    }
}

<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;

trait ORMEventTrait
{
    public EntityMetadata $metadata;

    public ORM $orm {
        get => $this->metadata->getORM();
    }

    public EntityMapper $entityMapper {
        get => $this->metadata->getEntityMapper();
    }

    public DatabaseAdapter $db {
        get => $this->metadata->getORM()->getDb();
    }

    /**
     * @deprecated  Use property hook instead.
     */
    public function getORM(): ORM
    {
        return $this->orm;
    }
}

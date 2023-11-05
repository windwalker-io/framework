<?php

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Relation\RelationCollection;

/**
 * The NestedEntityTrait class.
 */
trait MultiTreeNestedEntityTrait
{
    #[Column('root_id')]
    public mixed $rootId = null;

    public function getRootId(): mixed
    {
        return $this->rootId;
    }

    /**
     * @param  mixed  $rootId
     *
     * @return  static  Return self to support chaining.
     */
    public function setRootId(mixed $rootId): static
    {
        $this->rootId = $rootId;

        return $this;
    }
}

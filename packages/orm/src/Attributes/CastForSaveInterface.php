<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\ORM;

/**
 * @psalm-type  CasterClosure = Closure(mixed $value, ORM $orm, object $entity, bool $isNew, Column $column): mixed;
 */
interface CastForSaveInterface
{
    /**
     * @return  mixed|CasterClosure
     */
    public function getCaster(): mixed;
}

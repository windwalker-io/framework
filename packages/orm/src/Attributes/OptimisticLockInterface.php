<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\Metadata\EntityMember;
use Windwalker\Query\Query;

interface OptimisticLockInterface
{
    public EntityMember $member {
        get;
    }

    public function pushValueToNextValue(\ReflectionProperty $prop, mixed $value): mixed;

    public function buildConditions(Query $query, array $fullData): void;
}

<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\Metadata\EntityMember;

interface OptimisticLockInterface
{
    public EntityMember $member {
        get;
    }

    public function pushValueToNextValue(\ReflectionProperty $prop, mixed $value): mixed;
}

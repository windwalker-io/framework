<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

interface CastForSaveInterface
{
    public function getCaster(): mixed;
}

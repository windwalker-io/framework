<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\Cast\JsonCastArrayList;
use Windwalker\Utilities\TypeCast;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_PROPERTY)]
class JsonArray extends JsonCast
{
    protected function init(): void
    {
        parent::init();

        $this->options |= JsonCast::FORCE_ARRAY_LIST;
    }
}

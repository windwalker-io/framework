<?php

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use Windwalker\ORM\Attributes\CastAttributeInterface;
use Windwalker\ORM\Attributes\JsonSerializerInterface;

interface CompositeCastInterface extends CastAttributeInterface, CastInterface, JsonSerializerInterface
{
}

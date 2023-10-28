<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\Event\AbstractSaveEvent;
use Windwalker\ORM\Event\AbstractUpdateWhereEvent;
use Windwalker\ORM\Event\WatchEvent;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The Watch class.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class WatchBefore extends Watch
{
    public function __construct(callable|string $columnOrHandler, int $options = 0)
    {
        $options |= static::BEFORE_SAVE;

        parent::__construct($columnOrHandler, $options);
    }
}

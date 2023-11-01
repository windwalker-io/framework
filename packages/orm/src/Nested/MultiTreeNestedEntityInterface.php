<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\Relation\RelationCollection;

/**
 * The NestedEntityInterface class.
 */
interface MultiTreeNestedEntityInterface
{
    public function getRootId(): mixed;
}

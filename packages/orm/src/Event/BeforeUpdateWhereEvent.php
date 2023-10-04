<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;

/**
 * The BeforeUpdateBatchEvent class.
 */
#[Attribute]
class BeforeUpdateWhereEvent extends AbstractUpdateWhereEvent
{
}

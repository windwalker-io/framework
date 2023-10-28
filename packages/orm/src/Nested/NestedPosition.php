<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

/**
 * The NestedPosition class.
 */
enum NestedPosition
{
    case BEFORE;

    case AFTER;

    case FIRST_CHILD;

    case LAST_CHILD;
}

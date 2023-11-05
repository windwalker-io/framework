<?php

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

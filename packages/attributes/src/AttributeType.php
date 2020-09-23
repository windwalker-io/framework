<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Attributes;

/**
 * The AttributeType class.
 */
abstract class AttributeType
{
    public const CALLABLE = 'callable';

    public const PROPERTIES = 'properties';

    public const CLASSES = 'classes';

    public const PARAMETERS = 'parameters';
}

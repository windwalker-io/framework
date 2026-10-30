<?php

declare(strict_types=1);

namespace Windwalker\Attributes;

use Attribute;

/**
 * The AttributeType class.
 */
abstract class AttributeType
{
    public const int CLASSES = Attribute::TARGET_CLASS;

    public const int FUNCTIONS = Attribute::TARGET_FUNCTION;

    public const int METHODS = Attribute::TARGET_METHOD;

    public const int PROPERTIES = Attribute::TARGET_PROPERTY;

    public const int CLASS_CONSTANTS = Attribute::TARGET_CLASS_CONSTANT;

    public const int PARAMETERS = Attribute::TARGET_PARAMETER;

    public const int CALLABLE = 1 << 6;

    public const int ALL = (1 << 7) - 1;
}

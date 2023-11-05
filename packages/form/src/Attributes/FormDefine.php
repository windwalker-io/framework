<?php

declare(strict_types=1);

namespace Windwalker\Form\Attributes;

/**
 * The FormDefine class.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class FormDefine
{
    public function __construct(
        public ?int $ordering = null
    ) {
    }
}

<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Form\Attributes;

/**
 * The FormDefine class.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class FormDefine
{
    public function __construct(
        public ?string $fieldset = null,
        public ?string $ns = null,
        public ?int $ordering = null
    ) {
    }
}
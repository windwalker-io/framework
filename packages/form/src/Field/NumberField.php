<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

/**
 * The NumberField class.
 *
 * @since  3.0.1
 */
class NumberField extends AbstractHtml5Field
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected string $inputType = 'number';
}

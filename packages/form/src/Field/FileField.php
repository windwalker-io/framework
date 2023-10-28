<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

/**
 * The TextField class.
 *
 * @method  $this  multiple(bool $value = null)
 * @method  mixed  isMultiple()
 * @method  $this  accept(string $value = null)
 * @method  mixed  getAccept()
 * @method  mixed  capture($value)
 * @method  $this  getCapture(bool $value = null)
 *
 * @since  2.0
 */
class FileField extends TextField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected string $inputType = 'file';
}

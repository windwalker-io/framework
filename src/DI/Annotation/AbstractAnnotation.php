<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2020 .
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI\Annotation;

use Windwalker\Utilities\Classes\OptionAccessTrait;

/**
 * The AbstractAnnotation class.
 *
 * @since  3.5.19
 */
abstract class AbstractAnnotation
{
    use OptionAccessTrait;

    /**
     * AbstractAnnotation constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }
}

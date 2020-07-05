<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2020 .
 * @license    __LICENSE__
 */

namespace Windwalker\DI\Annotation;

use Windwalker\Utilities\Classes\OptionAccessTrait;

/**
 * The AbstractAnnotation class.
 *
 * @since  __DEPLOY_VERSION__
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

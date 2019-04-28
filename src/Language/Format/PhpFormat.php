<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Format;

/**
 * Class IniFormat
 *
 * @since 2.0
 */
class PhpFormat extends AbstractFormat
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'php';

    /**
     * parse
     *
     * @param array $array
     *
     * @return  array
     */
    public function parse($array)
    {
        return $this->toOneDimension($array);
    }
}

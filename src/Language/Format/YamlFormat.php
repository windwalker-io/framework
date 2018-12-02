<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Format;

use Symfony\Component\Yaml\Yaml;

/**
 * Class IniFormat
 *
 * @since 2.0
 */
class YamlFormat extends AbstractFormat
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'yaml';

    /**
     * parse
     *
     * @param string $string
     *
     * @return  string[]
     */
    public function parse($string)
    {
        $array = Yaml::parse($string);

        return $this->toOneDimension($array);
    }
}

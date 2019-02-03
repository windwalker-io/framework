<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Format;

use Symfony\Component\Yaml\Exception\ParseException;
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
     * @throws \ErrorException
     */
    public function parse($string)
    {
        try {
            $array = Yaml::parse($string);
        } catch (ParseException $e) {
            throw new \ErrorException(
                $e->getMessage(),
                $e->getCode(),
                E_ERROR,
                $e->getParsedFile(),
                $e->getParsedLine(),
                $e
            );
        }

        return $this->toOneDimension($array);
    }
}

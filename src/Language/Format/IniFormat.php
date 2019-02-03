<?php
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
class IniFormat extends AbstractFormat
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'ini';

    /**
     * parse
     *
     * @param string $string
     *
     * @return  array
     * @throws \ErrorException
     */
    public function parse($string)
    {
        try {
            return parse_ini_string($string);
        } catch (\Throwable $e) {
            preg_match('/on line (\d+)/', $e->getMessage(), $match);
            
            throw new \ErrorException(
                $e->getMessage(),
                $e->getCode(),
                E_ERROR,
                '',
                ($match[1] ?? 2) - 1,
                $e
            );
        }
    }
}

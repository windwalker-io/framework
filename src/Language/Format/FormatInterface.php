<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Format;

/**
 * Class FormatInterface
 *
 * @since 2.0
 */
interface FormatInterface
{
    /**
     * getName
     *
     * @return  string
     */
    public function getName();

    /**
     * parse
     *
     * @param string $string
     *
     * @return  array
     */
    public function parse($string);
}

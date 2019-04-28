<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Filter;

/**
 * Interface FilterInterface
 *
 * @since  2.0
 */
interface FilterInterface
{
    /**
     * clean
     *
     * @param string $text
     *
     * @return  mixed
     */
    public function clean($text);
}

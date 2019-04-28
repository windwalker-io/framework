<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language;

/**
 * Interface LanguageInterface
 */
interface LanguageInterface
{
    /**
     * translate
     *
     * @param string $key
     *
     * @return  string
     */
    public function translate($key);

    /**
     * plural
     *
     * @param string $string
     * @param int    $count
     *
     * @return  string
     */
    public function plural($string, $count = 1);

    /**
     * sprintf
     *
     * @param string $key
     *
     * @return  mixed
     */
    public function sprintf($key);

    /**
     * exists
     *
     * @param string $key
     *
     * @return  boolean
     */
    public function exists($key);
}

<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Filter\Cleaner;

/**
 * Interface FilterRuleInterface
 *
 * @since  2.0
 */
interface CleanerInterface
{
    /**
     * Method to clean text by rule.
     *
     * @param   string $source The source to be clean.
     *
     * @return  mixed  The cleaned value.
     */
    public function clean($source);
}

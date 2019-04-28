<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Filter\Test\Stub;

use Windwalker\Filter\Cleaner\CleanerInterface;

/**
 * The StubThorCleaner class.
 *
 * @since  2.0
 */
class StubThorCleaner implements CleanerInterface
{
    /**
     * Method to clean text by rule.
     *
     * @param   string $source The source to be clean.
     *
     * @return  mixed  The cleaned value.
     */
    public function clean($source)
    {
        return 'God';
    }
}

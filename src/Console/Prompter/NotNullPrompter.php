<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Console\Prompter;

/**
 * The NotNullPrompter class.
 *
 * @since  2.0
 */
class NotNullPrompter extends ValidatePrompter
{
    /**
     * Retry times.
     *
     * @var  int
     *
     * @since  2.0
     */
    protected $attempt = 3;

    /**
     * If this property set to true, application will be closed when validate fail.
     *
     * @var  boolean
     *
     * @since  2.0
     */
    protected $failToClose = false;

    /**
     * Returning message if valid fail.
     *
     * @var  string
     *
     * @since  2.0
     */
    protected $noValidMessage = '  Please enter something.';

    /**
     * Returning message if valid fail and close.
     *
     * @var  string
     *
     * @since  2.0
     */
    protected $closeMessage = '  No value and closed.';

    /**
     * Get callable handler.
     *
     * @return  callable  The validate callback.
     *
     * @since   2.0
     */
    public function getHandler()
    {
        return function ($value) {
            return (!is_null($value) && $value !== '');
        };
    }
}

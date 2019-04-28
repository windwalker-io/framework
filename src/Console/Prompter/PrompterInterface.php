<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Console\Prompter;

/**
 * Prompter Interface.
 *
 * Help us show dialog to ask use questions.
 *
 * @since  2.0
 */
interface PrompterInterface
{
    /**
     * Show prompt to ask user.
     *
     * @param   string $msg     Question.
     * @param   string $default Default value.
     *
     * @return  string  The value that use input.
     *
     * @since   2.0
     */
    public function ask($msg = '', $default = '');

    /**
     * Proxy to ask method.
     *
     * @param   string $default Default value.
     *
     * @return  string  The value that use input.
     *
     * @since   2.0
     */
    public function __invoke($default = '');
}

<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Console\Prompter;

/**
 * General text prompter.
 *
 * @since  2.0
 */
class TextPrompter extends AbstractPrompter
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
    public function ask($msg = '', $default = null)
    {
        $default = $default ?: $this->default;

        return $this->in($msg) ?: $default;
    }
}

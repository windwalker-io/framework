<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Console\Test\Prompter\Stubs;

use Windwalker\Console\Prompter\PasswordPrompter;

/**
 * Class Fake Password Prompter
 *
 * @since 2.0
 */
class FakePasswordPrompter extends PasswordPrompter
{
    /**
     * We dont't test bash because it break test process in IDE.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function findShell()
    {
        return false;
    }

    /**
     * canTestStty
     *
     * @return  boolean
     */
    public function canTestStty()
    {
        return $this->findStty();
    }
}

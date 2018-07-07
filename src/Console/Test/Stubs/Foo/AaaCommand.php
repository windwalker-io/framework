<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test\Stubs\Foo;

use Windwalker\Console\Command\Command;

/**
 * Class AaaCommand
 *
 * @since  2.0
 */
class AaaCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'aaa';

    /**
     * Initialise command.
     *
     * @return void
     *
     * @since  2.0
     */
    public function init()
    {
        $this->addCommand(new Aaa\BbbCommand());

        $this->addGlobalOption('a')
            ->alias('aaa')
            ->alias('a3')
            ->defaultValue(true)
            ->description('AAA options');
    }

    /**
     * doExecute
     *
     * @return int
     *
     * @since  2.0
     */
    public function doExecute()
    {
        echo 'Aaa';
    }
}

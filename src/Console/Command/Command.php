<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Console\Command;

/**
 * Base Command class.
 *
 * @since  2.0
 */
class Command extends AbstractCommand
{
    /**
     * Execute this command.
     *
     * @return int
     *
     * @since  2.0
     */
    protected function doExecute()
    {
        $this->io->setArguments([$this->name]);

        $output = $this->console->describeCommand($this);

        $this->out($output);

        return;
    }

    /**
     * Add an argument(sub command) setting. This method in Command use 'self' instead 'static' to make sure every sub
     * command add Command class as arguments.
     *
     * @param   string|AbstractCommand $command       The argument name or Console object.
     *                                                If we just send a string, the object will auto create.
     * @param   null                   $description   Console description.
     * @param   array                  $options       Console options.
     * @param   \Closure               $code          The closure to execute.
     *
     * @return  AbstractCommand  Return this object to support chaining.
     *
     * @since   2.0
     */
    public function addCommand($command, $description = null, $options = [], \Closure $code = null)
    {
        if (is_string($command) && class_exists($command) && is_subclass_of($command, __CLASS__)) {
            $command = new $command();
        }

        if (!($command instanceof AbstractCommand)) {
            $command = new self($command, $this->io, $this);
        }

        return parent::addCommand($command, $description, $options, $code);
    }
}

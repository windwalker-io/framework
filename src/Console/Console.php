<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Console;

use Windwalker\Console\Command\AbstractCommand;
use Windwalker\Console\Command\Command;
use Windwalker\Console\Command\HelpCommand;
use Windwalker\Console\Command\RootCommand;
use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;
use Windwalker\Structure\Structure;

/**
 * Class Console
 *
 * @since  2.0
 */
class Console extends AbstractConsole
{
    /**
     * The Console title.
     *
     * @var  string
     *
     * @since  3.0
     */
    protected $title = 'Windwalker Console';

    /**
     * Version of this application.
     *
     * @var string
     *
     * @since  2.0
     */
    protected $version = '1.0';

    /**
     * Console description.
     *
     * @var string
     *
     * @since  2.0
     */
    protected $description = '';

    /**
     * Property help.
     *
     * @var  string
     */
    protected $help = 'Welcome to Windwalker Console.';

    /**
     * A default command to run as application.
     *
     * @var  AbstractCommand
     *
     * @since  2.0
     */
    protected $rootCommand;

    /**
     * True to set this app auto exit.
     *
     * @var boolean
     *
     * @since  2.0
     */
    protected $autoExit;

    /**
     * Class init.
     *
     * @param   IOInterface $io     The Input and output handler.
     * @param   Structure   $config Application's config object.
     */
    public function __construct(IOInterface $io = null, Structure $config = null)
    {
        $io = $io ?: new IO();

        parent::__construct($io, $config);

        $this->registerRootCommand();
    }

    /**
     * Execute the application.
     *
     * @return  int  The Unix Console/Shell exit code.
     *
     * @throws \Exception
     * @since   2.0
     */
    public function execute()
    {
        $this->prepareExecute();

        // @event onBeforeExecute

        // Perform application routines.
        $exitCode = $this->doExecute();

        // @event onAfterExecute

        return $this->postExecute($exitCode);
    }

    /**
     * Method to run the application routines.
     *
     * @param   AbstractCommand $command The Command object to execute, default will be rootCommand.
     *
     * @return  int  The Unix Console/Shell exit code.
     *
     * @see     http://tldp.org/LDP/abs/html/exitcodes.html
     *
     * @since   2.0
     * @throws  \LogicException
     * @throws  \Exception
     */
    public function doExecute(AbstractCommand $command = null)
    {
        $command = $command ?: $this->getRootCommand();

        if (!$command->getHandler() && !count($this->io->getArguments())) {
            $this->set('show_help', true);
        }

        $error = false;

        try {
            /*
             * Exit code is the Linux/Unix command/shell return code to see
             * whether this script executed is successful or not.
             *
             * @see  http://tldp.org/LDP/abs/html/exitcodes.html
             */
            $exitCode = $command->execute();
        } catch (\Exception $e) {
            $command->renderException($e);

            $exitCode = $e->getCode();
            $error = true;
        } catch (\Throwable $t) {
            $command->renderException(
                $e = new \ErrorException(
                    $t->getMessage(),
                    $t->getCode(),
                    E_ERROR,
                    $t->getFile(),
                    $t->getLine(),
                    $t
                )
            );

            $exitCode = $t->getCode();
            $error = true;
        }

        if ($exitCode === true) {
            $exitCode = 0;
        } elseif (($error && $exitCode === 0) || $exitCode === false) {
            $exitCode = 1;
        } elseif ($exitCode > 255 || (int) $exitCode === -1) {
            $exitCode = 255;
        }

        if ($this->autoExit) {
            exit($exitCode);
        }

        return $exitCode;
    }

    /**
     * executeByPath
     *
     * @param string      $arguments
     * @param array       $options
     * @param IOInterface $io
     *
     * @return int
     * @throws \Exception
     */
    public function executeByPath($arguments, array $options = [], IOInterface $io = null)
    {
        $io = $io ?: clone $this->io;

        // Path
        if (is_string($arguments)) {
            $arguments = str_replace('/', ' ', $arguments);
            $arguments = array_filter(explode(' ', $arguments), 'strlen');
        }

        $io->setArguments($arguments);

        // Options
        foreach ($options as $key => $value) {
            $io->setOption($key, $value);
        }

        return $this->getRootCommand()->setIO($io)->execute();
    }

    /**
     * handleException
     *
     * @param \Throwable $e
     *
     * @return  void
     *
     * @since  3.5.2
     */
    public function handleException(\Throwable $e): void
    {
        echo $e;
    }

    /**
     * Register default command.
     *
     * @return  Console  Return this object to support chaining.
     *
     * @since  2.0
     */
    public function registerRootCommand()
    {
        if ($this->rootCommand) {
            return $this;
        }

        $this->rootCommand = new RootCommand(null, $this->io);

        $this->rootCommand->setApplication($this);

        $this->description ? $this->rootCommand->description($this->description) : null;
        $this->help ? $this->rootCommand->help($this->help) : null;

        return $this;
    }

    /**
     * Register a new Console.
     *
     * @param   string $name The command name.
     *
     * @return  AbstractCommand The created commend.
     *
     * @since  2.0
     */
    public function register($name)
    {
        return $this->addCommand(new Command($name, $this->io));
    }

    /**
     * Add a new command object.
     *
     * If a command with the same name already exists, it will be overridden.
     *
     * @param   AbstractCommand|string $command A Console object.
     *
     * @return  AbstractCommand  The registered command.
     *
     * @since  2.0
     */
    public function addCommand($command)
    {
        $this->getRootCommand()->addCommand($command);

        return $command;
    }

    /**
     * Get command by path.
     *
     * Example: getCommand('foo/bar/baz');
     *
     * @param   string $path The path or name of child.
     *
     * @return  AbstractCommand
     *
     * @since  2.0
     */
    public function getCommand($path)
    {
        return $this->getRootCommand()->getChild($path);
    }

    /**
     * Method to perform basic garbage collection and memory management in the sense of clearing the
     * stat cache.  We will probably call this method pretty regularly in our main loop.
     *
     * @return static
     */
    public function gc()
    {
        // Perform generic garbage collection.
        gc_collect_cycles();

        // Clear the stat cache so it doesn't blow up memory.
        clearstatcache();

        return $this;
    }

    /**
     * Activates the circular reference collector
     *
     * @return  static
     *
     * @since  3.3
     */
    public function gcEnable()
    {
        gc_enable();

        return $this;
    }

    /**
     * Disable the circular reference collector
     *
     * @return  static
     *
     * @since  3.3
     */
    public function gcDisable()
    {
        gc_disable();

        return $this;
    }

    /**
     * Sets whether to automatically exit after a command execution or not.
     *
     * @param   boolean $boolean Whether to automatically exit after a command execution or not.
     *
     * @return  Console  Return this object to support chaining.
     *
     * @since  2.0
     */
    public function setAutoExit($boolean)
    {
        $this->autoExit = (bool) $boolean;

        return $this;
    }

    /**
     * Get the default command.
     *
     * @return AbstractCommand  Default command.
     *
     * @since  2.0
     */
    public function getRootCommand()
    {
        return $this->rootCommand;
    }

    /**
     * Get version.
     *
     * @return string Application version.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version.
     *
     * @param   string $version Set version of this application.
     *
     * @return  Console  Return this object to support chaining.
     *
     * @since  2.0
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string  Application description.
     *
     * @since  2.0
     */
    public function getDescription()
    {
        return $this->getRootCommand()->getDescription();
    }

    /**
     * Set description.
     *
     * @param   string $description description of this application.
     *
     * @return  Console  Return this object to support chaining.
     *
     * @since  2.0
     */
    public function setDescription($description)
    {
        $this->getRootCommand()->description($description);

        return $this;
    }

    /**
     * Set execute code to default command.
     *
     * @param   callable $closure Console execute code.
     *
     * @return  Console  Return this object to support chaining.
     *
     * @since  2.0
     */
    public function setHandler($closure)
    {
        $this->getRootCommand()->handler($closure);

        return $this;
    }

    /**
     * setUsage
     *
     * @param string $usage
     *
     * @return  $this
     */
    public function setUsage($usage)
    {
        $this->getRootCommand()->usage($usage);

        return $this;
    }

    /**
     * setHelp
     *
     * @param string $help
     *
     * @return  $this
     */
    public function setHelp($help)
    {
        $this->getRootCommand()->help($help);

        return $this;
    }

    /**
     * Method to get property Help
     *
     * @return  string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Method to get property Title
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Method to set property title
     *
     * @param   string $title
     *
     * @return  static  Return self to support chaining.
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}

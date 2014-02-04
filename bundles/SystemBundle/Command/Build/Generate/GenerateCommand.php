<?php
/**
 * @package     Joomla.Cli
 * @subpackage  JConsole
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace SystemBundle\Command\Build\Generate;

use Windwalker\Console\Command\Command;

/**
 * Class GenerateCommand
 *
 * @package     Joomla.Cli
 * @subpackage  JConsole
 *
 * @since       3.2
 */
class GenerateCommand extends Command
{
	/**
	 * An enabled flag.
	 *
	 * @var bool
	 */
	public static $isEnabled = true;

	/**
	 * Console(Argument) name.
	 *
	 * @var  string
	 */
	protected $name = 'gen-command';

	/**
	 * The command description.
	 *
	 * @var  string
	 */
	protected $description = 'Generate a command class.';

	/**
	 * The usage to tell user how to use this command.
	 *
	 * @var string
	 */
	protected $usage = 'example <command> [option]';

	/**
	 * Template to generate command.
	 *
	 * @var string
	 */
	protected $template = <<<TMPL
<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace {{NAMESPACE}};

use Windwalker\Console\Command\Command;

defined('WINDWALKER') or die;

/**
 * Class {{CLASS}}
 *
 * @since  2.0
 */
class {{CLASS}}Command extends Command
{
	/**
	 * An enabled flag.
	 *
	 * @var bool
	 */
	public static \$isEnabled = true;

	/**
	 * Console(Argument) name.
	 *
	 * @var  string
	 */
	protected \$name = '{{NAME}}';

	/**
	 * The command description.
	 *
	 * @var  string
	 */
	protected \$description = '{{DESCRIPTION}}';

	/**
	 * The usage to tell user how to use this command.
	 *
	 * @var string
	 */
	protected \$usage = '{{NAME}} <cmd><command></cmd> <option>[option]</option>';

	/**
	 * Configure command information.
	 *
	 * @return void
	 */
	public function configure()
	{
		// \$this->addArgument();

		parent::configure();
	}

	/**
	 * Execute this command.
	 *
	 * @return int|void
	 */
	protected function doExecute()
	{
		return parent::doExecute();
	}
}

TMPL;

	/**
	 * Configure command information.
	 *
	 * @return void
	 */
	public function configure()
	{
		$this->addOption(
			array('d', 'description'),
			null,
			'Command description'
		);

		parent::configure();
	}

	/**
	 * Execute this command.
	 *
	 * @return int|void
	 */
	protected function doExecute()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		@$name       = $this->input->args[0] ? : exit("Please enter command name");
		@$namespace  = $this->input->args[1] ? : exit("Please enter command namespace");
		$description = $this->getOption('d') ? : $name;

		if (!$name || !$namespace)
		{
			$this->out('Need name & namespace.');

			return;
		}

		// Regularize Namespace
		$namespace = str_replace(array('/', '\\'), ' ', $namespace);

		$namespace = ucwords($namespace);

		$namespace = str_replace(' ', '\\', $namespace);

		$namespace = explode('\\', $namespace);

		if ($namespace[0] == 'Command')
		{
			array_shift($namespace);
		}

		$class = $namespace;

		$class = array_pop($class);

		$namespace = implode('\\', $namespace);

		$replace = array(
			'{{NAME}}'      => $name,
			'{{NAMESPACE}}' => $namespace,
			'{{CLASS}}'     => $class,
			'{{DESCRIPTION}}' => $description
		);

		$content = strtr($this->template, $replace);

		$file = WINDWALKER . '/bundles/' . $namespace . '/' . $class . 'Command.php';

		$file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $file);

		if (!\JFile::write($file, $content))
		{
			$this->out()->out('Failure when writing file.');

			return false;
		}

		$this->out('File generated: ' . $file);

		return;
	}
}

<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Descriptor\Text;

use Windwalker\Console\Console;
use Windwalker\Console\Command\AbstractCommand;
use Windwalker\Console\Descriptor\AbstractDescriptorHelper;

/**
 * A descriptor helper to get different descriptor and render it.
 *
 * @since  {DEPLOY_VERSION}
 */
class TextDescriptorHelper extends AbstractDescriptorHelper
{
	/**
	 * Template of console.
	 *
	 * @var string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $template = <<<EOF

<comment>%s</comment> - version: %s
------------------------------------------------------------

[<comment>%s</comment> Help]

%s
Usage:
  %s
{OPTIONS}
{COMMANDS}

%s
EOF;

	/**
	 * Describe a command detail.
	 *
	 * @param   AbstractCommand  $command  The command to described.
	 *
	 * @return  string  Return the described text.
	 *
	 * @throws  \RuntimeException
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function describe(AbstractCommand $command)
	{
		// Describe Options
		$options          = $command->getAllOptions();
		$optionDescriptor = $this->getOptionDescriptor();

		foreach ($options as $option)
		{
			$optionDescriptor->addItem($option);
		}

		$render['option'] = count($options) ? "\n\nOptions:\n\n" . $optionDescriptor->render() : '';

		// Describe Commands
		$commands          = $command->getChildren();
		$commandDescriptor = $this->getCommandDescriptor();

		foreach ($commands as $cmd)
		{
			$commandDescriptor->addItem($cmd);
		}

		$render['command'] = count($commands) ? "\nAvailable commands:\n\n" . $commandDescriptor->render() : '';

		// Render Help template
		/** @var Console $console */
		$console = $command->getApplication();

		if (!($console instanceof Console))
		{
			throw new \RuntimeException(sprintf('Help descriptor need Console object in %s command.', get_class($command)));
		}

		$consoleName = $console->getName();
		$version     = $console->getVersion();

		$commandName = $command->getName();
		$description = $command->getDescription();
		$usage       = $command->getUsage();
		$help        = $command->getHelp();

		// Clean line indent of description
		$description = explode("\n", $description);

		foreach ($description as &$line)
		{
			$line = trim($line);
		}

		$description = implode("\n", $description);
		$description = $description ? $description . "\n" : '';

		$template = sprintf(
			$this->template,
			$consoleName,
			$version,
			$commandName,
			$description,
			$usage,
			$help
		);

		return str_replace(
			array('{OPTIONS}', '{COMMANDS}'),
			$render,
			$template
		);
	}
}

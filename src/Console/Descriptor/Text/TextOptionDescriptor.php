<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Console\Descriptor\Text;

use Windwalker\Console\Descriptor\AbstractDescriptor;
use Windwalker\Console\Option\Option;

/**
 * Class Option AbstractDescriptor
 *
 * @since    2.0
 */
class TextOptionDescriptor extends AbstractDescriptor
{
    /**
     * The max length of command.
     *
     * @var int
     *
     * @since  2.0
     */
    protected $maxLength = 0;

    /**
     * Offset that between every commands and their descriptions.
     *
     * @var int
     *
     * @since  2.0
     */
    protected $offsetAfterCommand = 4;

    /**
     * Option description template.
     *
     * @var string
     *
     * @since  2.0
     */
    protected $template = <<<EOF
  <info>%-{WIDTH}s</info>%s
EOF;

    /**
     * Render an item description.
     *
     * @param   mixed $option The item to br described.
     *
     * @throws  \InvalidArgumentException
     * @return  string  Rendered description.
     *
     * @since   2.0
     */
    protected function renderItem($option)
    {
        if (!($option instanceof Option)) {
            throw new \InvalidArgumentException('Command descriptor need Command object to describe it.');
        }

        /** @var Option $command */
        $name = $option->getName();
        $description = $option->getDescription() ?: 'No description';
        $aliases = $option->getAliases();

        // Merge aliases
        array_unshift($aliases, $name);

        foreach ($aliases as &$alias) {
            $alias = strlen($alias) > 1 ? '--' . $alias : '-' . $alias;
        }

        $name = implode(' | ', $aliases);

        $template = str_replace('{WIDTH}', (string) ($this->maxLength + $this->offsetAfterCommand), $this->template);

        // Sets the body indent.
        $body = [];

        $description = explode("\n", $description);

        $line1 = array_shift($description);
        $body[] = sprintf($template, $name, $line1);

        foreach ($description as $line) {
            $line = trim($line);
            $line = sprintf($template, '', $line);
            $body[] = $line;
        }

        return implode("\n", $body);
    }

    /**
     * Render all items description.
     *
     * @return  string
     *
     * @since  2.0
     */
    public function render()
    {
        // Count the max command length as column width.
        foreach ($this->items as $item) {
            /** @var $item Option */
            $name = $item->getName();
            $aliases = $item->getAliases();

            // Merge aliases
            array_unshift($aliases, $name);

            foreach ($aliases as &$alias) {
                $alias = strlen($alias) > 1 ? '--' . $alias : '-' . $alias;
            }

            $name = implode(' | ', $aliases);

            $length = strlen($name);

            if ($length > $this->maxLength) {
                $this->maxLength = $length;
            }
        }

        return parent::render();
    }
}

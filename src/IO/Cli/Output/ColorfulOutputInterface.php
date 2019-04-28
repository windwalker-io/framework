<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\IO\Cli\Output;

use Windwalker\IO\Cli\Color\ColorProcessorInterface;

/**
 * The ColorfulOutputInterface class.
 *
 * @since  2.0
 */
interface ColorfulOutputInterface
{
    /**
     * Set a processor
     *
     * @param   ColorProcessorInterface $processor The output processor.
     *
     * @return  CliOutput  Instance of $this to allow chaining.
     *
     * @since   2.0
     */
    public function setProcessor(ColorProcessorInterface $processor);

    /**
     * Get a processor
     *
     * @return  ColorProcessorInterface
     *
     * @since   2.0
     * @throws  \RuntimeException
     */
    public function getProcessor();
}

<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\IO\Cli\Color;

/**
 * Class ProcessorInterface.
 *
 * @since  2.0
 */
interface ColorProcessorInterface
{
    /**
     * Process the provided output into a string.
     *
     * @param   string $output
     *
     * @return  string
     *
     * @since   2.0
     */
    public function process($output);

    /**
     * Add a style.
     *
     * @param   string     $name  The style name.
     * @param   ColorStyle $style The color style.
     *
     * @return  ColorProcessor  Instance of $this to allow chaining.
     *
     * @since   2.0
     */
    public function addStyle($name, ColorStyle $style);

    /**
     * Method to set property noColors
     *
     * @param   boolean $noColors
     *
     * @return  static  Return self to support chaining.
     */
    public function setNoColors($noColors);
}

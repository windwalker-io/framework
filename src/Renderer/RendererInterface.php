<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Renderer;

/**
 * Interface RendererInterface
 */
interface RendererInterface
{
    /**
     * render
     *
     * @param string $file
     * @param array  $data
     *
     * @return  string
     */
    public function render($file, $data = []);

    /**
     * Method to escape output.
     *
     * @param   string $output The output to escape.
     *
     * @return  string  The escaped output.
     */
    public function escape($output);
}


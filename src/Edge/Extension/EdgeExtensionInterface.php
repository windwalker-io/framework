<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Extension;

/**
 * The EdgeExtensionInterface class.
 *
 * @since  3.0
 */
interface EdgeExtensionInterface
{
    /**
     * getName
     *
     * @return  string
     */
    public function getName();

    /**
     * getDirectives
     *
     * @return  callable[]
     */
    public function getDirectives();

    /**
     * getGlobals
     *
     * @return  array
     */
    public function getGlobals();

    /**
     * getParsers
     *
     * @return  callable[]
     */
    public function getParsers();
}

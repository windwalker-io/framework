<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Console\Descriptor;

/**
 * Interface of Descriptor.
 *
 * @since  2.0
 */
interface DescriptorInterface
{
    /**
     * Add an item to describe.
     *
     * @param   mixed $item The item you want to describe.
     *
     * @return  DescriptorInterface  Return this object to support chaining.
     *
     * @since  2.0
     */
    public function addItem($item);

    /**
     * Render all items description.
     *
     * @return  string  Rendered result.
     *
     * @since   2.0
     */
    public function render();
}

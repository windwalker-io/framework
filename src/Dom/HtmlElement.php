<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Dom;

use Windwalker\Dom\Builder\HtmlBuilder;

/**
 * The Html element object.
 *
 * @since 2.0
 */
class HtmlElement extends DomElement
{
    /**
     * toString
     *
     * @param boolean $forcePair
     *
     * @return  string
     */
    public function toString($forcePair = false)
    {
        return HtmlBuilder::create($this->name, $this->content, $this->attribs, $forcePair);
    }
}

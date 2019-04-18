<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Field;

/**
 * The SearchField class.
 *
 * @since  2.0
 */
class SearchField extends TextField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'search';

    /**
     * prepare
     *
     * @param array $attrs
     *
     * @return  array|void
     */
    public function prepare(&$attrs)
    {
        parent::prepare($attrs);
    }
}

<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Field;

use Windwalker\Html\Select\RadioList;

/**
 * The RadioField class.
 *
 * @since  2.0
 */
class RadioField extends ListField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'radio';

    /**
     * buildInput
     *
     * @param array $attrs
     *
     * @return  mixed|void
     */
    public function buildInput($attrs)
    {
        $options = $this->getOptions();

        foreach ($options as $option) {
            $option->setAttribute('name', $this->getFieldName());
        }

        return new RadioList($this->getFieldName(), $options, $attrs, $this->getValue());
    }
}

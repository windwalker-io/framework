<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Html\Select;

use Windwalker\Html\Option;

/**
 * The CheckboxList class.
 *
 * @since  2.0
 */
class CheckboxList extends AbstractInputList
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'checkbox';

    /**
     * prepareOptions
     *
     * @return  void
     */
    public function prepareOptions()
    {
        parent::prepareOptions();

        // Prepare array name
        foreach ($this->content as $key => $option) {
            $option[0]->setAttribute('name', $option[0]->getAttribute('name') . '[]');
        }
    }

    /**
     * isChecked
     *
     * @param  Option $option
     *
     * @return  bool
     */
    protected function isChecked(Option $option)
    {
        return in_array($option->getValue(), (array) $this->getChecked());
    }
}

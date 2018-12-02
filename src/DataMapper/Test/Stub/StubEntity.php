<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\DataMapper\Test\Stub;

use Windwalker\DataMapper\Entity\Entity;

/**
 * The StubEntity class.
 *
 * @since  3.1
 */
class StubEntity extends Entity
{
    /**
     * getFooBarValue
     *
     * @return  string
     */
    protected function getFooBarValue()
    {
        return 'foo_bar';
    }

    /**
     * setFooBarValue
     *
     * @param $value
     */
    protected function setFlowerSakuraValue($value)
    {
        $this->addField('flower_sakura');
        $this->data['flower_sakura'] = $value . '_bar';
    }
}

<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Session\Bag;

/**
 * The ArrayBag class.
 *
 * @since  2.0
 */
class ArrayBag extends SessionBag implements SessionBagInterface
{
    /**
     * Property data.
     *
     * @var  array
     */
    protected $data = [];

    /**
     * setData
     *
     * @param array $data
     *
     * @return  void
     */
    public function setData(array &$data)
    {
        $this->data = [];

        return;
    }
}

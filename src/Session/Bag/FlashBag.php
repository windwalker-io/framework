<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Session\Bag;

/**
 * Class FlashBag
 *
 * @since 2.0
 */
class FlashBag extends SessionBag implements FlashBagInterface
{
    /**
     * add
     *
     * @param string $msg
     * @param string $type
     *
     * @return  $this
     */
    public function add($msg, $type = 'info')
    {
        if (!isset($this->data[$type]) || !is_array($this->data[$type])) {
            $this->data[$type] = [];
        }

        foreach ((array) $msg as $msg) {
            $this->data[$type][] = $msg;
        }

        return $this;
    }

    /**
     * Take all and clean.
     *
     * @return  array
     */
    public function takeAll()
    {
        $all = $this->all();

        $this->clear();

        return $all;
    }

    /**
     * getType
     *
     * @param string $type
     *
     * @return  array
     */
    public function getType($type)
    {
        return isset($this->data[$type]) ? $this->data[$type] : [];
    }
}

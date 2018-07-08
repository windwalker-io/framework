<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Bag;

/**
 * Class FlashBag
 *
 * @since 2.0
 */
class AutoExpiredFlashBag extends FlashBag
{
    /**
     * Property data.
     *
     * @var  array
     */
    protected $data = [
        'last' => [],
        'current' => [],
    ];

    /**
     * setData
     *
     * @param array $data
     *
     * @return  void
     */
    public function setData(array &$data)
    {
        $this->data = &$data;

        if (!isset($this->data['current'])) {
            $data['current'] = [];
        }

        $this->data['last'] = isset($this->data['current']) ? $this->data['current'] : [];

        $this->data['current'] = [];
    }

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
        if (!isset($this->data['current'][$type]) || !is_array($this->data['current'][$type])) {
            $this->data['current'][$type] = [];
        }

        foreach ((array) $msg as $msg) {
            $this->data['current'][$type][] = $msg;
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
     * all
     *
     * @return  array
     */
    public function all()
    {
        return $this->data['last'];
    }

    /**
     * clean
     *
     * @return  $this
     */
    public function clear()
    {
        $this->data['last'] = [];

        return $this;
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
        return isset($this->data['last'][$type]) ? $this->data['last'][$type] : [];
    }
}

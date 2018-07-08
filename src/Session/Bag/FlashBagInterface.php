<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Bag;

/**
 * Interface FlasgBagInterface
 */
interface FlashBagInterface extends SessionBagInterface
{
    /**
     * add
     *
     * @param string $msg
     * @param string $type
     *
     * @return  $this
     */
    public function add($msg, $type = 'info');

    /**
     * Take all and clean.
     *
     * @return  array
     */
    public function takeAll();

    /**
     * getType
     *
     * @param string $type
     *
     * @return  array
     */
    public function getType($type);
}

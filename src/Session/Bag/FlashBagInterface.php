<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
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

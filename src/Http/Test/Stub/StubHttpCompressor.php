<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Test\Stub;

use Windwalker\Http\Output\HttpCompressor;

/**
 * The StubHttpCompressor class.
 *
 * @since  3.0
 */
class StubHttpCompressor extends HttpCompressor
{
    /**
     * checkConnectionAlive
     *
     * @return  bool
     */
    public function checkConnectionAlive()
    {
        return true;
    }

    /**
     * checkHeadersSent
     *
     * @return  bool
     */
    public function checkHeadersSent()
    {
        return false;
    }
}

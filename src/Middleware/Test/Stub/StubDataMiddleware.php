<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Middleware\Test\Stub;

use Windwalker\Middleware\AbstractMiddleware;

/**
 * The StubDataMiddleware class.
 *
 * @since  3.0
 */
class StubDataMiddleware extends AbstractMiddleware
{
    /**
     * Call next middleware.
     *
     * @param  \stdClass $data
     *
     * @return mixed
     */
    public function execute($data = null)
    {
        $r = ">>> {$data->title}\n";

        $r .= $this->next->execute($data);

        $r .= "<<< {$data->title}\n";

        return $r;
    }
}

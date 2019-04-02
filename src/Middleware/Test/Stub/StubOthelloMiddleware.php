<?php
/**
 * Part of windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Middleware\Test\Stub;

use Windwalker\Middleware\AbstractMiddleware;

/**
 * The StubOthelloMiddleware class.
 *
 * @since  2.0
 */
class StubOthelloMiddleware extends AbstractMiddleware
{
    /**
     * Call next middleware.
     *
     * @param null $data
     *
     * @return mixed
     */
    public function execute($data = null)
    {
        $r = ">>> Othello\n";

        $r .= $this->next->execute($data);

        $r .= "<<< Othello\n";

        return $r;
    }
}

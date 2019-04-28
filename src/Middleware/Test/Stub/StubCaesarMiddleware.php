<?php declare(strict_types=1);
/**
 * Part of windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Middleware\Test\Stub;

use Windwalker\Middleware\AbstractMiddleware;

/**
 * The StubCaesarMiddleware class.
 *
 * @since  2.0
 */
class StubCaesarMiddleware extends AbstractMiddleware
{
    /**
     * Call next middleware.
     *
     * @param  array $data
     *
     * @return mixed
     */
    public function execute($data = null)
    {
        $r = ">>> Caesar\n";

        $r .= $this->next->execute($data);

        $r .= "<<< Caesar\n";

        return $r;
    }
}

<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Authentication\Method;

use Windwalker\Authentication\Credential;

/**
 * Interface MethodInterface
 *
 * @since  2.0
 */
interface MethodInterface
{
    /**
     * authenticate
     *
     * @param Credential $credential
     *
     * @return  integer
     */
    public function authenticate(Credential $credential);

    /**
     * getResult
     *
     * @return  integer
     */
    public function getStatus();
}

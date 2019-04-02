<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Authentication\Test\Mock;

use Windwalker\Authentication\Authentication;
use Windwalker\Authentication\Credential;
use Windwalker\Authentication\Method\AbstractMethod;

/**
 * The MockMethod class.
 *
 * @since  2.0
 */
class MockMethod extends AbstractMethod
{
    /**
     * authenticate
     *
     * @param Credential $credential
     *
     * @return  integer
     */
    public function authenticate(Credential $credential)
    {
        if ($credential->username === 'flower') {
            if ($credential->password === '1234') {
                $this->status = Authentication::SUCCESS;

                return true;
            }

            $this->status = Authentication::INVALID_CREDENTIAL;

            return false;
        }

        $this->status = Authentication::USER_NOT_FOUND;

        return false;
    }
}

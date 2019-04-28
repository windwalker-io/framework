<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Authorisation;

/**
 * The PolicyInterface class.
 *
 * @since  3.0
 */
interface PolicyInterface
{
    /**
     * authorise
     *
     * @param   mixed $user
     * @param   mixed $data
     *
     * @return  boolean
     */
    public function authorise($user, $data = null);
}

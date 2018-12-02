<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Authorisation;

/**
 * Interface PolicyInterface
 *
 * @since  3.0
 */
interface PolicyProviderInterface
{
    /**
     * register
     *
     * @param AuthorisationInterface $authorisation
     *
     * @return  void
     */
    public function register(AuthorisationInterface $authorisation);
}

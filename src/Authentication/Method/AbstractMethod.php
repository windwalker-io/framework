<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Authentication\Method;

/**
 * The AbstractMethod class.
 *
 * @since  2.0
 */
abstract class AbstractMethod implements MethodInterface
{
    /**
     * Property status.
     *
     * @var integer
     */
    protected $status;

    /**
     * getStatus
     *
     * @return  integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}

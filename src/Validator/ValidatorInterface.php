<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator;

/**
 * The ValidatorInterface class.
 *
 * @since  2.0
 */
interface ValidatorInterface
{
    /**
     * Test this value.
     *
     * @param mixed $value
     *
     * @return  boolean
     */
    public function validate($value);

    /**
     * Get error message.
     *
     * @return  string
     */
    public function getError();
}

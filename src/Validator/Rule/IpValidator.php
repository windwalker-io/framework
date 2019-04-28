<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Rule;

/**
 * The IpValidator class.
 *
 * @since  2.0
 */
class IpValidator extends RegexValidator
{
    /**
     * The regular expression to use in testing value.
     *
     * @var  string
     */
    protected $regex = '^((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))*$';

    /**
     * The regular expression modifiers to use when testing a value.
     *
     * @var  string
     */
    protected $modifiers = 'i';
}

<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Validator\Rule;

/**
 * The PhoneValidator class.
 *
 * @since  2.0
 */
class PhoneValidator extends RegexValidator
{
    /**
     * The regular expression to use in testing value.
     *
     * The phone should be: 0-123-456-7890 / 01234567890 / 123-456-7890 / 1234567890
     *
     * @var  string
     */
    protected $regex = '^((([0-9]{1})*[- .(]*([0-9]{3})[- .)]*[0-9]{3}[- .]*[0-9]{4})+)*$';

    /**
     * The regular expression modifiers to use when testing a value.
     *
     * @var  string
     */
    protected $modifiers = 'i';
}

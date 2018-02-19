<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Validator\Rule;

/**
 * The EmailValidator class.
 *
 * @since  2.0
 */
class EmailValidator extends RegexValidator
{
    /**
     * The regular expression to use in testing a form field value.
     *
     * @var  string
     * @see  http://www.w3.org/TR/html-markup/input.email.html
     */
    protected $regex = '^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$';
}

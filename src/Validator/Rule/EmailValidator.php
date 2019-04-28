<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
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

<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Rule;

/**
 * The UrlValidator class.
 *
 * @since  2.0
 */
class UrlValidator extends RegexValidator
{
    /**
     * The regular expression to validate url.
     *
     * @note Origin regular exp is from: http://www.w3schools.com/php/php_form_url_email.asp
     *
     * @var  string
     */
    protected $regex = '\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]';

    /**
     * The regular expression modifiers to use when testing a value.
     *
     * @var  string
     */
    protected $modifiers = 'i';
}

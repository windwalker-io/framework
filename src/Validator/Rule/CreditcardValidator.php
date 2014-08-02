<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Validator\Rule;

/**
 * The UrlValidator class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class CreditcardValidator extends RegexValidator
{
	/**
	 * The regular expression to use in testing value.
	 *
	 * @note Origin regular exp is from: http://www.virtuosimedia.com/dev/php/37-tested-php-perl-and-javascript-regular-expressions
	 *
	 * @var  string
	 */
	protected $regex = '^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|622((12[6-9]|1[3-9][0-9])|([2-8][0-9][0-9])|(9(([0-1][0-9])|(2[0-5]))))[0-9]{10}|64[4-9][0-9]{13}|65[0-9]{14}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})*$';

	/**
	 * The regular expression modifiers to use when testing a value.
	 *
	 * @var  string
	 */
	protected $modifiers = 'i';
}

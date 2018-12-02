<?php
/**
 * Part of windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Test\TestCase;

use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The AbstractBaseTestCase class.
 *
 * @since       2.0
 *
 * @deprecated  Directly use BaseAssertionTrait.
 */
abstract class AbstractBaseTestCase extends \PHPUnit\Framework\TestCase
{
    use BaseAssertionTrait;
}

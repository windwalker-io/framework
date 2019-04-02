<?php
/**
 * @copyright  Copyright (C) 2019 LYRASOFT Source Matters, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Utilities\Test;

/**
 * Tests for the global PHP methods.
 *
 * @since  2.0
 */
class FunctionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the with method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testWith()
    {
        $object = with(new \stdClass());

        $this->assertEquals(
            new \stdClass(),
            $object
        );
    }
}

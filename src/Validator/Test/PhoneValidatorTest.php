<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\PhoneValidator;

/**
 * Test class of PhoneValidator
 *
 * @since 2.0
 */
class PhoneValidatorTest extends AbstractValidateTestCase
{
    /**
     * Test instance.
     *
     * @var PhoneValidator
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new PhoneValidator();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * testCase
     *
     * @return  array
     */
    public function validateCase()
    {
        return [
            [
                'case1',
                '0225647186',
                true,
            ],
            [
                'case2',
                '0-800-456-7890',
                true,
            ],
            [
                '555-365-4785',
                '',
                true,
            ],
            [
                'case4',
                '4684521546874',
                true,
            ],
        ];
    }
}

<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\IpValidator;

/**
 * Test class of IpValidator
 *
 * @since 2.0
 */
class IpValidatorTest extends AbstractValidateTestCase
{
    /**
     * Test instance.
     *
     * @var IpValidator
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
        $this->instance = new IpValidator();
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
                '123.45.67.89',
                true,
            ],
            [
                'case2',
                '127.0.0.1',
                true,
            ],
            [
                'case3',
                '192.168.140.155',
                true,
            ],
            [
                'case4',
                '654.321.123.456',
                false,
            ],
            [
                'case5',
                'http://abc.com',
                false,
            ],
        ];
    }
}

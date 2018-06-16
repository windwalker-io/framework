<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\UrlValidator;

/**
 * Test class of UrlValidator
 *
 * @since 2.0
 */
class UrlValidatorTest extends AbstractValidateTestCase
{
    /**
     * Test instance.
     *
     * @var UrlValidator
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->instance = new UrlValidator();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
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
                'http://foo.com',
                true,
            ],
            [
                'case2',
                'https://windwalker.com/flower/sakura',
                true,
            ],
            [
                'case3',
                'ftp://windwalker.com/flower/sakura/?a=b&c=d',
                true,
            ],
            [
                'case4',
                'foo.com',
                false,
            ],
            [
                'case5',
                'sakura.com/wind/walker',
                false,
            ],
            [
                'case6',
                '25ihj9380t534895',
                false,
            ],
        ];
    }
}

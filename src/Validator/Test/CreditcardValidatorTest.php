<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\CreditcardValidator;

/**
 * Test class of CreditcardValidator
 *
 * @since 2.0
 */
class CreditcardValidatorTest extends AbstractValidateTestCase
{
    /**
     * Test instance.
     *
     * @var CreditcardValidator
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
        $this->instance = new CreditcardValidator();
    }

    /**
     * testCase
     *
     * These fake numbers were generated from: http://www.getcreditcardnumbers.com/
     *
     * @return  array
     */
    public function validateCase()
    {
        return [
            [
                'American Express',
                '378515770182856',
                true,
            ],
            [
                'Visa',
                '4509782003875110',
                true,
            ],
            [
                'Discover',
                '6011483235207596',
                true,
            ],
            [
                'MasterCard',
                '5110555858557787',
                true,
            ],
            [
                'Diners Club',
                '30333189575193',
                true,
            ],
            [
                'Not valid',
                '1234567887654321',
                false,
            ],
        ];
    }
}

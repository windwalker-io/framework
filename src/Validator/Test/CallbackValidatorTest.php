<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Test\Rule;

use PHPUnit\Framework\TestCase;
use Windwalker\Validator\Rule\CallbackValidator;

/**
 * Test class of \Windwalker\Validator\Rule\CallbackValidator
 *
 * @since 3.2
 */
class CallbackValidatorTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var CallbackValidator
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
        $this->instance = new CallbackValidator();
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
     * Method to test __construct().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\Rule\CallbackValidator::__construct
     */
    public function testConstruct()
    {
        $v = new CallbackValidator(
            function ($value) {
                return is_array($value);
            }
        );

        self::assertTrue($v->validate([]));
        self::assertFalse($v->validate('Foo'));
        self::assertInstanceOf(\Closure::class, $v->getHandler());
    }

    /**
     * Method to test getHandler().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\Rule\CallbackValidator::getHandler
     * @covers \Windwalker\Validator\Rule\CallbackValidator::setHandler
     */
    public function testAccessHandler()
    {
        $this->instance->setHandler(
            function ($value) {
                return is_array($value);
            }
        );

        self::assertTrue($this->instance->validate([]));
        self::assertFalse($this->instance->validate('Foo'));
        self::assertInstanceOf(\Closure::class, $this->instance->getHandler());
    }
}

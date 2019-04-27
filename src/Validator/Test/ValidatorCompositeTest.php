<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Validator\Rule\AlnumValidator;
use Windwalker\Validator\Rule\IpValidator;
use Windwalker\Validator\Rule\PhoneValidator;
use Windwalker\Validator\Rule\UrlValidator;
use Windwalker\Validator\ValidatorComposite;
use Windwalker\Validator\ValidatorInterface;

/**
 * Test class of \Windwalker\Validator\ValidatorComposite
 *
 * @since 3.2
 */
class ValidatorCompositeTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var ValidatorComposite
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
        $this->instance = new ValidatorComposite();
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
     * Method to test __construct().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\ValidatorComposite::__construct
     */
    public function testConstruct()
    {
        $v = new ValidatorComposite(
            [
                UrlValidator::class,
                AlnumValidator::class,
            ]
        );

        static::assertNotEmpty($v->getValidators());
        static::assertContainsOnlyInstancesOf(ValidatorInterface::class, $v->getValidators());
    }

    /**
     * Method to test addValidator().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\ValidatorComposite::addValidator
     * @covers \Windwalker\Validator\ValidatorComposite::getValidators
     */
    public function testAddValidator()
    {
        $v = new UrlValidator();
        $this->instance->addValidator($v);

        self::assertSame($v, $this->instance->getValidators()[0]);

        $v = (new ValidatorComposite())->addValidator('is_numeric');

        self::assertTrue($v->validate('123.12'));
    }

    /**
     * Method to test setValidators().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\ValidatorComposite::setValidators
     */
    public function testSetValidators()
    {
        $this->instance->setValidators(
            [
                UrlValidator::class,
                AlnumValidator::class,
            ]
        );

        static::assertNotEmpty($this->instance->getValidators());
        static::assertContainsOnlyInstancesOf(ValidatorInterface::class, $this->instance->getValidators());
    }

    /**
     * Method to test getErrors().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\ValidatorComposite::getErrors
     */
    public function testGetErrors()
    {
        $r = $this->instance->setValidators(
            [
                (new UrlValidator())->setMessage('Invalid URL'),
                (new IpValidator())->setMessage('Invalid IP'),
            ]
        )->validate('Hello');

        self::assertFalse($r);
        self::assertEquals(['Invalid URL', 'Invalid IP'], $this->instance->getErrors());
    }

    /**
     * Method to test setErrors().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\ValidatorComposite::setErrors
     */
    public function testSetErrors()
    {
        $this->instance->setErrors(['Foo']);

        self::assertEquals(['Foo'], $this->instance->getErrors());
    }

    /**
     * Method to test getResults().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\ValidatorComposite::getResults
     */
    public function testMatchAll()
    {
        $r = $this->instance->setValidators(
            [
                new AlnumValidator(),
                new PhoneValidator(),
            ]
        )->validate('1a2b');

        $results = $this->instance->getResults();

        self::assertFalse($r);
        self::assertEquals([true, false], $results);
        self::assertTrue($this->instance->validate('0225647186'));
    }

    /**
     * testMatchOne
     *
     * @return  void
     */
    public function testMatchOne()
    {
        $r = $this->instance->setValidators(
            [
                new AlnumValidator(),
                new PhoneValidator(),
            ]
        )->setMode(ValidatorComposite::MODE_MATCH_ONE)
            ->validate('1a2b');

        $results = $this->instance->getResults();

        self::assertTrue($r);
        self::assertEquals([true, false], $results);
    }
}

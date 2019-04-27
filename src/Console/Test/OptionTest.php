<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Console\Test;

use Windwalker\Console\Command\RootCommand;
use Windwalker\Console\Option\Option;

/**
 * Class OptionTest
 *
 * @since  2.0
 */
class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var Option
     */
    protected $instance;

    /**
     * Test command instance.
     *
     * @var  RootCommand
     */
    protected $command;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     *
     * @since  2.0
     */
    protected function setUp(): void
    {
        $command = new RootCommand('default');

        $this->instance = $option = new Option(['y', 'yell'], 0, 'desc', Option::IS_GLOBAL);

        $command->addOption($option);

        $this->command = $command;
    }

    /**
     * Option test provider.
     *
     * @return array
     *
     * @since  2.0
     */
    public function optionProvider()
    {
        return [
            [
                [
                    'y' => ['y', 'yell', 'Y'],
                ],

                [
                    'yell' => ['y', 'yell', 'Y'],
                ],

                [
                    'Y' => ['y', 'yell', 'Y'],
                ],
            ],
        ];
    }

    /**
     * Test set & get alias.
     *
     * @return void
     *
     * @since  2.0
     */
    public function testSetAndGetAlias()
    {
        $this->instance->setAliases(['yell', 'Y']);

        $alias = $this->instance->getAliases();

        $this->assertEquals(['yell', 'Y'], $alias);
    }

    /**
     * testHasAlias
     *
     * @return  void
     */
    public function testHasAlias()
    {
        $this->assertTrue($this->instance->hasAlias('yell'));
        $this->assertTrue($this->instance->hasAlias('y'));
        $this->assertFalse($this->instance->hasAlias('k'));
        $this->assertFalse($this->instance->hasAlias('foo'));
    }

    /**
     * Test set & get default value.
     *
     * @return void
     *
     * @since  2.0
     */
    public function testSetAndGetDefaultValue()
    {
        $this->instance->defaultValue(0);

        $this->assertEquals(0, $this->instance->getDefaultValue(), 'Default value not matched.');
    }

    /**
     * Test set & get description.
     *
     * @return void
     *
     * @since  2.0
     */
    public function testSetAndGetDescription()
    {
        $this->instance->description('Desc');

        $this->assertEquals('Desc', $this->instance->getDescription(), 'Description value not matched.');
    }

    /**
     * Test set & get name.
     *
     * @return void
     *
     * @since  2.0
     */
    public function testSetAndGetName()
    {
        $this->instance->setName('defaulttt');

        $this->assertEquals('defaulttt', $this->instance->getName(), 'Name value not matched.');
    }

    /**
     * Test set & get input.
     *
     * @return void
     *
     * @since  2.0
     */
    public function testSetAndGetIO()
    {
        $this->assertEquals($this->instance->getIO(), $this->command->getIO(), 'IO not the same instance.');
    }

    /**
     * Test get value.
     *
     * @param   array $inputs The input option.
     *
     * @dataProvider  optionProvider
     *
     * @return  void
     *
     * @since         2.0
     */
    public function testGetValue($inputs)
    {
        foreach ($inputs as $key => $vals) {
            $this->instance->getIO()->setOption($key, 1);

            foreach ($vals as $val) {
                $this->assertEquals(1, $this->instance->getValue($val));
            }
        }

        // Filter
        $this->instance->getIO()->setOption('y', 'flower sakura');

        $this->assertEquals('flower sakura', $this->instance->getValue('y'), 'Default input filter should string.');
    }

    /**
     * Test global.
     *
     * @return void
     *
     * @since  2.0
     */
    public function testGlobal()
    {
        $this->command->addGlobalOption(
            'k',
            'default',
            'k desc'
        );

        $this->command->addCommand('kkk');

        $kkk = $this->command->getChild('kkk');

        $options = $kkk->getAllOptions();

        $this->assertArrayHasKey('k', $options);
    }
}

<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DataMapper\Test;

use Windwalker\DataMapper\Entity\Entity;
use Windwalker\DataMapper\Test\Stub\StubEntity;

/**
 * Test class of Entity
 *
 * @since 2.0
 */
class EntityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var Entity
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
        $this->instance = new Entity($this->getTestFields(), $this->getTestData());
    }

    /**
     * getTestFields
     *
     * @return  array
     */
    protected function getTestFields()
    {
        return [
            'id',
            'title',
            'content',
        ];
    }

    /**
     * getTestData
     *
     * @return  array
     */
    protected function getTestData()
    {
        return [
            'id' => 5,
            'title' => 'Sakura',
            'content' => 'foo',
            'created' => '2014-08-17',
            'user' => 255,
            'params' => '{}',
        ];
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
     * test__construct
     *
     * @return  void
     */
    public function test__construct()
    {
        $dump = $this->instance->dump();

        $this->assertEquals('Sakura', $this->instance->title);
        $this->assertEquals(false, isset($dump['user']));
    }

    /**
     * Method to test addFields().
     *
     * @return void
     *
     * @covers \Windwalker\DataMapper\Entity\Entity::addFields
     */
    public function testAddFields()
    {
        $entity = new Entity();

        $entity->addFields($this->getTestFields());

        $entity->bind($this->getTestData());

        $entity = $entity->dump();

        $this->assertFalse(isset($entity['params']));
        $this->assertEquals('Sakura', $entity['title']);
    }

    /**
     * Method to test addField().
     *
     * @return void
     *
     * @covers \Windwalker\DataMapper\Entity\Entity::addField
     */
    public function testAddField()
    {
        $this->instance->addField('user');

        $this->instance->bind($this->getTestData());

        $this->assertEquals(255, $this->instance->user);
    }

    /**
     * Method to test removeField().
     *
     * @return void
     *
     * @covers \Windwalker\DataMapper\Entity\Entity::removeField
     */
    public function testRemoveField()
    {
        $this->instance->removeField('content');

        $this->instance->bind($this->getTestData());

        $data = $this->instance->dump();

        $this->assertFalse(isset($data['content']));
    }

    /**
     * testAccessor
     *
     * @return  void
     */
    public function testAccessor()
    {
        $entity = new StubEntity();

        $this->assertEquals('foo_bar', $entity->foo_bar);
    }

    /**
     * testMutator
     *
     * @return  void
     */
    public function testMutator()
    {
        $entity = new StubEntity();

        $entity->flower_sakura = 'yoo';

        $this->assertEquals('yoo_bar', $entity->flower_sakura);
    }

    /**
     * testJsonSerialize
     *
     * @return  void
     */
    public function testJsonSerialize()
    {
        $this->assertEquals(json_encode($this->instance->dump(true)), json_encode($this->instance));
    }
}

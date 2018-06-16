<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Test\Postgresql;

use Windwalker\Database\Driver\Pdo\PdoDriver;
use Windwalker\Database\Driver\Pdo\PdoReader;

/**
 * Test class of PostgresqlReader
 *
 * @since 2.0
 */
class PostgresqlReaderTest extends AbstractPostgresqlTestCase
{
    /**
     * Method to test fetchArray().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Pdo\PdoReader::fetchArray
     */
    public function testFetchArray()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10')->execute();

        $item = $reader->fetchArray();

        $this->assertEquals('Alstroemeria', $item[2]);

        $item = $reader->fetchArray();

        $this->assertEquals('Amaryllis', $item[2]);
    }

    /**
     * Method to test fetchAssoc().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Pdo\PdoReader::fetchAssoc
     */
    public function testFetchAssoc()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10')->execute();

        $item = $reader->fetchAssoc();

        $this->assertEquals('Alstroemeria', $item['title']);

        $item = $reader->fetchAssoc();

        $this->assertEquals('Amaryllis', $item['title']);
    }

    /**
     * Method to test fetchObject().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Pdo\PdoReader::fetchObject
     */
    public function testFetchObject()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10')->execute();

        $item = $reader->fetchObject();

        $this->assertEquals('Alstroemeria', $item->title);

        $item = $reader->fetchObject();

        $this->assertEquals('Amaryllis', $item->title);
    }

    /**
     * Method to test fetch().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Pdo\PdoReader::fetch
     */
    public function testFetch()
    {
        /** @var $reader PdoReader */
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10')->execute();

        $item = $reader->fetch(\PDO::FETCH_OBJ);

        $this->assertEquals('Alstroemeria', $item->title);

        $item = $reader->fetch(\PDO::FETCH_OBJ);

        $this->assertEquals('Amaryllis', $item->title);
    }

    /**
     * Method to test fetchAll().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Pdo\PdoReader::fetchAll
     */
    public function testFetchAll()
    {
        /** @var $reader PdoReader */
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10')->execute();

        $items = $reader->fetchAll(\PDO::FETCH_OBJ);

        $this->assertEquals('Alstroemeria', $items[0]->title);

        $this->assertEquals('Amaryllis', $items[1]->title);
    }

    /**
     * Method to test count().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Pdo\PdoReader::count
     */
    public function testCount()
    {
        /** @var $reader PdoReader */
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10')->execute();

        $reader->fetchAll(\PDO::FETCH_OBJ);

        $this->assertEquals(10, $reader->count());
    }

    /**
     * Method to test countAffected().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Pdo\PdoReader::countAffected
     */
    public function testCountAffected()
    {
        $this->db->setQuery('INSERT INTO ' . $this->qn('#__flower') . ' ("catid", "title") VALUES (\'3\', \'test\')');

        $this->db->execute();

        $this->assertEquals(1, $this->db->getReader()->countAffected());
    }

    /**
     * Method to test setQuery().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::setQuery
     */
    public function testSetQuery()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getIterator().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::getIterator
     */
    public function testGetIterator()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10');

        $iterator = $reader->getIterator();

        $this->assertInstanceOf('Windwalker\Database\Iterator\DataIterator', $iterator);

        $items = iterator_to_array($iterator);

        $this->assertEquals('Alstroemeria', $items[0]->title);

        $this->assertEquals('Amaryllis', $items[1]->title);

        // Test nested
        $array = [];

        foreach ($r1 = $this->db->getReader('SELECT * FROM #__categories LIMIT 3') as $cat) {
            foreach ($r2 = $this->db->getReader("SELECT * FROM #__flower WHERE catid={$cat->id} LIMIT 2") as $flower) {
                $array[$cat->title][] = $flower->title;
            }
        }

        $expected = [
            'Foo' => ['Anemone', 'Apple Blossom'],
            'Bar' => ['Alstroemeria', 'Amaryllis'],
        ];

        $this->assertEquals($expected, $array);
    }

    /**
     * Method to test loadResult().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::loadResult
     */
    public function testLoadResult()
    {
        $reader = $this->db->getReader('SELECT title FROM #__flower WHERE id = 2');

        $result = $reader->loadResult();

        $this->assertEquals('Amaryllis', $result);
    }

    /**
     * Method to test loadColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::loadColumn
     */
    public function testLoadColumn()
    {
        $reader = $this->db->getReader('SELECT title FROM #__flower LIMIT 10');

        $result = $reader->loadColumn();

        $this->assertEquals('Alstroemeria', $result[0]);

        $this->assertEquals('Amaryllis', $result[1]);
    }

    /**
     * Method to test loadArray().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::loadArray
     */
    public function testLoadArray()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10');

        $result = $reader->loadArray();

        $this->assertEquals('Alstroemeria', $result[2]);
    }

    /**
     * Method to test loadArrayList().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::loadArrayList
     */
    public function testLoadArrayList()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10');

        $result = $reader->loadArrayList();

        $this->assertEquals('Alstroemeria', $result[0][2]);

        $this->assertEquals('Amaryllis', $result[1][2]);
    }

    /**
     * Method to test loadAssoc().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::loadAssoc
     */
    public function testLoadAssoc()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10');

        $result = $reader->loadAssoc();

        $this->assertEquals('Alstroemeria', $result['title']);
    }

    /**
     * Method to test loadAssocList().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::loadAssocList
     */
    public function testLoadAssocList()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10');

        $result = $reader->loadAssocList();

        $this->assertEquals('Alstroemeria', $result[0]['title']);

        $this->assertEquals('Amaryllis', $result[1]['title']);
    }

    /**
     * Method to test loadObject().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::loadObject
     */
    public function testLoadObject()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10');

        $result = $reader->loadObject();

        $this->assertEquals('Alstroemeria', $result->title);
    }

    /**
     * Method to test loadObjectList().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::loadObjectList
     */
    public function testLoadObjectList()
    {
        $reader = $this->db->getReader('SELECT * FROM #__flower LIMIT 10');

        $result = $reader->loadObjectList();

        $this->assertEquals('Alstroemeria', $result[0]->title);

        $this->assertEquals('Amaryllis', $result[1]->title);
    }

    /**
     * Method to test freeResult().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::freeResult
     * @TODO   Implement testFreeResult().
     */
    public function testFreeResult()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getDb().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractReader::getDriver
     */
    public function testGetDriver()
    {
        $this->assertSame(static::$dbo, $this->db->getReader()->getDriver());
    }

    /**
     * Method to test setDb().
     *
     * @return void
     *
     * @throws \ReflectionException
     * @covers \Windwalker\Database\Command\AbstractReader::setDriver
     */
    public function testSetDriver()
    {
        $driver = new PdoDriver();

        $reader = clone $this->db->getReader();

        $reader->setDriver($driver);

        $this->assertSame($driver, $reader->getDriver());

        unset($reader);
    }
}

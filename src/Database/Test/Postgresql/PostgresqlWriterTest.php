<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Test\Postgresql;

use Windwalker\Compare\EqCompare;

/**
 * Test class of PostgresqlWriter
 *
 * @since 2.0
 */
class PostgresqlWriterTest extends AbstractPostgresqlTestCase
{
    /**
     * Method to test insertOne().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::insertOne
     */
    public function testInsertOne()
    {
        $writer = $this->db->getWriter();

        $compare = new EqCompare('a', 'b');

        $data = new \stdClass();
        $data->catid = 3;
        $data->title = 'Sakura';
        $data->params = $compare;

        $writer->insertOne('#__flower', $data, 'id');

        // Test get inserted id
        $this->assertEquals(86, $data->id);

        $item = $this->db->getReader('SELECT * FROM #__flower WHERE id = 86')->loadObject();

        // Re query item back
        $this->assertEquals('Sakura', $item->title);
        $this->assertEquals((string) $compare, $item->params);

        // Use array
        $data = [];
        $data['catid'] = 4;
        $data['title'] = 'Sunflower';

        $writer->insertOne('#__flower', $data, 'id');

        // Test get inserted id
        $this->assertEquals(87, $data['id']);

        $item = $this->db->getReader('SELECT * FROM #__flower WHERE id = 87')->loadObject();

        // Re query item back
        $this->assertEquals('Sunflower', $item->title);
    }

    /**
     * Method to test updateOne().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::updateOne
     */
    public function testUpdateOne()
    {
        $writer = $this->db->getWriter();

        $compare = new EqCompare('c', 'd');

        $data = new \stdClass();
        $data->id = 86;
        $data->title = 'Sakura2';
        $data->params = $compare;

        $writer->updateOne('#__flower', $data, 'id');

        $item = $this->db->getReader('SELECT * FROM #__flower WHERE id = 86')->loadObject();

        $this->assertEquals('Sakura2', $item->title);
        $this->assertEquals((string) $compare, $item->params);

        // Use array
        $data = [];
        $data['id'] = 87;
        $data['catid'] = 5;
        $data['title'] = 'Sunflower2';

        $writer->updateOne('#__flower', $data, 'id');

        $item = $this->db->getReader('SELECT * FROM #__flower WHERE id = 87')->loadObject();

        // Re query item back
        $this->assertEquals('Sunflower2', $item->title);

        // Test multiple keys
        $data = new \stdClass();
        $data->catid = 2;
        $data->ordering = 8;
        $data->title = 'Rose';

        $writer->updateOne('#__flower', $data, ['catid', 'ordering']);

        $item = $this->db->getReader('SELECT * FROM #__flower WHERE catid = 2 AND ordering = 8')->loadObject();

        $this->assertEquals('Rose', $item->title);
    }

    /**
     * Method to test saveOne().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::saveOne
     */
    public function testSaveOne()
    {
        $writer = $this->db->getWriter();

        // Insert
        $data = new \stdClass();
        $data->catid = 3;
        $data->title = 'Sakura';

        $writer->saveOne('#__flower', $data, 'id');

        // Test get inserted id
        $this->assertEquals(88, $data->id);

        $item = $this->db->getReader('SELECT * FROM #__flower WHERE id = 88')->loadObject();

        // Re query item back
        $this->assertEquals('Sakura', $item->title);

        // Update
        $data = [];
        $data['id'] = 88;
        $data['catid'] = 3;
        $data['title'] = 'Sakura2';

        $writer->saveOne('#__flower', $data, 'id');

        $item = $this->db->getReader('SELECT * FROM #__flower WHERE id = 88')->loadObject();

        // Re query item back
        $this->assertEquals('Sakura2', $item->title);
    }

    /**
     * Method to test insertMultiple().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::insertMultiple
     */
    public function testInsertMultiple()
    {
        $writer = $this->db->getWriter();

        $dataSet = [
            ['title' => 'Foo', 'catid' => 6],
            ['title' => 'Bar', 'catid' => 6],
        ];

        $writer->insertMultiple('#__flower', $dataSet, 'id');

        $this->assertEquals($dataSet[0]['id'], 89);

        $items = $this->db->getReader('SELECT * FROM #__flower WHERE catid = 6')->loadObjectList();

        $this->assertEquals('Bar', $items[1]->title);
    }

    /**
     * Method to test updateMultiple().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::updateMultiple
     */
    public function testUpdateMultiple()
    {
        $writer = $this->db->getWriter();

        $dataSet = [
            ['id' => 89, 'title' => 'Foo2', 'catid' => 6],
            ['id' => 90, 'title' => 'Bar2', 'catid' => 6],
        ];

        $writer->updateMultiple('#__flower', $dataSet, 'id');

        $items = $this->db->getReader('SELECT * FROM #__flower WHERE catid = 6')->loadObjectList();

        $this->assertEquals('Bar2', $items[1]->title);
    }

    /**
     * Method to test saveMultiple().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::saveMultiple
     * @TODO   Implement testSaveMultiple().
     */
    public function testSaveMultiple()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test updateBatch().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::updateBatch
     */
    public function testUpdateBatch()
    {
        $writer = $this->db->getWriter();

        $data = ['state' => 1];

        $writer->updateBatch('#__flower', $data, ['state' => 0, 'catid' => 2]);

        $items = $this->db->getReader('SELECT * FROM #__flower WHERE catid = 2')->loadObjectList();

        $this->assertEquals(1, $items[0]->state);
    }

    /**
     * Method to test countAffected().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::countAffected
     */
    public function testCountAffected()
    {
        $this->db->setQuery('UPDATE #__flower SET state = 0 WHERE id = 5')->execute();

        $count = $this->db->getWriter()->countAffected();

        $this->assertEquals(1, $count);
    }

    /**
     * Method to test getDriver().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::getDriver
     * @TODO   Implement testGetDriver().
     */
    public function testGetDriver()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setDriver().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractWriter::setDriver
     * @TODO   Implement testSetDriver().
     */
    public function testSetDriver()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test insertId().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Pdo\PdoWriter::insertId
     */
    public function testInsertId()
    {
        $this->db->setQuery('INSERT INTO #__flower (catid, title) VALUES (\'3\', \'Foo3\')')->execute();

        $this->assertEquals(91, $this->db->getWriter()->insertId());
    }
}

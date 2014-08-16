<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper\Test;

use Windwalker\Data\Data;
use Windwalker\Data\DataSet;
use Windwalker\DataMapper\DataMapper;

/**
 * Test class of DataMapper
 *
 * @since {DEPLOY_VERSION}
 */
class DataMapperTest extends DatabaseTest
{
	/**
	 * Test instance.
	 *
	 * @var DataMapper
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
		parent::setUp();

		$this->db = static::$dbo;

		$this->instance = new DataMapper('ww_flower');
	}


	/**
	 * Method to test find().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::find
	 */
	public function testFind()
	{
		$dataset = $this->instance->find(array(), null, 0, 3);

		$this->assertEquals(array(1, 2, 3), $dataset->id);
		$this->assertEquals(array('Alstroemeria', 'Amaryllis', 'Anemone'), $dataset->title);

		$dataset = $this->instance->find(array('state' => 1), 'ordering DESC', 2, 3);

		$this->assertEquals(array(1, 1, 1), $dataset->state);
		$this->assertEquals(array(82, 79, 77), $dataset->ordering);
		$this->assertEquals(array('Violet', 'red', 'pink'), $dataset->title);
	}

	/**
	 * Method to test findAll().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::findAll
	 */
	public function testFindAll()
	{
		$dataset = $this->instance->findAll('catid', 0, 3);

		$this->assertEquals(array(3, 4, 7), $dataset->id);
		$this->assertEquals(array('Anemone', 'Apple Blossom', 'Baby\'s Breath'), $dataset->title);
	}

	/**
	 * Method to test findOne().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::findOne
	 */
	public function testFindOne()
	{
		// Find by primary key
		$data = $this->instance->findOne(7);

		$this->assertInstanceOf('Windwalker\\Data\\Data', $data, 'Return not Data object.');
		$this->assertEquals('Baby\'s Breath', $data->title);

		// Find by conditions
		$data = $this->instance->findOne(array('title' => 'Cosmos'));

		$this->assertEquals('peaceful', $data->meaning);

		$data = $this->instance->findOne(array('title' => 'Freesia', 'state' => 1));

		$this->assertTrue($data->isNull());
	}

	/**
	 * Method to test create().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::create
	 */
	public function testCreate()
	{
		// Create from array
		$dataset = array(
			array('title' => 'Sakura'),
			array('title' => 'Peony'),
			array('title' => 'Sunflower')
		);

		$returns = $this->instance->create($dataset);

		$newDataset = $this->loadToDataset('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 3');

		$this->assertEquals(array('Sunflower', 'Peony', 'Sakura'), $newDataset->title);

		$this->assertEquals(86, $returns[0]->id, 'Inserted id not matched.');

		$this->assertInstanceOf('Windwalker\\Data\\Data', $returns[0], 'Return not Data object.');

		// Create from DataSet
		$dataset = new DataSet(
			array(
				new Data(array('title' => 'Sakura2')),
				new Data(array('title' => 'Peony2')),
				new Data(array('title' => 'Sunflower2'))
			)
		);

		$returns = $this->instance->create($dataset);

		$newDataset = $this->loadToDataset('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 3');

		$this->assertEquals(array('Sunflower2', 'Peony2', 'Sakura2'), $newDataset->title);

		$this->assertEquals(89, $returns[0]->id, 'Inserted id not matched.');

		$this->assertInstanceOf('Windwalker\\Data\\Data', $returns[0], 'Return not Data object.');
	}

	/**
	 * Method to test createOne().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::createOne
	 */
	public function testCreateOne()
	{
		// Create from array
		$data = array(
			'title' => 'Foo flower',
			'state' => 1
		);

		$newData = $this->instance->createOne($data);

		$this->assertEquals(92, $newData->id);
		$this->assertEquals(92, $this->loadToData('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 1')->id);

		$this->assertInstanceOf('Windwalker\\Data\\Data', $newData, 'Return not Data object.');

		// Create from Data
		$data = new Data(
			array(
				'title' => 'Foo flower',
				'state' => 1
			)
		);

		$newData = $this->instance->createOne($data);

		$this->assertEquals(93, $newData->id);
		$this->assertEquals(93, $this->loadToData('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 1')->id);

		$this->assertInstanceOf('Windwalker\\Data\\Data', $newData, 'Return not Data object.');
	}

	/**
	 * Method to test update().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::update
	 */
	public function testUpdate()
	{
		// Update from array
		$dataset = array(
			array('id' => 1, 'state' => 1),
			array('id' => 2, 'state' => 1),
			array('id' => 3, 'state' => 1)
		);

		$returns = $this->instance->update($dataset, 'id');

		$updateDataset = $this->loadToDataset('SELECT * FROM ww_flower LIMIT 3');

		$this->assertEquals(array(1, 1, 1), $updateDataset->state);

		$this->assertInstanceOf('Windwalker\\Data\\Data', $returns[0], 'Return not Data object.');

		// Use from DataSet
		$dataset = new DataSet(
			array(
				new Data(array('id' => 1, 'state' => 0)),
				new Data(array('id' => 2, 'state' => 0)),
				new Data(array('id' => 3, 'state' => 0))
			)
		);

		$returns = $this->instance->update($dataset, 'id');

		$updateDataset = $this->loadToDataset('SELECT * FROM ww_flower LIMIT 3');

		$this->assertEquals(array(0, 0, 0), $updateDataset->state);

		$this->assertInstanceOf('Windwalker\\Data\\Data', $returns[0], 'Return not Data object.');
	}

	/**
	 * Method to test updateOne().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::updateOne
	 */
	public function testUpdateOne()
	{
		// Update from array
		$data = array('id' => 10, 'params' => '{}');

		$updateData = $this->instance->updateOne($data);

		$this->assertEquals('{}', $this->loadToData('SELECT * FROM ww_flower WHERE id = 10 LIMIT 1')->params);

		$this->assertInstanceOf('Windwalker\\Data\\Data', $updateData, 'Return not Data object.');

		// Update from Data
		$data = new Data(array('id' => 11, 'params' => '{}'));

		$updateData = $this->instance->updateOne($data);

		$this->assertEquals('{}', $this->loadToData('SELECT * FROM ww_flower WHERE id = 11 LIMIT 1')->params);

		$this->assertInstanceOf('Windwalker\\Data\\Data', $updateData, 'Return not Data object.');
	}

	/**
	 * Method to test updateAll().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::updateAll
	 */
	public function testUpdateAll()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test flush().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::flush
	 * @TODO   Implement testFlush().
	 */
	public function testFlush()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test save().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::save
	 * @TODO   Implement testSave().
	 */
	public function testSave()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test saveOne().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::saveOne
	 * @TODO   Implement testSaveOne().
	 */
	public function testSaveOne()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test delete().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::delete
	 * @TODO   Implement testDelete().
	 */
	public function testDelete()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getPrimaryKey().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::getPrimaryKey
	 * @TODO   Implement testGetPrimaryKey().
	 */
	public function testGetPrimaryKey()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getTable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::getTable
	 * @TODO   Implement testGetTable().
	 */
	public function testGetTable()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setTable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::setTable
	 * @TODO   Implement testSetTable().
	 */
	public function testSetTable()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getDataClass().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::getDataClass
	 * @TODO   Implement testGetDataClass().
	 */
	public function testGetDataClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setDataClass().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::setDataClass
	 * @TODO   Implement testSetDataClass().
	 */
	public function testSetDataClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getDatasetClass().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::getDatasetClass
	 * @TODO   Implement testGetDatasetClass().
	 */
	public function testGetDatasetClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setDatasetClass().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::setDatasetClass
	 * @TODO   Implement testSetDatasetClass().
	 */
	public function testSetDatasetClass()
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
	 * @covers Windwalker\DataMapper\DataMapper::getDb
	 */
	public function testGetAndSetDb()
	{
		$this->assertInstanceOf('Windwalker\\DataMapper\\Adapter\\WindwalkerAdapter', $this->instance->getDb());
	}
}

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
		$dataset = array(
			array('title' => 'Sakura'),
			array('title' => 'Peony'),
			array('title' => 'Sunflower')
		);

		$returns = $this->instance->create($dataset);

		$newDataset = $this->loadToDataset('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 3');

		$this->assertEquals(array('Sunflower', 'Peony', 'Sakura'), $newDataset->title);

		$this->assertEquals(86, $returns[0]->id, 'Inserted id not matched.');
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
		$data = array(
			'title' => 'Foo flower',
			'state' => 1
		);

		$newData = $this->instance->createOne($data);

		$this->assertEquals(89, $newData->id);
		$this->assertEquals(89, $this->loadToData('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 1')->id);
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
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test updateOne().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::updateOne
	 * @TODO   Implement testUpdateOne().
	 */
	public function testUpdateOne()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test updateAll().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::updateAll
	 * @TODO   Implement testUpdateAll().
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

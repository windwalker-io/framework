<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper\Test;

use Windwalker\Data\Data;
use Windwalker\Data\DataSet;
use Windwalker\DataMapper\DataMapper;

/**
 * Test class of DataMapper
 *
 * @since 2.0
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

		$dataset = $this->instance->find(null, null, 0, 3);

		$this->assertEquals(array(), $dataset->id);

		$dataset = $this->instance->find(0, null, 0, 3);

		$this->assertEquals(array(), $dataset->id);

		$dataset = $this->instance->find(false, null, 0, 3);

		$this->assertEquals(array(), $dataset->id);

		$dataset = $this->instance->find(array('state' => 1), 'ordering DESC', 2, 3);

		$this->assertEquals(array(1, 1, 1), $dataset->state);
		$this->assertEquals(array(82, 79, 77), $dataset->ordering);
		$this->assertEquals(array('Violet', 'red', 'pink'), $dataset->title);

		$datamapper = new DataMapper('ww_flower');

		$datamapper->setSelectFields(array('id', 'state'));

		$dataset = $datamapper->find(array('state' => 1), 'ordering DESC', 2, 3);

		$this->assertEquals(array(null, null, null), $dataset->catid);
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
		$dataset = $this->instance->findAll('catid, ordering', 0, 3);

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
	 * testFindColumn
	 *
	 * @return  void
	 */
	public function testFindColumn()
	{
		$columns = $this->instance->findColumn('id', array(), 'catid, ordering', 0, 3);

		$this->assertEquals(array(3, 4, 7), $columns);
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

			// DataMapper should remove non-necessary field
			array('title' => 'Sunflower', 'anim' => 'bird')
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

		// TODO: Test Update Nulls
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

		// TODO: Test Update Nulls
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
		$data = array('state' => 0);

		$this->instance->updateAll($data, array('id' => array(4, 5, 6)));

		$dataset = $this->loadToDataset('SELECT * FROM ww_flower WHERE id IN(4, 5, 6)');

		$this->assertEquals(array(0, 0, 0), $dataset->state);
	}

	/**
	 * Method to test flush().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::flush
	 */
	public function testFlush()
	{
		// Prepare test data
		$this->db->setQuery('UPDATE ww_flower SET catid = 3 WHERE id IN (6, 7, 8)')->execute();

		$dataset = array(
			array('title' => 'Baby\'s Breath2', 'catid' => 3),
			array('title' => 'Bachelor Button2', 'catid' => 3),
			array('title' => 'Begonia2', 'catid' => 3),
		);

		// Delete all catid = 3 and re insert them.
		$returns = $this->instance->flush($dataset, array('catid' => 3));

		$newDataset = $this->loadToDataset('SELECT * FROM ww_flower WHERE catid = 3');

		$this->assertEquals(array('Baby\'s Breath2', 'Bachelor Button2', 'Begonia2'), $newDataset->title);
		$this->assertEquals(array(94, 95, 96), $newDataset->id);
	}

	/**
	 * Method to test save().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::save
	 */
	public function testSave()
	{
		$dataset = array(
			array('title' => 'Sunflower', 'catid' => 5),
			array('id' => 15, 'title' => 'striped2', 'catid' => 5),
		);

		$returns = $this->instance->save($dataset, 'id');

		$returns = new DataSet($returns);

		$newDataset = $this->loadToDataset('SELECT * FROM ww_flower WHERE catid = 5');

		$this->assertEquals(array(97, 15), $returns->id, 'Inserted ID not matched');
		$this->assertEquals(array(5, 5), $newDataset->catid, 'New catid should be 5');
	}

	/**
	 * Method to test saveOne().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::saveOne
	 */
	public function testSaveOne()
	{
		$data = array('title' => 'Sakura', 'catid' => 6);

		$return = $this->instance->saveOne($data, 'id');

		$this->assertEquals('Sakura', $this->db->setQuery('SELECT title FROM ww_flower WHERE catid = 6')->loadResult());
		$this->assertEquals(98, $return->id);

		$data = array('id' => 15, 'title' => 'striped3', 'catid' => 6);

		$return = $this->instance->saveOne($data, 'id');

		$this->assertEquals('striped3', $this->db->setQuery('SELECT title FROM ww_flower WHERE id = 15')->loadResult());
		$this->assertEquals(15, $return->id);
	}

	/**
	 * Method to test delete().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::delete
	 */
	public function testDelete()
	{
		$this->instance->delete(array('id' => 16));

		$this->assertFalse($this->loadToData('SELECT * FROM ww_flower WHERE id = 16'));
	}

	/**
	 * Method to test getPrimaryKey().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::getPrimaryKey
	 */
	public function testGetPrimaryKey()
	{
		$this->assertEquals('id', $this->instance->getPrimaryKey());

		$mapper = new DataMapper('ww_flower', array('a', 'b'));

		$this->assertEquals(array('a', 'b'), $mapper->getPrimaryKey());
	}

	/**
	 * Method to test getTable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::getTable
	 */
	public function testGetTable()
	{
		$this->assertEquals('ww_flower', $this->instance->getTable());
	}

	/**
	 * Method to test setTable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::setTable
	 */
	public function testSetTable()
	{
		$this->instance->setTable('ww_categories');

		$this->assertEquals(array('Foo', 'Bar'), $this->instance->findAll()->title);
	}

	/**
	 * Method to test getDataClass().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::getDataClass
	 */
	public function testGetAndSetDataClass()
	{
		$this->instance->setDataClass('stdClass');

		$this->assertEquals('stdClass', $this->instance->getDataClass());

		// If we use DataSet, all stdClass will be auto convert to Data
		$this->assertInstanceOf('Windwalker\Data\Data', $this->instance->findOne());

		$this->instance->setDatasetClass('ArrayObject');

		$this->assertInstanceOf('stdClass', $this->instance->findOne());
	}

	/**
	 * Method to test getDatasetClass().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\AbstractDataMapper::getDatasetClass
	 */
	public function testGetAndSetDatasetClass()
	{
		$this->instance->setDatasetClass('ArrayObject');

		$this->assertEquals('ArrayObject', $this->instance->getDatasetClass());

		$this->assertInstanceOf('ArrayObject', $this->instance->findAll('id', 0, 2));
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

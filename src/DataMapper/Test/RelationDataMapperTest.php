<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper\Test;

use Windwalker\Compare\GteCompare;
use Windwalker\DataMapper\RelationDataMapper;

/**
 * Test class of RelationDataMapper
 *
 * @since {DEPLOY_VERSION}
 */
class RelationDataMapperTest extends DatabaseTest
{
	/**
	 * Test instance.
	 *
	 * @var RelationDataMapper
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

		$this->instance = new RelationDataMapper('flower', 'ww_flower');

		$this->instance->addTable('cat', 'ww_categories', 'flower.catid = cat.id');
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
	 * Test find()
	 *
	 * @covers Windwalker\DataMapper\RelationDataMapper::find
	 *
	 * @return void
	 */
	public function testFind()
	{
		$dataset = $this->instance->find(
			array(
				'flower.state' => 1,
				new GteCompare('cat.id', 2)
			),
			'flower.title DESC',
			1,
			3
		);

		$sql = <<<SQL
SELECT `flower`.`id`,
	`flower`.`catid`,
	`flower`.`title`,
	`flower`.`meaning`,
	`flower`.`ordering`,
	`flower`.`state`,
	`flower`.`params`,
	`cat`.`id` AS `cat_id`,
	`cat`.`title` AS `cat_title`,
	`cat`.`ordering` AS `cat_ordering`,
	`cat`.`params` AS `cat_params`
FROM `ww_flower` AS `flower`
	LEFT JOIN `ww_categories` AS `cat` ON flower.catid = cat.id
WHERE `flower`.`state` = 1
	AND `cat`.`id` >= 2
ORDER BY flower.title DESC
LIMIT 1, 3
SQL;

		$this->assertEquals($dataset, $this->loadToDataset($sql));
	}

	/**
	 * Test findAll()
	 *
	 * @covers Windwalker\DataMapper\RelationDataMapper::findAll
	 *
	 * @return void
	 */
	public function testFindAll()
	{
		$dataset = $this->instance->findAll(
			'flower.title DESC',
			1,
			3
		);

		$sql = <<<SQL
SELECT `flower`.`id`,
	`flower`.`catid`,
	`flower`.`title`,
	`flower`.`meaning`,
	`flower`.`ordering`,
	`flower`.`state`,
	`flower`.`params`,
	`cat`.`id` AS `cat_id`,
	`cat`.`title` AS `cat_title`,
	`cat`.`ordering` AS `cat_ordering`,
	`cat`.`params` AS `cat_params`
FROM `ww_flower` AS `flower`
	LEFT JOIN `ww_categories` AS `cat` ON flower.catid = cat.id
ORDER BY flower.title DESC
LIMIT 1, 3
SQL;

		$this->assertEquals($dataset, $this->loadToDataset($sql));
	}

	/**
	 * Test find one.
	 *
	 * @covers Windwalker\DataMapper\RelationDataMapper::findOne
	 *
	 * @return void
	 */
	public function testFindOne()
	{
		$data = $this->instance->findOne(
			array(
				'flower.state' => 1,
				new GteCompare('cat.id', 2)
			),
			'flower.title DESC'
		);

		$sql = <<<SQL
SELECT `flower`.`id`,
	`flower`.`catid`,
	`flower`.`title`,
	`flower`.`meaning`,
	`flower`.`ordering`,
	`flower`.`state`,
	`flower`.`params`,
	`cat`.`id` AS `cat_id`,
	`cat`.`title` AS `cat_title`,
	`cat`.`ordering` AS `cat_ordering`,
	`cat`.`params` AS `cat_params`
FROM `ww_flower` AS `flower`
	LEFT JOIN `ww_categories` AS `cat` ON flower.catid = cat.id
WHERE `flower`.`state` = 1
	AND `cat`.`id` >= 2
ORDER BY flower.title DESC
SQL;

		$this->assertEquals($data, $this->loadToData($sql));
	}

	/**
	 * Method to test addTable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\RelationDataMapper::addTable
	 */
	public function testAddTable()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test removeTable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\DataMapper\RelationDataMapper::removeTable
	 * @TODO   Implement testRemoveTable().
	 */
	public function testRemoveTable()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}

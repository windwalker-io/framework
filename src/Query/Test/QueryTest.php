<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Query\Test;

use Windwalker\Query\Query;
use Windwalker\Utilities\Test\TestHelper;

/**
 * Test class of Query
 *
 * @since {DEPLOY_VERSION}
 */
class QueryTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Query
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
		$this->instance = new Query;
	}

	/**
	 * getQuery
	 *
	 * @return  Query
	 */
	protected function getQuery()
	{
		return new Query;
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
	 * Method to test __get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::__get
	 * @TODO   Implement test__get().
	 */
	public function test__get()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test call().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::call
	 * @TODO   Implement testCall().
	 */
	public function testCall()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test clear().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::clear
	 */
	public function testClear()
	{
		$query = $this->getQuery();

		$query->select('*')->from('foo');

		$query->clear();

		$this->assertNull(TestHelper::getValue($query, 'select'));
		$this->assertNull(TestHelper::getValue($query, 'from'));
	}

	/**
	 * Method to test clear().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::clear
	 */
	public function testClearClause()
	{
		$q = $this->getQuery();

		$clauses = array(
			'from',
			'join',
			'set',
			'where',
			'group',
			'having',
			// Union should before order because it will clear order.
			'union',
			'order',
			'columns',
			'values',
			'exec',
			'call',
		);

		// Set the clauses
		foreach ($clauses as $clause)
		{
			$q->$clause('foo', 'bar', 'yoo', 'goo');
		}

		// Test each clause.
		foreach ($clauses as $clause)
		{
			$query = clone $q;

			// Clear the clause.
			$query->clear($clause);

			// Check that clause was cleared.
			$this->assertNull(TestHelper::getValue($query, $clause));

			// Check the state of the other clauses.
			foreach ($clauses as $clause2)
			{
				if ($clause != $clause2)
				{
					$this->assertNotNull(TestHelper::getValue($query, $clause2), $clause2 . ' Should not be NULL if we clear ' . $clause . '.');
				}
			}
		}
	}

	/**
	 * Method to test clear().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::clear
	 */
	public function testClearType()
	{
		$q = $this->getQuery();

		$types = array(
			'select',
			'delete',
			'update',
			'insert'
		);

		$clauses = array(
			'from',
			'join',
			'set',
			'where',
			'group',
			'having',
			'union',
			'order',
			'columns',
			'values',
		);

		// Set the clauses
		foreach ($clauses as $clause)
		{
			$q->$clause('foo', 'bar', 'yoo', 'goo');
		}

		// Check that all properties have been cleared
		foreach ($types as $type)
		{
			$query = clone $q;

			// Set the type.
			$query->$type('foo', 'bar');

			// Clear the type.
			$query->clear($type);

			// Check the type has been cleared.
			$this->assertNull(TestHelper::getValue($query, 'type'), 'Query property: ' . $type . ' should be null.');

			$this->assertNull(TestHelper::getValue($query, $type), $type . ' should be null.');

			// Now check the claues have not been affected.
			foreach ($clauses as $clause)
			{
				$this->assertNotNull(TestHelper::getValue($query, $clause), $clause . ' should exists if we clear ' . $type);
			}
		}
	}

	/**
	 * Method to test columns().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::columns
	 */
	public function testColumns()
	{
		$query = $this->getQuery()
			->insert('foo')
			->columns('a, b, c')
			->values('1, 2, 3');

		$this->assertEquals('INSERT INTO foo' . PHP_EOL . '(a, b, c) VALUES ' . PHP_EOL . '(1, 2, 3)', trim((string) $query));

		$query = $this->getQuery()
			->insert('foo')
			->columns(array('a', 'b', 'c'))
			->values('1, 2, 3');

		$this->assertEquals('INSERT INTO foo' . PHP_EOL . '(a,b,c) VALUES ' . PHP_EOL . '(1, 2, 3)', trim((string) $query));
	}

	/**
	 * Method to test dateFormat().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::dateFormat
	 */
	public function testDateFormat()
	{
		$this->assertEquals('Y-m-d H:i:s', $this->instance->dateFormat());
	}

	/**
	 * Method to test dump().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::dump
	 * @TODO   Implement testDump().
	 */
	public function testDump()
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
	 * @covers Windwalker\Query\Query::delete
	 */
	public function testDelete()
	{
		$query = $this->getQuery()
			->delete('foo')
			->where('flower = "sakura"');

		$this->assertEquals('DELETE ' . PHP_EOL . 'FROM foo' . PHP_EOL . 'WHERE flower = "sakura"', trim((string) $query));
	}

	/**
	 * Method to test escape().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::escape
	 * @covers Windwalker\Query\Query::e
	 */
	public function testEscape()
	{
		// Default query has no quote.
		$this->assertEquals('foo', $this->instance->escape('foo'));
	}

	/**
	 * Method to test exec().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::exec
	 */
	public function testExec()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test from().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::from
	 */
	public function testFrom()
	{
		$query = $this->getQuery()
			->select('*')
			->from('foo');

		$this->assertEquals('SELECT *' . PHP_EOL . 'FROM foo', trim((string) $query));

		// Subquery
		$query = $this->getQuery()
			->select('*')
			->from('foo AS a')
			->from($query, 'b');

		$this->assertEquals('SELECT *' . PHP_EOL . 'FROM foo AS a,' . PHP_EOL . '(SELECT *' . PHP_EOL . 'FROM foo) AS b', trim((string) $query));

		// Array
		$query = $this->getQuery()
			->select('*')
			->from(array('foo', 'bar'));

		$this->assertEquals('SELECT *' . PHP_EOL . 'FROM foo,bar', trim((string) $query));
	}

	/**
	 * Method to test expression().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::expression
	 */
	public function testExpression()
	{
		$this->assertEquals('FOO(flower, sakura)', $this->instance->expression('FOO', 'flower', 'sakura'));
	}

	/**
	 * Method to test group().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::group
	 */
	public function testGroup()
	{
		$query = $this->getQuery()
			->select('a.*')
			->from('foo AS a')
			->group('a.id');

		$this->assertEquals('SELECT a.*' . PHP_EOL . 'FROM foo AS a' . PHP_EOL . 'GROUP BY a.id', trim((string) $query));
	}

	/**
	 * Method to test having().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::having
	 */
	public function testHaving()
	{
		$query = $this->getQuery()
			->select('a.id AS aid')
			->from('foo AS a')
			->having('aid = "sun"');

		$this->assertEquals('SELECT a.id AS aid' . PHP_EOL . 'FROM foo AS a' . PHP_EOL . 'HAVING aid = "sun"', trim((string) $query));
	}

	/**
	 * Method to test innerJoin().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::innerJoin
	 */
	public function testInnerJoin()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test insert().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::insert
	 * @TODO   Implement testInsert().
	 */
	public function testInsert()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test join().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::join
	 * @TODO   Implement testJoin().
	 */
	public function testJoin()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test leftJoin().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::leftJoin
	 * @TODO   Implement testLeftJoin().
	 */
	public function testLeftJoin()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test nullDate().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::nullDate
	 * @TODO   Implement testNullDate().
	 */
	public function testNullDate()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test order().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::order
	 * @TODO   Implement testOrder().
	 */
	public function testOrder()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test limit().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::limit
	 * @TODO   Implement testLimit().
	 */
	public function testLimit()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test processLimit().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::processLimit
	 * @TODO   Implement testProcessLimit().
	 */
	public function testProcessLimit()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test outerJoin().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::outerJoin
	 * @TODO   Implement testOuterJoin().
	 */
	public function testOuterJoin()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test quote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::quote
	 * @TODO   Implement testQuote().
	 */
	public function testQuote()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test q().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::q
	 * @TODO   Implement testQ().
	 */
	public function testQ()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test quoteName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::quoteName
	 * @TODO   Implement testQuoteName().
	 */
	public function testQuoteName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test qn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::qn
	 * @TODO   Implement testQn().
	 */
	public function testQn()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test rightJoin().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::rightJoin
	 * @TODO   Implement testRightJoin().
	 */
	public function testRightJoin()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test select().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::select
	 * @TODO   Implement testSelect().
	 */
	public function testSelect()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test set().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::set
	 * @TODO   Implement testSet().
	 */
	public function testSet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setQuery().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::setQuery
	 * @TODO   Implement testSetQuery().
	 */
	public function testSetQuery()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test update().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::update
	 * @TODO   Implement testUpdate().
	 */
	public function testUpdate()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test values().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::values
	 */
	public function testValues()
	{
		$query = $this->getQuery()
			->insert('foo')
			->columns('a, b, c')
			->values('1, 2, 3');

		$this->assertEquals('INSERT INTO foo' . PHP_EOL . '(a, b, c) VALUES ' . PHP_EOL . '(1, 2, 3)', trim((string) $query));

		$query = $this->getQuery()
			->insert('foo')
			->columns('a, b, c')
			->values(array('1, 2, 3', '1, 2, 3', '1, 2, 3'));

		$this->assertEquals(
			'INSERT INTO foo' . PHP_EOL . '(a, b, c) VALUES ' . PHP_EOL . '(1, 2, 3),' . PHP_EOL . '(1, 2, 3),' . PHP_EOL . '(1, 2, 3)',
			trim((string) $query)
		);

		$query = $this->getQuery()
			->insert('foo')
			->columns('a, b, c')
			->values(
				array(
					array(1, 2, 3),
					array(1, 2, 3),
					array(1, 2, 3),
				)
			);

		$this->assertEquals(
			'INSERT INTO foo' . PHP_EOL . '(a, b, c) VALUES ' . PHP_EOL . '(1,2,3),' . PHP_EOL . '(1,2,3),' . PHP_EOL . '(1,2,3)',
			trim((string) $query)
		);
	}

	/**
	 * Method to test where().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::where
	 * @TODO   Implement testWhere().
	 */
	public function testWhere()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test __clone().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::__clone
	 * @TODO   Implement test__clone().
	 */
	public function test__clone()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test union().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::union
	 * @TODO   Implement testUnion().
	 */
	public function testUnion()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test unionDistinct().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::unionDistinct
	 * @TODO   Implement testUnionDistinct().
	 */
	public function testUnionDistinct()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test format().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::format
	 * @TODO   Implement testFormat().
	 */
	public function testFormat()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::getName
	 * @TODO   Implement testGetName().
	 */
	public function testGetName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getExpression().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::getExpression
	 * @TODO   Implement testGetExpression().
	 */
	public function testGetExpression()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setExpression().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::setExpression
	 * @TODO   Implement testSetExpression().
	 */
	public function testSetExpression()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}

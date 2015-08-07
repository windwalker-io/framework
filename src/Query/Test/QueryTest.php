<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Test;

use Windwalker\Query\Query;
use Windwalker\Test\TestHelper;

/**
 * Test class of Query
 *
 * @since 2.0
 */
class QueryTest extends AbstractQueryTestCase
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
	 */
	public function testCall()
	{
		$query = $this->getQuery()
			->call(array('foo', 'bar'));

		$sql = 'CALL foo,bar';

		$this->assertEquals($this->format($sql), $this->format($query));
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
		$this->assertEquals('foo "\'\'_-!@#$%^&*() \n' . " \t " . '\r \000', $this->instance->escape("foo \"'_-!@#$%^&*() \n \t \r \0"));
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
		$query = $this->getQuery()
			->exec('foo');

		$this->assertEquals('EXEC foo', trim($query));
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
	 * Method to test expression().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::expression
	 */
	public function testExpr()
	{
		$this->assertEquals('FOO(flower, sakura)', $this->instance->expr('FOO', 'flower', 'sakura'));
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
		// Add one join
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->innerJoin('bar AS b', 'a.id = b.aid');

		$sql = 'SELECT a.*, b.* FROM foo AS a INNER JOIN bar AS b ON a.id = b.aid';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Add multiple conditions
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->innerJoin('bar AS b', array('a.id = b.aid', 'a.user = b.user'));

		$sql = 'SELECT a.*, b.* FROM foo AS a INNER JOIN bar AS b ON a.id = b.aid AND a.user = b.user';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Use array
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->innerJoin(
				array(
					'bar AS b ON a.id = b.aid',
					'yoo AS y ON a.id = y.aid'
				)
			);

		$sql = 'SELECT a.*, b.* FROM foo AS a INNER JOIN bar AS b ON a.id = b.aid,yoo AS y ON a.id = y.aid';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Add two join
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->innerJoin('bar AS b', 'a.id = b.aid')
			->innerJoin('yoo AS y', 'a.id = y.aid');

		$sql = 'SELECT a.*, b.* FROM foo AS a INNER JOIN bar AS b ON a.id = b.aid INNER JOIN yoo AS y ON a.id = y.aid';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test insert().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::insert
	 */
	public function testInsert()
	{
		$query = $this->getQuery()
			->insert('foo')
			->columns('a, b, c')
			->values('1, 2, 3');

		$sql = 'INSERT INTO foo (a, b, c) VALUES  (1, 2, 3)';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test join().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::join
	 */
	public function testJoin()
	{
		// Add one join
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->join('LEFT', 'bar AS b', 'a.id = b.aid');

		$sql = 'SELECT a.*, b.* FROM foo AS a LEFT JOIN bar AS b ON a.id = b.aid';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Add multiple conditions
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->join('RIGHT', 'bar AS b', array('a.id = b.aid', 'a.user = b.user'));

		$sql = 'SELECT a.*, b.* FROM foo AS a RIGHT JOIN bar AS b ON a.id = b.aid AND a.user = b.user';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Use array
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->join('INNER',
				array(
					'bar AS b ON a.id = b.aid',
					'yoo AS y ON a.id = y.aid'
				)
			);

		$sql = 'SELECT a.*, b.* FROM foo AS a INNER JOIN bar AS b ON a.id = b.aid,yoo AS y ON a.id = y.aid';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test leftJoin().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::leftJoin
	 */
	public function testLeftJoin()
	{
		// Add one join
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->leftJoin('bar AS b', 'a.id = b.aid');

		$sql = 'SELECT a.*, b.* FROM foo AS a LEFT JOIN bar AS b ON a.id = b.aid';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Add multiple conditions
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->leftJoin('bar AS b', array('a.id = b.aid', 'a.user = b.user'));

		$sql = 'SELECT a.*, b.* FROM foo AS a LEFT JOIN bar AS b ON a.id = b.aid AND a.user = b.user';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Use array
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->leftJoin(
				array(
					'bar AS b ON a.id = b.aid',
					'yoo AS y ON a.id = y.aid'
				)
			);

		$sql = 'SELECT a.*, b.* FROM foo AS a LEFT JOIN bar AS b ON a.id = b.aid,yoo AS y ON a.id = y.aid';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Add two join
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->leftJoin('bar AS b', 'a.id = b.aid')
			->leftJoin('yoo AS y', 'a.id = y.aid');

		$sql = 'SELECT a.*, b.* FROM foo AS a LEFT JOIN bar AS b ON a.id = b.aid LEFT JOIN yoo AS y ON a.id = y.aid';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test nullDate().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::nullDate
	 */
	public function testNullDate()
	{
		$this->assertEquals($this->instance->quote('0000-00-00 00:00:00'), $this->instance->nullDate());

		$this->assertEquals('0000-00-00 00:00:00', $this->instance->nullDate(false));
	}

	/**
	 * Method to test order().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::order
	 */
	public function testOrder()
	{
		$query = $this->getQuery()
			->select('*')
			->from('foo')
			->where('a = b')
			->order('id');

		$sql = 'SELECT * FROM foo WHERE a = b ORDER BY id';

		$this->assertEquals($this->format($sql), $this->format($query));

		$query = $this->getQuery()
			->select('*')
			->from('foo')
			->where('a = b')
			->order(array('id DESC', 'catid'));

		$sql = 'SELECT * FROM foo WHERE a = b ORDER BY id DESC,catid';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test limit().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::limit
	 */
	public function testLimit()
	{
		$query = $this->getQuery()
			->select('*')
			->from('foo')
			->where('a = b')
			->order('id')
			->limit(3);

		$sql = 'SELECT * FROM foo WHERE a = b ORDER BY id LIMIT 3';

		$this->assertEquals($this->format($sql), $this->format($query));

		$query = $this->getQuery()
			->select('*')
			->from('foo')
			->where('a = b')
			->order('id')
			->limit(3, 1);

		$sql = 'SELECT * FROM foo WHERE a = b ORDER BY id LIMIT 1, 3';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test processLimit().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::processLimit
	 */
	public function testProcessLimit()
	{
		$query = $this->getQuery()
			->select('*')
			->from('foo')
			->where('a = b')
			->order('id');

		$sql = 'SELECT * FROM foo WHERE a = b ORDER BY id LIMIT 3';

		$this->assertEquals($this->format($sql), $this->format($query->processLimit($query, 3)));

		$query = $this->getQuery()
			->select('*')
			->from('foo')
			->where('a = b')
			->order('id');

		$sql = 'SELECT * FROM foo WHERE a = b ORDER BY id LIMIT 0, 3';

		$this->assertEquals($this->format($sql), $this->format($query->processLimit($query, 3, 0)));
	}

	/**
	 * Method to test outerJoin().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::outerJoin
	 */
	public function testOuterJoin()
	{
		// Add one join
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->outerJoin('bar AS b', 'a.id = b.aid');

		$sql = 'SELECT a.*, b.* FROM foo AS a OUTER JOIN bar AS b ON a.id = b.aid';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Add multiple conditions
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->outerJoin('bar AS b', array('a.id = b.aid', 'a.user = b.user'));

		$sql = 'SELECT a.*, b.* FROM foo AS a OUTER JOIN bar AS b ON a.id = b.aid AND a.user = b.user';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Use array
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->outerJoin(
				array(
					'bar AS b ON a.id = b.aid',
					'yoo AS y ON a.id = y.aid'
				)
			);

		$sql = 'SELECT a.*, b.* FROM foo AS a OUTER JOIN bar AS b ON a.id = b.aid,yoo AS y ON a.id = y.aid';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Add two join
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->outerJoin('bar AS b', 'a.id = b.aid')
			->outerJoin('yoo AS y', 'a.id = y.aid');

		$sql = 'SELECT a.*, b.* FROM foo AS a OUTER JOIN bar AS b ON a.id = b.aid OUTER JOIN yoo AS y ON a.id = y.aid';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test quote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::quote
	 */
	public function testQuote()
	{
		$this->assertEquals("'foo'", $this->instance->quote('foo'));
	}

	/**
	 * Method to test q().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::q
	 */
	public function testQ()
	{
		$this->assertEquals("'foo'", $this->instance->q('foo'));
	}

	/**
	 * Method to test quoteName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::qn
	 */
	public function testQuoteName()
	{
		$this->assertEquals('"foo"', $this->instance->quoteName('foo'));
	}

	/**
	 * Method to test rightJoin().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::rightJoin
	 */
	public function testRightJoin()
	{
		// Add one join
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->rightJoin('bar AS b', 'a.id = b.aid');

		$sql = 'SELECT a.*, b.* FROM foo AS a RIGHT JOIN bar AS b ON a.id = b.aid';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Add multiple conditions
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->rightJoin('bar AS b', array('a.id = b.aid', 'a.user = b.user'));

		$sql = 'SELECT a.*, b.* FROM foo AS a RIGHT JOIN bar AS b ON a.id = b.aid AND a.user = b.user';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Use array
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->rightJoin(
				array(
					'bar AS b ON a.id = b.aid',
					'yoo AS y ON a.id = y.aid'
				)
			);

		$sql = 'SELECT a.*, b.* FROM foo AS a RIGHT JOIN bar AS b ON a.id = b.aid,yoo AS y ON a.id = y.aid';

		$this->assertEquals($this->format($sql), $this->format($query));

		// Add two join
		$query = $this->getQuery()
			->select('a.*, b.*')
			->from('foo AS a')
			->rightJoin('bar AS b', 'a.id = b.aid')
			->rightJoin('yoo AS y', 'a.id = y.aid');

		$sql = 'SELECT a.*, b.* FROM foo AS a RIGHT JOIN bar AS b ON a.id = b.aid RIGHT JOIN yoo AS y ON a.id = y.aid';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test select().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::select
	 */
	public function testSelect()
	{
		$query = $this->getQuery()
			->select('*')
			->from('foo')
			->where('a = b')
			->order('id');

		$sql = 'SELECT * FROM foo WHERE a = b ORDER BY id';

		$this->assertEquals($this->format($sql), $this->format($query));

		$query = $this->getQuery()
			->select(array('a.*', 'a.id'))
			->from('foo AS a')
			->where('a = b')
			->order('id');

		$sql = 'SELECT a.*,a.id FROM foo AS a WHERE a = b ORDER BY id';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test set().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::set
	 */
	public function testSet()
	{
		$query = $this->getQuery()
			->update('foo')
			->set('a = b')
			->set('c = d');

		$sql = 'UPDATE foo SET a = b , c = d';

		$this->assertEquals($this->format($sql), $this->format($query));

		$query = $this->getQuery()
			->update('foo')
			->set(array('a = b', 'c = d'));

		$sql = 'UPDATE foo SET a = b , c = d';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test setQuery().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::setQuery
	 */
	public function testSetQuery()
	{
		$query = $this->getQuery();

		$query->setQuery('SELECT foo');

		$this->assertEquals('SELECT foo', (string) $query);
	}

	/**
	 * Method to test update().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::update
	 */
	public function testUpdate()
	{
		$query = $this->getQuery()
			->update('foo')
			->set('a = b')
			->set('c = d')
			->where('id = 1');

		$sql = 'UPDATE foo SET a = b , c = d WHERE id = 1';

		$this->assertEquals($this->format($sql), $this->format($query));

		$query = $this->getQuery()
			->update('foo AS a')
			->set('a = b')
			->set('c = d')
			->leftJoin('bar AS b ON a.id = b.aid')
			->where('id = 1');

		$sql = 'UPDATE foo AS a LEFT JOIN bar AS b ON a.id = b.aid SET a = b , c = d WHERE id = 1';

		$this->assertEquals($this->format($sql), $this->format($query));
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
	 */
	public function testWhere()
	{
		$query = $this->getQuery()
			->select('*')
			->from('foo')
			->where('a = b')
			->order('id');

		$sql = 'SELECT * FROM foo WHERE a = b ORDER BY id';

		$this->assertEquals($this->format($sql), $this->format($query));

		$query = $this->getQuery()
			->update('foo')
			->set('a = b')
			->set('c = d')
			->where('id = 1');

		$sql = 'UPDATE foo SET a = b , c = d WHERE id = 1';

		$this->assertEquals($this->format($sql), $this->format($query));

		$query = $this->getQuery()
			->delete('foo')
			->where('flower = "sakura"');

		$this->assertEquals('DELETE ' . PHP_EOL . 'FROM foo' . PHP_EOL . 'WHERE flower = "sakura"', trim((string) $query));
	}

	/**
	 * Method to test __clone().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::__clone
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
	 */
	public function testUnion()
	{
		$query = $this->getQuery();

		$query->union(
			$this->getQuery()
				->select('*')
				->from('foo')
				->where('a = b')
				->order('id')
		)->union(
			$this->getQuery()
				->select('*')
				->from('foo')
				->where('a = b')
				->order('id')
		);

		$sql = '( SELECT * FROM foo WHERE a = b ORDER BY id) UNION ( SELECT * FROM foo WHERE a = b ORDER BY id)';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test unionDistinct().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::unionDistinct
	 */
	public function testUnionDistinct()
	{
		$query = $this->getQuery();

		$query->unionDistinct(
			$this->getQuery()
				->select('*')
				->from('foo')
				->where('a = b')
				->order('id')
		)->union(
				$this->getQuery()
					->select('*')
					->from('foo')
					->where('a = b')
					->order('id')
			);

		$sql = '( SELECT * FROM foo WHERE a = b ORDER BY id) UNION DISTINCT ( SELECT * FROM foo WHERE a = b ORDER BY id)';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test union().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::union
	 */
	public function testUnionAll()
	{
		$query = $this->getQuery();

		$query->unionAll(
			$this->getQuery()
				->select('*')
				->from('foo')
				->where('a = b')
				->order('id')
		)->union(
				$this->getQuery()
					->select('*')
					->from('foo')
					->where('a = b')
					->order('id')
			);

		$sql = '( SELECT * FROM foo WHERE a = b ORDER BY id) UNION ALL ( SELECT * FROM foo WHERE a = b ORDER BY id)';

		$this->assertEquals($this->format($sql), $this->format($query));
	}

	/**
	 * Method to test format().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::format
	 */
	public function testFormat()
	{
		$result = $this->instance->format('SELECT %n FROM %n WHERE %n = %a', 'foo', '#__bar', 'id', 10);

		$sql = 'SELECT ' . $this->instance->qn('foo') . ' FROM ' . $this->instance->qn('#__bar') .
			' WHERE ' . $this->instance->qn('id') . ' = 10';

		$this->assertEquals($sql, $result);

		$result = $this->instance->format('SELECT %n FROM %n WHERE %n = %t OR %3$n = %Z', 'id', '#__foo', 'date');

		$sql = 'SELECT ' . $this->instance->qn('id') . ' FROM ' . $this->instance->qn('#__foo') .
			' WHERE ' . $this->instance->qn('date') . ' = ' . $this->instance->expression('current_timestamp') .
			' OR ' . $this->instance->qn('date') . ' = ' . $this->instance->nullDate(true);

		$this->assertEquals($sql, $result);
	}

	/**
	 * Method to test getName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::getName
	 */
	public function testGetName()
	{
		$this->assertEquals('', $this->instance->getName());
	}

	/**
	 * Method to test getExpression().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Query::getExpression
	 */
	public function testGetExpression()
	{
		$this->assertInstanceOf('Windwalker\\Query\\QueryExpression', $this->instance->getExpression());
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

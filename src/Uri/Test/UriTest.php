<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Uri\Tests;

use Windwalker\Uri\Uri;

/**
 * Tests for the Windwalker\Uri\Uri class.
 *
 * @since  2.0
 */
class UriTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Object under test
	 *
	 * @var    Uri
	 * @since  2.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function setUp()
	{
		$this->object = new Uri('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');
	}

	/**
	 * Test the __toString method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::__toString
	 */
	public function test__toString()
	{
		$this->assertThat(
			$this->object->__toString(),
			$this->equalTo('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment')
		);
	}

	/**
	 * Test the parse method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::parse
	 * @covers  \Windwalker\Uri\Uri::__construct
	 */
	public function testConstruct()
	{
		$object = new Uri('http://someuser:somepass@www.example.com:80/path/file.html?var=value&amp;test=true#fragment');

		$this->assertThat(
			$object->getHost(),
			$this->equalTo('www.example.com')
		);

		$this->assertThat(
			$object->getPath(),
			$this->equalTo('/path/file.html')
		);

		$this->assertThat(
			$object->getScheme(),
			$this->equalTo('http')
		);
	}

	/**
	 * Test the toString method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::toString
	 */
	public function testToString()
	{
		$this->assertThat(
			$this->object->toString(),
			$this->equalTo('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment')
		);

		$this->object->setQuery('somevar=somevalue');
		$this->object->setVar('somevar2', 'somevalue2');
		$this->object->setScheme('ftp');
		$this->object->setUser('root');
		$this->object->setPass('secret');
		$this->object->setHost('www.example.org');
		$this->object->setPort('8888');
		$this->object->setFragment('someFragment');
		$this->object->setPath('/this/is/a/path/to/a/file');

		$this->assertThat(
			$this->object->toString(),
			$this->equalTo('ftp://root:secret@www.example.org:8888/this/is/a/path/to/a/file?somevar=somevalue&somevar2=somevalue2#someFragment')
		);
	}

	/**
	 * Test the setVar method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::setVar
	 */
	public function testSetVar()
	{
		$this->object->setVar('somevariable', 'somevalue');

		$this->assertThat(
			$this->object->getVar('somevariable'),
			$this->equalTo('somevalue')
		);
	}

	/**
	 * Test the hasVar method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::hasVar
	 */
	public function testHasVar()
	{
		$this->assertThat(
			$this->object->hasVar('somevariable'),
			$this->equalTo(false)
		);

		$this->assertThat(
			$this->object->hasVar('var'),
			$this->equalTo(true)
		);
	}

	/**
	 * Test the getVar method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::getVar
	 */
	public function testGetVar()
	{
		$this->assertThat(
			$this->object->getVar('var'),
			$this->equalTo('value')
		);

		$this->assertThat(
			$this->object->getVar('var2'),
			$this->equalTo('')
		);

		$this->assertThat(
			$this->object->getVar('var2', 'default'),
			$this->equalTo('default')
		);
	}

	/**
	 * Test the delVar method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::delVar
	 */
	public function testDelVar()
	{
		$this->assertThat(
			$this->object->getVar('var'),
			$this->equalTo('value')
		);

		$this->object->delVar('var');

		$this->assertThat(
			$this->object->getVar('var'),
			$this->equalTo('')
		);
	}

	/**
	 * Test the setQuery method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::setQuery
	 */
	public function testSetQuery()
	{
		$this->object->setQuery('somevar=somevalue');

		$this->assertThat(
			$this->object->getQuery(),
			$this->equalTo('somevar=somevalue')
		);

		$this->object->setQuery('somevar=somevalue&amp;test=true');

		$this->assertThat(
			$this->object->getQuery(),
			$this->equalTo('somevar=somevalue&test=true')
		);

		$this->object->setQuery(array('somevar' => 'somevalue', 'test' => 'true'));

		$this->assertThat(
			$this->object->getQuery(),
			$this->equalTo('somevar=somevalue&test=true')
		);
	}

	/**
	 * Test the getQuery method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::getQuery
	 */
	public function testGetQuery()
	{
		$this->assertThat(
			$this->object->getQuery(),
			$this->equalTo('var=value')
		);

		$this->assertThat(
			$this->object->getQuery(true),
			$this->equalTo(array('var' => 'value'))
		);
	}

	/**
	 * Test the getScheme method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::getScheme
	 */
	public function testGetScheme()
	{
		$this->assertThat(
			$this->object->getScheme(),
			$this->equalTo('http')
		);
	}

	/**
	 * Test the setScheme method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::setScheme
	 */
	public function testSetScheme()
	{
		$this->object->setScheme('ftp');

		$this->assertThat(
			$this->object->getScheme(),
			$this->equalTo('ftp')
		);
	}

	/**
	 * Test the getUser method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::getUser
	 */
	public function testGetUser()
	{
		$this->assertThat(
			$this->object->getUser(),
			$this->equalTo('someuser')
		);
	}

	/**
	 * Test the setUser method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::setUser
	 */
	public function testSetUser()
	{
		$this->object->setUser('root');

		$this->assertThat(
			$this->object->getUser(),
			$this->equalTo('root')
		);
	}

	/**
	 * Test the getPass method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::getPass
	 */
	public function testGetPass()
	{
		$this->assertThat(
			$this->object->getPass(),
			$this->equalTo('somepass')
		);
	}

	/**
	 * Test the setPass method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::setPass
	 */
	public function testSetPass()
	{
		$this->object->setPass('secret');

		$this->assertThat(
			$this->object->getPass(),
			$this->equalTo('secret')
		);
	}

	/**
	 * Test the getHost method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::getHost
	 */
	public function testGetHost()
	{
		$this->assertThat(
			$this->object->getHost(),
			$this->equalTo('www.example.com')
		);
	}

	/**
	 * Test the setHost method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::setHost
	 */
	public function testSetHost()
	{
		$this->object->setHost('www.example.org');

		$this->assertThat(
			$this->object->getHost(),
			$this->equalTo('www.example.org')
		);
	}

	/**
	 * Test the getPort method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::getPort
	 */
	public function testGetPort()
	{
		$this->assertThat(
			$this->object->getPort(),
			$this->equalTo('80')
		);
	}

	/**
	 * Test the setPort method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::setPort
	 */
	public function testSetPort()
	{
		$this->object->setPort('8888');

		$this->assertThat(
			$this->object->getPort(),
			$this->equalTo('8888')
		);
	}

	/**
	 * Test the getPath method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::getPath
	 */
	public function testGetPath()
	{
		$this->assertThat(
			$this->object->getPath(),
			$this->equalTo('/path/file.html')
		);
	}

	/**
	 * Test the setPath method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::setPath
	 */
	public function testSetPath()
	{
		$this->object->setPath('/this/is/a/path/to/a/file.htm');

		$this->assertThat(
			$this->object->getPath(),
			$this->equalTo('/this/is/a/path/to/a/file.htm')
		);
	}

	/**
	 * Test the getFragment method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::getFragment
	 */
	public function testGetFragment()
	{
		$this->assertThat(
			$this->object->getFragment(),
			$this->equalTo('fragment')
		);
	}

	/**
	 * Test the setFragment method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::setFragment
	 */
	public function testSetFragment()
	{
		$this->object->setFragment('someFragment');

		$this->assertThat(
			$this->object->getFragment(),
			$this->equalTo('someFragment')
		);
	}

	/**
	 * Test the isSSL method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @covers  \Windwalker\Uri\Uri::isSSL
	 */
	public function testIsSSL()
	{
		$object = new Uri('https://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');

		$this->assertThat(
			$object->isSSL(),
			$this->equalTo(true)
		);

		$object = new Uri('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');

		$this->assertThat(
			$object->isSSL(),
			$this->equalTo(false)
		);
	}
}

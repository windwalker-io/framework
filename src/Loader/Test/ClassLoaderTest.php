<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Loader\Test;

use Windwalker\Loader\ClassLoader;
use Windwalker\Loader\Loader\Psr0Loader;
use Windwalker\Loader\Test\Mock\MockFileMappingLoader;
use Windwalker\Loader\Test\Mock\MockPsr0Loader;
use Windwalker\Loader\Test\Mock\MockPsr4Loader;

/**
 * Test class of ClassLoader
 *
 * @since 2.0
 */
class ClassLoaderTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Test instance.
	 *
	 * @var ClassLoader
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
		$this->instance = new ClassLoader(
			new MockFileMappingLoader,
			new MockPsr0Loader,
			new MockPsr4Loader
		);

		$this->instance->register();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->instance->unregister();
	}

	/**
	 * Method to test register().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Loader\ClassLoader::register
	 */
	public function testRegister()
	{
		$this->instance->unregister();

		$this->instance->register();

		$funcs = spl_autoload_functions();

		$func = array_pop($funcs);

		$this->assertInstanceOf('Windwalker\Loader\Loader\Psr4Loader', $func[0]);

		$func = array_pop($funcs);

		$this->assertInstanceOf('Windwalker\Loader\Loader\Psr0Loader', $func[0]);

		$func = array_pop($funcs);

		$this->assertInstanceOf('Windwalker\Loader\Loader\FileMappingLoader', $func[0]);
	}

	/**
	 * Method to test unregister().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Loader\ClassLoader::unregister
	 */
	public function testUnregister()
	{
		$this->instance->unregister();

		$funcs = spl_autoload_functions();

		$func = array_pop($funcs);

		if (!is_array($func))
		{
			$func = array($func);
		}

		$this->assertNotInstanceOf('Windwalker\Loader\Loader\Psr4Loader', $func[0]);
	}

	/**
	 * Method to test addPsr0().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Loader\ClassLoader::addPsr0
	 */
	public function testAddPsr0()
	{
		/** @var MockPsr0Loader $psr0 */
		$psr0 = $this->instance->getPsr0Loader();

		// Test 1 prefix
		$this->instance->addPsr0('Psr0', __DIR__ . '/fixtures');

		$psr0->loadClass('Psr0\Flower\Sakura');

		$this->assertFileEquals(__DIR__ . '/fixtures/Psr0/Flower/Sakura.php', $psr0->getLastRequired());

		$psr0->loadClass('Psr0_Olive_Peace');

		$this->assertFileEquals(__DIR__ . '/fixtures/Psr0/Olive/Peace.php', $psr0->getLastRequired());

		$psr0 = $this->instance->unregister()
			->setPsr0Loader(new MockPsr0Loader)
			->register()
			->getPsr0Loader();

		// Test 2 prefix
		$this->instance->addPsr0('Psr0\Flower', __DIR__ . '/fixtures');

		$psr0->loadClass('Psr0\Flower\Sakura');

		$this->assertFileEquals(__DIR__ . '/fixtures/Psr0/Flower/Sakura.php', $psr0->getLastRequired());

		$psr0->loadClass('Psr0_Olive_Peace');

		$this->assertNull($psr0->getLastRequired());

		$psr0 = $this->instance->unregister()
			->setPsr0Loader(new MockPsr0Loader)
			->register()
			->getPsr0Loader();

		// Test 0 prefix
		$this->instance->addPsr0('', __DIR__ . '/fixtures');

		$psr0->loadClass('Psr0\Flower\Sakura');

		$this->assertFileEquals(__DIR__ . '/fixtures/Psr0/Flower/Sakura.php', $psr0->getLastRequired());

		$psr0->loadClass('Psr0_Olive_Peace');

		$this->assertFileEquals(__DIR__ . '/fixtures/Psr0/Olive/Peace.php', $psr0->getLastRequired());
	}

	/**
	 * Method to test addPsr4().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Loader\ClassLoader::addPsr4
	 */
	public function testAddPsr4()
	{
		/** @var MockPsr4Loader $psr4 */
		$psr4 = $this->instance->getPsr4Loader();

		// Test 1 prefix
		$this->instance->addPsr4('Psr4', __DIR__ . '/fixtures');

		$psr4->loadClass('Psr4\Rose\Love');

		$this->assertFileEquals(__DIR__ . '/fixtures/Rose/Love.php', $psr4->getLastRequired());

		$psr4 = $this->instance->unregister()
			->setPsr4Loader(new MockPsr4Loader)
			->register()
			->getPsr4Loader();

		// Test 2 prefix
		$this->instance->addPsr4('Psr4\\Rose', __DIR__ . '/fixtures/Rose');

		$psr4->loadClass('Psr4\Rose\Love');

		$this->assertFileEquals(__DIR__ . '/fixtures/Rose/Love.php', $psr4->getLastRequired());

		// Test as Psr0 prefix
		$this->instance->addPsr4('Psr0', __DIR__ . '/fixtures/Psr0');

		$psr4->loadClass('Psr0\Flower\Sakura');

		$this->assertFileEquals(__DIR__ . '/fixtures/Psr0/Flower/Sakura.php', $psr4->getLastRequired());
	}

	/**
	 * Method to test addMap().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Loader\ClassLoader::addMap
	 */
	public function testAddMap()
	{
		/** @var MockFileMappingLoader $map */
		$map = $this->instance->getFilesLoader();

		$this->instance->addMap('Sun\Flower\Sunflower', __DIR__ . '/fixtures/Sunflower.php');

		$map->loadClass('Sun\Flower\Sunflower');

		$this->assertFileEquals(__DIR__ . '/fixtures/Sunflower.php', $map->getLastRequired());

		$this->instance->addMap('WindTalker', __DIR__ . '/fixtures/WindTalker.php');

		$map->loadClass('WindTalker');

		$this->assertFileEquals(__DIR__ . '/fixtures/WindTalker.php', $map->getLastRequired());
	}
}

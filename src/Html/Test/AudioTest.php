<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Html\Test;

use Windwalker\Html\Media\Audio;
use Windwalker\Dom\Test\AbstractDomTestCase;

/**
 * Test class of Audio
 *
 * @since 2.1
 */
class AudioTest extends AbstractDomTestCase
{
	/**
	 * Test instance.
	 *
	 * @var Audio
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
	 * Method to test addMp3Source().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Html\Media\Audio::addMp3Source
	 */
	public function testToString()
	{
		$audio = Audio::create(array('class' => 'foo'))
			->addWavSource('foo.wav')
			->addOggSource('foo.ogg')
			->addMp3Source('foo.mp3', 'screen and (min-width:320px)')
			->autoplay(true)
			->controls(true)
			->loop(true)
			->muted(true)
			->preload(Audio::PRELOAD_METADATA);

		$html = <<<HTML
<audio class="foo" autoplay controls loop muted preload="metadata">
    <source src="foo.wav" type="audio/wav" />
	<source src="foo.ogg" type="audio/ogg" />
	<source src="foo.mp3" type="audio/mpeg" media="screen and (min-width:320px)" />
</audio>
HTML;

		$this->assertHtmlFormatEquals($html, $audio);
	}
}

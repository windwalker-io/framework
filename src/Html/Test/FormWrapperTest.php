<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Html\Test;

use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\FormWrapper;
use Windwalker\Html\Form\InputElement;
use Windwalker\Test\TestCase\AbstractDomTestCase;

/**
 * Test class of FormWrapper
 *
 * @since {DEPLOY_VERSION}
 */
class FormWrapperTest extends AbstractDomTestCase
{
	/**
	 * Test instance.
	 *
	 * @var FormWrapper
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
		$this->instance = new FormWrapper;
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
	 * Method to test create().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Html\Form\FormWrapper::create
	 */
	public function testCreate()
	{
		$this->assertInstanceOf(get_class($this->instance), FormWrapper::create());

		$form = FormWrapper::create('Foo', array('class' => 'foo'));

		$this->assertDomFormatEquals('<form class="foo">Foo</form>', $form);
	}

	/**
	 * Method to test start().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Html\Form\FormWrapper::start
	 */
	public function testStart()
	{
		$this->assertEquals('<form>', FormWrapper::start());
		$this->assertEquals(
			'<form name="foo" id="foo" method="get" action="foo.php" enctype="multipart/form-data">',
			FormWrapper::start('foo', FormWrapper::METHOD_GET, 'foo.php', FormWrapper::ENCTYPE_FORM_DATA)
		);
	}

	/**
	 * Method to test end().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Html\Form\FormWrapper::end
	 */
	public function testEnd()
	{
		$this->assertEquals('</form>', FormWrapper::end());

		FormWrapper::setTokenHandler(function()
		{
			return new InputElement('hidden', 'token', 1);
		});

		$this->assertEquals('<input type="hidden" name="token" value="1" /></form>', FormWrapper::end());
	}

	/**
	 * Method to test renderStart().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Html\Form\FormWrapper::*
	 */
	public function testRenderStart()
	{
		$form = new FormWrapper;

		$form->accept('UTF-8')
			->acceptCharset('UTF-8')
			->action('foo.php')
			->autocomplete('off')
			->enctype(FormWrapper::ENCTYPE_URLENCODED)
			->method(FormWrapper::METHOD_POST)
			->name('test')
			->novalidate(true)
			->target('_blank');

		$html = <<<HTML
<form accept="UTF-8" accept-charset="UTF-8" action="foo.php" autocomplete="off" enctype="application/x-www-form-urlencoded" method="post" name="test" novalidate="novalidate" target="_blank">
HTML;

		$this->assertDomFormatEquals($html, $form->renderStart());
	}
}

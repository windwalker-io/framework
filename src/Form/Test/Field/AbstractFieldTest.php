<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Form\Test\Field;

use Windwalker\Form\Test\Stub\StubField;
use Windwalker\Test\TestCase\DomTestCase;
use Windwalker\Validator\Rule\IpValidator;

/**
 * Test class of AbstractField
 *
 * @since {DEPLOY_VERSION}
 */
class AbstractFieldTest extends DomTestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubField
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
		$this->instance = new StubField(
			'flower',
			'Flower',
			array(
				'placeholder' => 'The Flower',
				'class' => 'stub-flower'
			)
		);
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
	 * Method to test renderInput().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::renderInput
	 */
	public function testRenderInput()
	{
		$expect = <<<HTML
<input type="text" name="flower" id="flower" class="stub-flower" />
HTML;

		$this->assertDomStringEqualsDomString($expect, $this->instance->renderInput());

		// Control
		$this->instance->setControl('windwalker');

		$expect = <<<HTML
<input type="text" name="windwalker[flower]" id="windwalker-flower" class="stub-flower" />
HTML;

		$this->assertDomStringEqualsDomString($expect, $this->instance->renderInput());

		// Value
		$this->instance->setValue('sakura');

		$expect = <<<HTML
<input type="text" name="windwalker[flower]" id="windwalker-flower" class="stub-flower" value="sakura" />
HTML;

		$this->assertDomStringEqualsDomString($expect, $this->instance->renderInput());
	}

	/**
	 * Method to test buildInput().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::buildInput
	 * @TODO   Implement testBuildInput().
	 */
	public function testBuildInput()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test renderLabel().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::renderLabel
	 */
	public function testRenderLabel()
	{
		$expect = <<<HTML
<label id="flower-label" for="flower">Flower</label>
HTML;

		$this->assertDomStringEqualsDomString($expect, $this->instance->renderLabel());
	}

	/**
	 * Method to test renderView().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::renderView
	 */
	public function testRenderView()
	{
		$this->instance->setValue('sakura');

		$this->assertEquals('sakura', $this->instance->renderView());
	}

	/**
	 * Method to test render().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::render
	 * @TODO   Implement testRender().
	 */
	public function testRender()
	{
		$this->instance->setControl('windwalker');
		$this->instance->setValue('sakura');

		$this->instance->setAttribute('controlClass', 'control-group');

		$expect = <<<HTML
<div id="windwalker-flower-control" class="stub-field control-group">
	<label id="windwalker-flower-label" for="windwalker-flower">Flower</label>
	<input type="text" name="windwalker[flower]" id="windwalker-flower" class="stub-flower" value="sakura" />
</div>
HTML;

		$this->assertDomStringEqualsDomString($expect, $this->instance->render());
	}

	/**
	 * Method to test getLabel().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getLabel
	 */
	public function testGetLabel()
	{
		$this->assertEquals('Flower', $this->instance->getLabel());
	}

	/**
	 * Method to test getId().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getId
	 */
	public function testGetId()
	{
		$this->assertEquals('flower', $this->instance->getId());

		$this->instance->setControl('windwalker');

		$this->assertEquals('windwalker-flower', $this->instance->getId());
	}

	/**
	 * Method to test validate().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::validate
	 */
	public function testValidate()
	{
		$field = new StubField(
			'flower',
			'Flower',
			array(
				'placeholder' => 'The Flower',
				'class' => 'stub-flower'
			),
			null,
			new IpValidator
		);

		$this->assertTrue($field->setValue('123.21.23.156')->validate()->isSuccess());
		$this->assertFalse($field->setValue('/var/foo/bar')->validate()->isSuccess());
	}

	/**
	 * Method to test checkRequired().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::checkRequired
	 * @TODO   Implement testCheckRequired().
	 */
	public function testCheckRequired()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test checkRule().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::checkRule
	 * @TODO   Implement testCheckRule().
	 */
	public function testCheckRule()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test filter().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::filter
	 * @TODO   Implement testFilter().
	 */
	public function testFilter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test prepareStore().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::prepareStore
	 * @TODO   Implement testPrepareStore().
	 */
	public function testPrepareStore()
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
	 * @covers Windwalker\Form\Field\AbstractField::getName
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
	 * Method to test setName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::setName
	 * @TODO   Implement testSetName().
	 */
	public function testSetName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getFieldName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getFieldName
	 * @TODO   Implement testGetFieldName().
	 */
	public function testGetFieldName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setFieldName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::setFieldName
	 * @TODO   Implement testSetFieldName().
	 */
	public function testSetFieldName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getGroup().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getGroup
	 * @TODO   Implement testGetGroup().
	 */
	public function testGetGroup()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setGroup().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::setGroup
	 * @TODO   Implement testSetGroup().
	 */
	public function testSetGroup()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getFieldset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getFieldset
	 * @TODO   Implement testGetFieldset().
	 */
	public function testGetFieldset()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setFieldset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::setFieldset
	 * @TODO   Implement testSetFieldset().
	 */
	public function testSetFieldset()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getValue().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getValue
	 * @TODO   Implement testGetValue().
	 */
	public function testGetValue()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setValue().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::setValue
	 * @TODO   Implement testSetValue().
	 */
	public function testSetValue()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setValidator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::setValidator
	 * @TODO   Implement testSetValidator().
	 */
	public function testSetValidator()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getValidator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getValidator
	 * @TODO   Implement testGetValidator().
	 */
	public function testGetValidator()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setFilter().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::setFilter
	 * @TODO   Implement testSetFilter().
	 */
	public function testSetFilter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getFilter().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getFilter
	 * @TODO   Implement testGetFilter().
	 */
	public function testGetFilter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getControl().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getControl
	 * @TODO   Implement testGetControl().
	 */
	public function testGetControl()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setControl().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::setControl
	 * @TODO   Implement testSetControl().
	 */
	public function testSetControl()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getAttribute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getAttribute
	 * @TODO   Implement testGetAttribute().
	 */
	public function testGetAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setAttribute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::setAttribute
	 * @TODO   Implement testSetAttribute().
	 */
	public function testSetAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::get
	 * @TODO   Implement testGet().
	 */
	public function testGet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getBool().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getBool
	 * @TODO   Implement testGetBool().
	 */
	public function testGetBool()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getFalse().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getFalse
	 * @TODO   Implement testGetFalse().
	 */
	public function testGetFalse()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getAttributes().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::getAttributes
	 * @TODO   Implement testGetAttributes().
	 */
	public function testGetAttributes()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test def().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\AbstractField::def
	 * @TODO   Implement testDef().
	 */
	public function testDef()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}

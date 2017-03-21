<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Test\Field;

use Windwalker\Form\Test\Mock\MockFilter;
use Windwalker\Filter\InputFilter;
use Windwalker\Form\Test\Stub\StubField;
use Windwalker\Form\Test\Stub\StubFilter;
use Windwalker\Form\Validate\ValidateResult;
use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Validator\Rule\EmailValidator;
use Windwalker\Validator\Rule\IpValidator;

/**
 * Test class of AbstractField
 *
 * @since 2.0
 */
class AbstractFieldTest extends AbstractDomTestCase
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
			[
				'placeholder' => 'The Flower',
				'class' => 'stub-flower'
			]
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
	 * @covers \Windwalker\Form\Field\AbstractField::renderInput
	 */
	public function testRenderInput()
	{
		$expect = <<<HTML
<input type="text" name="flower" id="input-flower" class="stub-flower" />
HTML;

		$this->assertHtmlFormatEquals($expect, $this->instance->renderInput());

		// Control
		$this->instance->setControl('windwalker');

		$expect = <<<HTML
<input type="text" name="windwalker[flower]" id="input-windwalker-flower" class="stub-flower" />
HTML;

		$this->assertHtmlFormatEquals($expect, $this->instance->renderInput());

		// Default value
		$this->instance->setAttribute('default', 'default-value');

		$expect = <<<HTML
<input type="text" name="windwalker[flower]" id="input-windwalker-flower" class="stub-flower" value="default-value" />
HTML;

		$this->assertHtmlFormatEquals($expect, $this->instance->renderInput());

		// Value
		$this->instance->setValue('sakura');

		$expect = <<<HTML
<input type="text" name="windwalker[flower]" id="input-windwalker-flower" class="stub-flower" value="sakura" />
HTML;

		$this->assertHtmlFormatEquals($expect, $this->instance->renderInput());

		// Group
		$this->instance->setGroup('foo/bar');

		$expect = <<<HTML
<input type="text" name="windwalker[foo][bar][flower]" id="input-windwalker-foo-bar-flower" class="stub-flower" value="sakura" />
HTML;

		$this->assertHtmlFormatEquals($expect, $this->instance->renderInput());
	}

	/**
	 * testCreateByXml
	 *
	 * @return  void
	 */
	public function testCreateByXml()
	{
		$xml = <<<XML
<field
	name="flower"
	type="stub"
	label="Flower"
	description=""
	class="stub-flower"
	default="default-value"
	/>
XML;

		$field = new StubField(simplexml_load_string($xml));

		$expect = <<<HTML
<input type="text" name="flower" id="input-flower" class="stub-flower" value="default-value" />
HTML;

		$this->assertHtmlFormatEquals($expect, $field->renderInput());
	}

	/**
	 * Method to test buildInput().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::buildInput
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
	 * @covers \Windwalker\Form\Field\AbstractField::renderLabel
	 */
	public function testRenderLabel()
	{
		$expect = <<<HTML
<label id="input-flower-label" for="input-flower">Flower</label>
HTML;

		$this->assertHtmlFormatEquals($expect, $this->instance->renderLabel());
	}

	/**
	 * Method to test renderView().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::renderView
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
	 * @covers \Windwalker\Form\Field\AbstractField::render
	 */
	public function testRender()
	{
		$this->instance->setControl('windwalker');
		$this->instance->setValue('sakura');

		$this->instance->setAttribute('controlClass', 'control-group');
		$this->instance->setAttribute('attribs', ['data-test-element' => true]);

		$expect = <<<HTML
<div id="input-windwalker-flower-control" class="stub-field control-group">
	<label id="input-windwalker-flower-label" for="input-windwalker-flower">Flower</label>
	<input type="text" name="windwalker[flower]" id="input-windwalker-flower" class="stub-flower" value="sakura" data-test-element />
</div>
HTML;

		$this->assertHtmlFormatEquals($expect, $this->instance->render());
	}

	/**
	 * Method to test getLabel().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::getLabel
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
	 * @covers \Windwalker\Form\Field\AbstractField::getId
	 */
	public function testGetId()
	{
		$this->assertEquals('input-flower', $this->instance->getId());

		$this->instance->setControl('windwalker');

		$this->assertEquals('input-windwalker-flower', $this->instance->getId());

		$this->instance->setName('a.b-c_d:e');

		$this->assertEquals('input-windwalker-a-b-c_d-e', $this->instance->getId());
	}

	/**
	 * Method to test validate().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::validate
	 */
	public function testValidate()
	{
		$field = new StubField(
			'flower',
			'Flower',
			[
				'placeholder' => 'The Flower',
				'class' => 'stub-flower'
			],
			null,
			new IpValidator
		);

		// No value will not validate
		$this->assertTrue($field->validate()->isSuccess());

		// Do validate
		$this->assertTrue($field->setValue('123.21.23.156')->validate()->isSuccess());

		$this->assertFalse($field->setValue('/var/foo/bar')->validate()->isSuccess());
		$this->assertEquals(ValidateResult::STATUS_FAILURE, $field->setValue('/var/foo/bar')->validate()->getResult());
	}

	/**
	 * Method to test checkRequired().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::checkRequired
	 */
	public function testCheckRequired()
	{
		$field = new StubField(
			'flower',
			'Flower',
			[
				'placeholder' => 'The Flower',
				'class' => 'stub-flower',
				'required' => true
			]
		);

		$this->assertFalse($field->validate()->isSuccess());
		$this->assertEquals(ValidateResult::STATUS_REQUIRED, $field->validate()->getResult());

		$field->setValue('123');

		$this->assertTrue($field->validate()->isSuccess());
	}

	/**
	 * Method to test checkRule().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::checkRule
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
	 * @covers \Windwalker\Form\Field\AbstractField::filter
	 */
	public function testFilter()
	{
		$field = new StubField(
			'flower',
			'Flower',
			[
				'placeholder' => 'The Flower',
				'class' => 'stub-flower'
			],
			InputFilter::CMD
		);

		$field->setValue('abc foo_bar-yoo<div>data</div>456:789');

		$this->assertEquals('abcfoo_bar-yoodivdatadiv456789', $field->filter()->getValue());

		$field->setFilter(new MockFilter)->setValue('foo');

		$this->assertEquals('abc', $field->filter()->getValue());
	}

	/**
	 * Method to test getName().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::getName
	 */
	public function testGetName()
	{
		$this->instance->setControl('windwalker');
		$this->instance->setValue('sakura');

		$this->assertEquals('flower', $this->instance->getName());
	}

	/**
	 * Method to test setName().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::setName
	 */
	public function testSetName()
	{
		$this->instance->setControl('windwalker');
		$this->instance->setGroup('goo');
		$this->instance->setName('yoo');

		$this->assertEquals('input-windwalker-goo-yoo', $this->instance->getId());
	}

	/**
	 * Method to test getFieldName().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::getFieldName
	 */
	public function testGetFieldName()
	{
		$this->instance->setControl('windwalker');

		$this->assertEquals('windwalker[flower]', $this->instance->getFieldName());

		$this->instance->setGroup('foo/bar');
		$this->instance->setName('yoo');

		$this->assertEquals('windwalker[foo][bar][yoo]', $this->instance->getFieldName(true));
	}

	/**
	 * Method to test setFieldName().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::setFieldName
	 */
	public function testSetFieldName()
	{
		$this->instance->setFieldName('foo[bar]');

		$this->assertEquals('foo[bar]', $this->instance->getFieldName());
	}

	/**
	 * Method to test getGroup().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::getGroup
	 */
	public function testGetAbdSetGroup()
	{
		$this->instance->setGroup('wind');

		$expect = <<<HTML
<input type="text" name="wind[flower]" id="input-wind-flower" class="stub-flower" />
HTML;

		$this->assertHtmlFormatEquals($expect, $this->instance->renderInput());

		$this->instance->setGroup('wind/walker');

		$expect = <<<HTML
<input type="text" name="wind[walker][flower]" id="input-wind-walker-flower" class="stub-flower" />
HTML;

		$this->assertHtmlFormatEquals($expect, $this->instance->renderInput());
	}

	/**
	 * Method to test getFieldset().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::getFieldset
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
	 * @covers \Windwalker\Form\Field\AbstractField::setFieldset
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
	 * @covers \Windwalker\Form\Field\AbstractField::getValue
	 */
	public function testGetAndSetValue()
	{
		// Test default value
		$this->instance->setAttribute('default', 'joo');

		$this->assertEquals('joo', $this->instance->getValue());

		// Test set value
		$this->instance->setValue('Sakura');

		$this->assertEquals('Sakura', $this->instance->getValue());
	}

	/**
	 * Method to test setValidator().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::setValidator
	 */
	public function testSetValidator()
	{
		$this->instance->setValidator(new EmailValidator);

		$this->assertInstanceOf('Windwalker\\Validator\\Rule\\EmailValidator', $this->instance->getValidator());
	}

	/**
	 * Method to test setFilter().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::setFilter
	 */
	public function testGetAndSetFilter()
	{
		// Set filter type
		$this->instance->setFilter(InputFilter::INTEGER);

		$this->assertEquals('123', $this->instance->setValue('abc123cba')->filter()->getValue());

		// Set filter handler
		$closure = function($value)
		{
			return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
		};

		$this->instance->setFilter($closure);

		$this->assertEquals('123123', $this->instance->setValue('abc123cba123fgh')->filter()->getValue());

		// Set filter object
		$this->instance->setFilter(new StubFilter);

		$this->assertEquals('123123', $this->instance->setValue('abc123cba123fgh')->filter()->getValue());
	}

	/**
	 * Method to test getControl().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::getControl
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
	 * @covers \Windwalker\Form\Field\AbstractField::setControl
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
	 * @covers \Windwalker\Form\Field\AbstractField::getAttribute
	 */
	public function testGetAttribute()
	{
		$field = new StubField(
			'flower',
			'Flower',
			[
				'placeholder' => 'The Flower',
				'class' => 'stub-flower',
				'required' => true
			]
		);

		$this->assertEquals('stub-flower', $field->getAttribute('class'));
		$this->assertEquals('default', $field->getAttribute('host', 'default'));
		$this->assertEquals(null, $field->getAttribute('host'));
	}

	/**
	 * Method to test setAttribute().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::setAttribute
	 */
	public function testSetAttribute()
	{
		$this->instance->setAttribute('host', 'localhost');

		$this->assertEquals('localhost', $this->instance->getAttribute('host', 'default'));
	}

	/**
	 * Method to test get().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::get
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
	 * @covers \Windwalker\Form\Field\AbstractField::getBool
	 */
	public function testGetBool()
	{
		$field = new StubField(
			'flower',
			'Flower',
			[
				'placeholder' => 'The Flower',
				'class' => 'stub-flower',
				'required' => 'true',
				'disabled' => true,
				'case1' => 'yes',
				'case2' => '1',
				'false' => 'false'
			]
		);

		$this->assertTrue($field->getBool('required'));
		$this->assertTrue($field->getBool('disabled'));
		$this->assertTrue($field->getBool('case1'));
		$this->assertTrue($field->getBool('case2'));
		$this->assertFalse($field->getBool('false'));
	}

	/**
	 * Method to test getFalse().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::getFalse
	 */
	public function testGetFalse()
	{
		$field = new StubField(
			'flower',
			'Flower',
			[
				'placeholder' => 'The Flower',
				'class' => 'stub-flower',
				'required' => 'false',
				'disabled' => false,
				'case1' => 'no',
				'case2' => 'none',
				'case3' => '0',
				'true' => 'yes',
			]
		);

		$this->assertFalse($field->getBool('required'));
		$this->assertFalse($field->getBool('disabled'));
		$this->assertFalse($field->getBool('case1'));
		$this->assertFalse($field->getBool('case2'));
		$this->assertFalse($field->getBool('case3'));
		$this->assertTrue($field->getBool('true'));
	}

	/**
	 * Method to test getAttributes().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::getAttributes
	 */
	public function testGetAttributes()
	{
		$this->assertEquals(['placeholder' => 'The Flower', 'class' => 'stub-flower'], $this->instance->getAttributes());
	}

	/**
	 * Method to test def().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\Field\AbstractField::def
	 */
	public function testDef()
	{
		$this->instance->def('foo', 'bar');

		$this->assertEquals('bar', $this->instance->getAttribute('foo'));

		$this->instance->def('class', 'bar');

		$this->assertEquals('stub-flower', $this->instance->getAttribute('class'));
	}
}

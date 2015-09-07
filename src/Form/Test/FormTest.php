<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Test;

use Windwalker\Form\Field\AbstractField;
use Windwalker\Form\Field\TextField;
use Windwalker\Form\FieldHelper;
use Windwalker\Form\FilterHelper;
use Windwalker\Form\Form;
use Windwalker\Form\Test\Stub\StubFieldDefinition;
use Windwalker\Form\ValidatorHelper;

/**
 * Test class of Form
 *
 * @since 2.0
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Form
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
	 * getByDefine
	 *
	 * @param string $control
	 *
	 * @return  Form
	 *
	 * @covers  Windwalker\Form\Form::defineFormFields
	 */
	protected function getByDefine($control = null)
	{
		$form = new Form($control);

		$form->defineFormFields(new StubFieldDefinition);

		return $form;
	}

	/**
	 * getByDefine
	 *
	 * @param string $control
	 *
	 * @return  Form
	 *
	 * @covers  Windwalker\Form\Form::loadFile
	 */
	protected function getByXml($control = null)
	{
		$form = new Form($control);

		$form->loadFile(__DIR__ . '/Stub/fields.xml');

		return $form;
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
	 * Method to test load().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::loadXml
	 */
	public function testLoadXml()
	{
		$form = $this->getByXml();

		$fields = $form->getFields();

		$defineForm = $this->getByDefine();

		// Group structure
		$this->assertEquals(array_keys($fields), array_keys($defineForm->getFields()));

		$this->assertEquals('u[username]', $fields['u/username']->getFieldName());
	}

	/**
	 * Method to test addFields().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::addFields
	 */
	public function testAddAndGetFields()
	{
		$form = new Form;

		$form->addFields(
			array(
				new TextField('foo'),
				new TextField('bar'),
			)
		);

		$fields = $form->getFields();

		$this->assertInstanceOf('Windwalker\\Form\\Field\\TextField', $fields['foo']);
		$this->assertEquals('bar', $fields['bar']->getName());

		// Test fieldset
		$form->addFields(
			array(
				new TextField('bird'),
				new TextField('rabbit'),
			),
			'flower'
		);

		$fields = $form->getFields('flower');

		$this->assertInstanceOf('Windwalker\\Form\\Field\\TextField', $fields['bird']);
		$this->assertEquals('rabbit', $fields['rabbit']->getFieldName());

		// Test Group
		$form->addFields(
			array(
				new TextField('egg'),
				new TextField('hotdog'),
			),
			'rose',
			'sakura'
		);

		$fields = $form->getFields(null, 'sakura');

		$this->assertInstanceOf('Windwalker\\Form\\Field\\TextField', $fields['sakura/egg']);
		$this->assertEquals('sakura[hotdog]', $fields['sakura/hotdog']->getFieldName());
	}

	/**
	 * Method to test addField().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::addField
	 */
	public function testAddAndGetField()
	{
		$form = new Form;

		$form->addField(new TextField('foo'));

		$this->assertEquals('foo', $form->getField('foo')->getFieldname());

		$form->addField(new TextField('bar'), 'flower');

		$this->assertEquals('bar', $form->getField('bar')->getFieldname());

		$form->addField(new TextField('yoo'), null, 'sakura');

		$this->assertEquals('sakura[yoo]', $form->getField('yoo', 'sakura')->getFieldname());
	}

	/**
	 * Method to test addFieldNamespace().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::addFieldNamespace
	 */
	public function testAddFieldNamespace()
	{
		FieldHelper::reset();

		$form = new Form;

		$form->addFieldNamespace('TestNS');

		$ns = FieldHelper::getNamespaces();

		$ns = iterator_to_array($ns);

		$this->assertEquals('TestNS', $ns[0]);
	}

	/**
	 * Method to test addFilterNamespace().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::addFilterNamespace
	 */
	public function testAddFilterNamespace()
	{
		FilterHelper::reset();

		$form = new Form;

		$form->addFilterNamespace('TestNS');

		$ns = FilterHelper::getNamespaces();

		$ns = iterator_to_array($ns);

		$this->assertEquals('TestNS', $ns[0]);
	}

	/**
	 * Method to test addValidatorNamespace().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::addValidatorNamespace
	 */
	public function testAddValidatorNamespace()
	{
		ValidatorHelper::reset();

		$form = new Form;

		$form->addValidatorNamespace('TestNS');

		$ns = ValidatorHelper::getNamespaces();

		$ns = iterator_to_array($ns);

		$this->assertEquals('TestNS', $ns[0]);
	}

	/**
	 * Method to test getIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::getIterator
	 */
	public function testGetIterator()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getCallbackIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::getCallbackIterator
	 * @TODO   Implement testGetCallbackIterator().
	 */
	public function testGetCallbackIterator()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test removeField().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::removeField
	 */
	public function testRemoveField()
	{
		$form = $this->getByDefine();

		$form->removeField('username', 'u');

		$this->assertNull($form->getField('username', 'u'));
	}

	/**
	 * Method to test removeFields().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::removeFields
	 */
	public function testRemoveFields()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getFieldsets().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::getFieldsets
	 * @TODO   Implement testGetFieldsets().
	 */
	public function testGetFieldsets()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getGroups().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::getGroups
	 * @TODO   Implement testGetGroups().
	 */
	public function testGetGroups()
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
	 * @covers Windwalker\Form\Form::setAttribute
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
	 * Method to test getAttribute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::getAttribute
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
	 * Method to test bind().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::bind
	 */
	public function testBind()
	{
		$data = array(
			'id' => 5,
			'u' => array(
				'username' => 'foo'
			)
		);

		$form = $this->getByDefine();

		$fields = $form->bind($data)->getFields();

		$this->assertEquals(5, $fields['id']->getValue());
		$this->assertEquals('foo', $fields['u/username']->getValue());
	}

	/**
	 * Method to test validate().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::validate
	 */
	public function testValidate()
	{
		$form = $this->getByDefine();

		$data = array(
			'id' => 5,
			'u' => array(
				'username' => 'foo'
			),
			'b' => array(
				'email' => 'bar@gmail.com',
				'password' => '123_ abc4456qwe:$yui'
			)
		);

		$form->bind($data);

		$result = $form->filter()->validate();

		$this->assertTrue($result);

		$data = array(
			'id' => 5,
			'u' => array(
				'username' => 'foo'
			),
			'b' => array(
				'email' => 'bar/gmail.com',
				'password' => '123_ abc4456qwe:$yui'
			)
		);

		$form->bind($data);

		$result = $form->filter()->validate();
		$errors = $form->getErrors();

		$this->assertFalse($result);
		$this->assertEquals('Field Email validate fail.', $errors[0]->getMessage());

		// Filter ALNUM
		$this->assertEquals('123abc4456qweyui', $form->getField('password', 'b')->getValue());
	}

	/**
	 * Method to test prepareView().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::prepareView
	 */
	public function testGetViews()
	{
		$form = $this->getByDefine();

		$data = array(
			'id' => 5,
			'u' => array(
				'username' => 'foo'
			),
			'b' => array(
				'email' => 'bar/gmail.com',
				'password' => '123_ abc4456qwe:$yui'
			)
		);

		$form->bind($data);

		$array = $form->getViews();

		$this->assertEquals('123_ abc4456qwe:$yui', $array['b/password']['value']);
	}

	/**
	 * Method to test prepareStore().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::prepareStore
	 */
	public function testPrepareStore()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test renderField().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::renderField
	 *
	 * @since  2.1.2
	 */
	public function testRenderField()
	{
		$form = $this->getByDefine('windwalker');

		$html = '<div id="input-windwalker-id-control" class="text-field "><label id="input-windwalker-id-label" for="input-windwalker-id">ID</label><input type="text" name="windwalker[id]" id="input-windwalker-id" class="control-input" /></div>';

		$this->assertEquals($html, $form->renderField('id'));

		// Use renderer

		$form->setFieldRenderHandler(function(AbstractField $field, $form)
		{
			return 'Happy Field: ' . $field->getName();
		});

		$this->assertEquals('Happy Field: id', $form->renderField('id'));
	}

	/**
	 * Method to test renderFields().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Form::renderFields
	 * @TODO   Implement testRenderFields().
	 */
	public function testRenderFields()
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
	 * @covers Windwalker\Form\Form::getControl
	 */
	public function testGetAndSetControl()
	{
		$form = $this->getByDefine();

		$form->setControl('wind');

		$this->assertEquals('wind[id]', $form->getField('id')->getFieldname());
		$this->assertEquals('wind', $form->getControl());
	}

	/**
	 * testFilterAndGetValues
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Form\Form::filter
	 * @covers Windwalker\Form\Form::getValues
	 */
	public function testFilterAndGetValues()
	{
		$form = $this->getByDefine();

		$data = array(
			'id' => '123abc',
			'u' => array(
				'username' => 'foo'
			),
			'b' => array(
				'email' => 'bar@gmail.com',
				'password' => '123_ abc/\4,456qwe:$yui'
			)
		);

		$form->bind($data);

		$form->filter();

		$values = $form->getValues();

		$this->assertEquals('123', $values['id']);
		$this->assertEquals('123abc4456qweyui', $values['b']['password']);
	}
}

<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Test\Stub;

use Windwalker\Filter\InputFilter;
use Windwalker\Form\Field\PasswordField;
use Windwalker\Form\Field\TextField;
use Windwalker\Form\FieldDefinitionInterface;
use Windwalker\Form\Form;
use Windwalker\Validator\Rule\EmailValidator;

/**
 * The StubFieldDefinition class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class StubFieldDefinition implements FieldDefinitionInterface
{
	/**
	 * Define the form fields.
	 *
	 * @param Form $form The Windwalker form object.
	 *
	 * @return  void
	 */
	public function define(Form $form)
	{
		$form->addField(
			new TextField(
				'id',
				'ID',
				array(
					'class' => 'control-input'
				),
				InputFilter::INTEGER
			),
			'a',
			null
		)->addField(
			new TextField(
				'username',
				'Username',
				array(
					'class' => 'control-input',
					'required' => true
				)
			),
			'a',
			'u'
		)->addField(
			new TextField(
				'email',
				'Email',
				array(
					'required' => true
				),
				null,
				new EmailValidator
			),
			null,
			'b'
		)->addField(
			new PasswordField(
				'password',
				'Password',
				null,
				InputFilter::ALNUM
			),
			'pf',
			'b'
		)->addField(
			new TextField(
				'address',
				'Address'
			)
		);
	}
}

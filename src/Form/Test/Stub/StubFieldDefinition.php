<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
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
 * @since  2.0
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
		$form->addField(new TextField('id', 'ID'), 'a', null)
			->set('class', 'control-input')
			->setFilter(InputFilter::INTEGER);

		$form->addField(new TextField('username', 'Username'), 'a', 'u')
			->required()
			->set('class', 'control-input');

		$form->addField(new TextField('email', 'Email'), null, 'b')
			->required()
			->setValidator(new EmailValidator)
			->set('class', 'control-input');

		$form->addField(new PasswordField('password', 'Password'), 'pf', 'b')
			->setFilter(InputFilter::ALNUM);

		$form->addField(new TextField('address', 'Address'));
	}
}

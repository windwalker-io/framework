<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

include_once __DIR__ . '/../../../../vendor/autoload.php';

use Windwalker\Form\Field\ListField;
use Windwalker\Form\Field\TextareaField;
use Windwalker\Form\Form;
use Windwalker\Form\Field\TextField;
use Windwalker\Form\Field\PasswordField;
use Windwalker\Html\Option;
use Windwalker\Validator\Rule\EmailValidator;

$form = new Form;

$form->addField(new TextField('username', 'Username'));
$form->addField(new PasswordField('password', 'Password'));
$form->addField(new TextField('email', 'Email'));
$form->addField(new TextareaField('description', 'Description'));

echo $form->renderFields();

$field = new ListField(
	'flower',
	'Flower',
	array(
		new Option('', ''),
		new Option(1, 'Yes'),
		new Option(0, 'No'),
	),
	array(
		'class' => 'stub-flower'
	)
);

echo $field->render();

\Windwalker\Form\FilterHelper::addNamespace();

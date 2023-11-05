<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Stub;

use Windwalker\Filter\Rule\CastTo;
use Windwalker\Filter\Rule\EmailAddress;
use Windwalker\Form\Attributes\Fieldset;
use Windwalker\Form\Attributes\FormDefine;
use Windwalker\Form\Attributes\NS;
use Windwalker\Form\Field\PasswordField;
use Windwalker\Form\Field\TextField;
use Windwalker\Form\Form;

/**
 * The StubAttrDefinition class.
 */
class StubAttrDefinition
{
    #[FormDefine]
    #[Fieldset('basic')]
    public function basic(Form $form)
    {
        $form->addField(new TextField('id', 'ID'))
            ->addWrapperClass('control-input')
            ->addFilter(new CastTo('int'));

        $form->addField(new TextField('u/username', 'Username'))
            ->required(true)
            ->addClass('control-input')
            ->defaultValue('Admin');
    }

    #[FormDefine]
    #[Fieldset('user', 'User')]
    #[NS('user')]
    public function user(Form $form): void
    {
        $form->addField(new TextField('email', 'Email'))
            ->required(true)
            ->addValidator(EmailAddress::class)
            ->addClass('control-input');

        $form->addField(new PasswordField('password', 'Password'))
            ->addFilter('alnum');
    }

    #[FormDefine(ordering: 1)]
    #[Fieldset('meta')]
    public function meta(Form $form): void
    {
        $form->addField(new TextField('address', 'Address'))
            ->defaultValue('Default Address');
    }
}

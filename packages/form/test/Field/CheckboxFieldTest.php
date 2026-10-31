<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Form\Field\CheckboxField;

class CheckboxFieldTest extends TestCase
{
    #[Test]
    public function uncheckedByDefaultWithOnValue(): void
    {
        $html = (new CheckboxField('agree'))->renderInput();

        self::assertStringContainsString('type="checkbox"', $html);
        self::assertStringContainsString('value="on"', $html);
        self::assertStringNotContainsString('checked', $html);
    }

    #[Test]
    public function truthyValueIsChecked(): void
    {
        $field = new CheckboxField('agree');
        $field->setValue(1);

        self::assertStringContainsString('checked', $field->renderInput());
    }

    #[Test]
    public function customCheckedValue(): void
    {
        $field = new CheckboxField('agree');
        $field->checkedValue('yes');

        self::assertStringContainsString('value="yes"', $field->renderInput());
    }
}

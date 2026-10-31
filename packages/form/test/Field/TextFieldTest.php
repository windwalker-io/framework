<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Form\Field\TextField;

class TextFieldTest extends TestCase
{
    private TextField $instance;

    #[Before]
    protected function createTextField(): void
    {
        $this->instance = new TextField('flower', 'Flower');
        $this->instance->setAttribute('id', 'test-field');
    }

    #[Test]
    public function renderInputWithoutOptionsHasNoList(): void
    {
        $html = $this->instance->setValue('sakura')->renderInput();

        self::assertStringContainsString('type="text"', $html);
        self::assertStringContainsString('value="sakura"', $html);
        self::assertStringNotContainsString('list=', $html);
        self::assertStringNotContainsString('datalist', $html);
        self::assertNull($this->instance->buildListId());
    }

    #[Test]
    public function optionsRenderDatalist(): void
    {
        $this->instance->option('Rose')->option('Sakura', 'sakura');

        $html = $this->instance->renderInput();

        self::assertSame('test-field-list', $this->instance->buildListId());
        self::assertStringContainsString('list="test-field-list"', $html);
        self::assertStringContainsString('<datalist id="test-field-list">', $html);
        self::assertStringContainsString('value="Rose"', $html);
        self::assertStringContainsString('value="sakura"', $html);
    }

    #[Test]
    public function groupedOptionsRenderOptgroup(): void
    {
        $this->instance->option('Rose', null, [], 'Flowers');

        $html = $this->instance->renderInput();

        self::assertStringContainsString('<optgroup label="Flowers">', $html);
        self::assertStringContainsString('value="Rose"', $html);
    }

    #[Test]
    public function customListAttributeIsKept(): void
    {
        $this->instance->setAttribute('list', 'custom');

        self::assertStringContainsString('list="custom"', $this->instance->renderInput());
    }

    #[Test]
    public function maxlengthSetsAttributeAndFilter(): void
    {
        $this->instance->maxlength(5);

        self::assertStringContainsString('maxlength="5"', $this->instance->renderInput());
        self::assertSame('abcde', $this->instance->filter('abcdefgh'));
    }

    #[Test]
    public function maxlengthWithoutFilterOnlySetsAttribute(): void
    {
        $this->instance->maxlength(5, false);

        self::assertSame('abcdefgh', $this->instance->filter('abcdefgh'));
    }

    #[Test]
    public function valueIsCastToString(): void
    {
        $this->instance->setValue(123);

        self::assertSame('123', $this->instance->getValue());
    }
}

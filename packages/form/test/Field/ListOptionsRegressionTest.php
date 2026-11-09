<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\HTMLElement;
use Windwalker\Form\Field\ListField;
use Windwalker\Form\Field\RadioField;
use Windwalker\Form\Field\TextField;

/**
 * Regression tests for list option registration, surround and string options.
 */
class ListOptionsRegressionTest extends TestCase
{
    #[Test]
    public function registerOptionsBackedEnumUsesNameAsLabelAndValueAsValue(): void
    {
        $field = new ListField('color');
        $field->registerOptions(ListOptionsRegressionColor::class);

        $options = $field->getOptions();

        self::assertCount(2, $options);
        self::assertSame('RED', (string) $options[0]->textContent);
        self::assertSame('r', $options[0]->getAttribute('value'));
        self::assertSame('GREEN', (string) $options[1]->textContent);
        self::assertSame('g', $options[1]->getAttribute('value'));
    }

    #[Test]
    public function surroundDoesNotDuplicateInputOnRepeatedRender(): void
    {
        $field = new TextField('title');
        $field->surround(HTMLElement::new('div', ['class' => 'wrap']));

        $first = (string) $field->render();
        $second = (string) $field->render();

        self::assertSame(1, substr_count($first, '<input'));
        self::assertSame(1, substr_count($second, '<input'));
    }

    #[Test]
    public function addOptionsAcceptsStrings(): void
    {
        $field = new RadioField('flower');
        $field->addOptions(['Asia/Tokyo', 'UTC']);

        $html = (string) $field->render();

        self::assertSame(2, substr_count($html, 'type="radio"'));
        self::assertStringContainsString('value="UTC"', $html);
    }
}

enum ListOptionsRegressionColor: string
{
    case RED = 'r';
    case GREEN = 'g';
}

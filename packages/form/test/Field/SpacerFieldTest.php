<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Form\Field\SpacerField;

class SpacerFieldTest extends TestCase
{
    #[Test]
    public function rendersSpanWithDescriptionByDefault(): void
    {
        $field = new SpacerField('space');
        $field->setAttribute('description', 'Hello');

        $html = $field->renderInput();

        self::assertStringContainsString('<span', $html);
        self::assertStringContainsString('Hello', $html);
    }

    #[Test]
    public function rendersHrWhenEnabled(): void
    {
        $field = new SpacerField('space');
        $field->hr(true);

        self::assertStringContainsString('<hr', $field->renderInput());
    }

    #[Test]
    public function rendersCustomTag(): void
    {
        $field = new SpacerField('space');
        $field->tag('h3');
        $field->setAttribute('description', 'Title');

        self::assertStringContainsString('<h3', $field->renderInput());
    }
}

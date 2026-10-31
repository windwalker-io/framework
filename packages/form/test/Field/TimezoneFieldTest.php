<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Form\Field\TimezoneField;

class TimezoneFieldTest extends TestCase
{
    #[Test]
    public function rendersTimezonesGroupedByRegion(): void
    {
        $field = new TimezoneField('tz');
        $field->setValue('Asia/Taipei');

        $html = $field->renderInput();

        self::assertStringContainsString('<optgroup label="Asia">', $html);
        self::assertStringContainsString('<option value="Asia/Taipei" selected', $html);
        self::assertStringContainsString('<option value="UTC"', $html);
    }
}

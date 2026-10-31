<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Form\Field\CustomHtmlField;
use Windwalker\Test\Traits\DOMTestTrait;

use function Windwalker\DOM\h;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class CustomHtmlTest extends TestCase
{
    use DOMTestTrait;

    protected CustomHtmlField $instance;

    #[Before]
    protected function createCustomHtmlField(): void
    {
        $this->instance = new CustomHtmlField(
            'flower',
            'Flower'
        );

        $this->instance->content(h('div', ['data-test-element' => true], 'Sakura'));
    }

    #[Test]
    public function renderInputOutputsCustomContent(): void
    {
        $html = <<<HTML
<div data-test-element>Sakura</div>
HTML;

        self::assertHtmlFormatEquals($html, $this->instance->renderInput());
    }
}

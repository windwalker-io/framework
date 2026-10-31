<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Form\Field\ButtonField;
use Windwalker\Form\Field\TextField;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class ButtonFieldTest extends TestCase
{
    use BaseAssertionTrait;
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var TextField
     */
    protected $instance;

    #[Before]
    protected function createButtonField(): void
    {
        $this->instance = new ButtonField(
            'flower',
            'Flower',
            [
                'class' => 'stub-flower',
            ]
        );

        $this->instance->setAttribute('id', 'test-field');
        $this->instance->setAttribute('readonly', true);
        $this->instance->setAttribute('disabled', true);
        $this->instance->setAttribute('data-test-element', true);
        $this->instance->setValue('Sakura');
    }

    #[Test]
    public function renderInputOutputsButtonElementWithAttributes(): void
    {
        // phpcs:disable
        $html = <<<HTML
<button id="test-field" name="flower" class="stub-flower" data-field-input data-test-element disabled readonly type="button">Sakura</button>
HTML;

        // phpcs:enable
        self::assertHtmlFormatEquals($html, $this->instance->renderInput());
    }
}

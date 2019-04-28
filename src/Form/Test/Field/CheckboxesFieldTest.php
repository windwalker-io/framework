<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Test\Field;

use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Form\Field\CheckboxesField;
use Windwalker\Html\Option;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class CheckboxesFieldTest extends AbstractDomTestCase
{
    /**
     * Test instance.
     *
     * @var CheckboxesField
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new CheckboxesField(
            'flower',
            'Flower',
            [
                new Option('Asia - Tokyo', 'Asia/Tokyo', ['class' => 'opt']),
                new Option('Asia - Taipei', 'Asia/Taipei'),
                new Option('Europe - Paris', 'Asia/Paris'),
                new Option('UTC', 'UTC'),
            ],
            [
                'class' => 'stub-flower',
            ]
        );

        $this->instance->setAttribute('size', 10);
        $this->instance->setAttribute('readonly', false);
        $this->instance->setAttribute('disabled', true);
        $this->instance->setAttribute('onchange', 'return false;');
        $this->instance->setAttribute('multiple', false);
        $this->instance->setAttribute('attribs', ['data-test-element' => true]);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * Method to test prepareAttributes().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\TextField::prepareAttributes
     */
    public function testRender()
    {
        $html = <<<HTML
<span id="input-flower" class="checkbox-inputs stub-flower" data-test-element>
    <input class="opt" value="Asia/Tokyo" name="flower[]" type="checkbox" id="input-flower-asia-tokyo" disabled="disabled" />
    <label class="opt" id="input-flower-asia-tokyo-label" for="input-flower-asia-tokyo">Asia - Tokyo</label>

    <input value="Asia/Taipei" name="flower[]" type="checkbox" id="input-flower-asia-taipei" disabled="disabled" />
    <label id="input-flower-asia-taipei-label" for="input-flower-asia-taipei">Asia - Taipei</label>

    <input value="Asia/Paris" name="flower[]" type="checkbox" id="input-flower-asia-paris" disabled="disabled" />
    <label id="input-flower-asia-paris-label" for="input-flower-asia-paris">Europe - Paris</label>

    <input value="UTC" name="flower[]" type="checkbox" id="input-flower-utc" disabled="disabled" />
    <label id="input-flower-utc-label" for="input-flower-utc">UTC</label>
</span>
HTML;

        $this->assertHtmlFormatEquals($html, $this->instance->renderInput());

        $this->instance->setValue('UTC');

        $html = <<<HTML
<span id="input-flower" class="checkbox-inputs stub-flower" data-test-element>
    <input class="opt" value="Asia/Tokyo" name="flower[]" type="checkbox" id="input-flower-asia-tokyo" disabled="disabled" />
    <label class="opt" id="input-flower-asia-tokyo-label" for="input-flower-asia-tokyo">Asia - Tokyo</label>

    <input value="Asia/Taipei" name="flower[]" type="checkbox" id="input-flower-asia-taipei" disabled="disabled" />
    <label id="input-flower-asia-taipei-label" for="input-flower-asia-taipei">Asia - Taipei</label>

    <input value="Asia/Paris" name="flower[]" type="checkbox" id="input-flower-asia-paris" disabled="disabled" />
    <label id="input-flower-asia-paris-label" for="input-flower-asia-paris">Europe - Paris</label>

    <input value="UTC" name="flower[]" checked="checked" type="checkbox" id="input-flower-utc" disabled="disabled" />
    <label id="input-flower-utc-label" for="input-flower-utc">UTC</label>
</span>
HTML;

        $this->assertHtmlFormatEquals($html, $this->instance->renderInput());
    }
}

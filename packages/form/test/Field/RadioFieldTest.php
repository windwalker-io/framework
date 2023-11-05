<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\TestCase;
use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Form\Field\RadioField;
use Windwalker\Html\Option;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class RadioFieldTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var RadioField
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
        $this->instance = new RadioField(
            'flower',
            'Flower',
            [
                'class' => 'stub-flower',
            ]
        );

        $this->instance->addOptions(
            [
                RadioField::createOption('Asia - Tokyo', 'Asia/Tokyo', ['class' => 'opt']),
                RadioField::createOption('Asia - Taipei', 'Asia/Taipei'),
                RadioField::createOption('Europe - Paris', 'Asia/Paris'),
                RadioField::createOption('UTC', 'UTC'),
            ]
        );

        $this->instance->setAttribute('size', 10);
        $this->instance->setAttribute('readonly', false);
        $this->instance->setAttribute('disabled', true);
        $this->instance->setAttribute('onchange', 'return false;');
        $this->instance->setAttribute('multiple', false);
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
     */
    public function testRender()
    {
        // phpcs:disable
        $html = <<<HTML
<div id="input-flower" class="stub-flower" data-field-input disabled onchange="return false;" size="10">
    <div id="input-flower-Asia-Tokyo-item" class="radio" data-radio-item-wrapper>
        <input id="input-flower-Asia-Tokyo" value="Asia/Tokyo" name="flower" class="opt" data-radio-item-input type="radio">
        <label for="input-flower-Asia-Tokyo" id="input-flower-Asia-Tokyo-label" data-radio-item-label>Asia - Tokyo</label>
    </div>
    <div id="input-flower-Asia-Taipei-item" class="radio" data-radio-item-wrapper>
        <input id="input-flower-Asia-Taipei" value="Asia/Taipei" name="flower" data-radio-item-input type="radio">
        <label for="input-flower-Asia-Taipei" id="input-flower-Asia-Taipei-label" data-radio-item-label>Asia - Taipei</label>
    </div>
    <div id="input-flower-Asia-Paris-item" class="radio" data-radio-item-wrapper>
        <input id="input-flower-Asia-Paris" value="Asia/Paris" name="flower" data-radio-item-input type="radio">
        <label for="input-flower-Asia-Paris" id="input-flower-Asia-Paris-label" data-radio-item-label>Europe - Paris</label>
    </div>
    <div id="input-flower-UTC-item" class="radio" data-radio-item-wrapper>
        <input id="input-flower-UTC" value="UTC" name="flower" data-radio-item-input type="radio">
        <label for="input-flower-UTC" id="input-flower-UTC-label" data-radio-item-label>UTC</label>
    </div>
</div>
HTML;

        self::assertHtmlFormatEquals($html, $this->instance->renderInput());

        $this->instance->setValue('UTC');

        $html = <<<HTML
<div id="input-flower" class="stub-flower" data-field-input disabled onchange="return false;" size="10">
    <div id="input-flower-Asia-Tokyo-item" class="radio" data-radio-item-wrapper>
        <input id="input-flower-Asia-Tokyo" value="Asia/Tokyo" name="flower" class="opt" data-radio-item-input type="radio">
        <label for="input-flower-Asia-Tokyo" id="input-flower-Asia-Tokyo-label" data-radio-item-label>Asia - Tokyo</label>
    </div>
    <div id="input-flower-Asia-Taipei-item" class="radio" data-radio-item-wrapper>
        <input id="input-flower-Asia-Taipei" value="Asia/Taipei" name="flower" data-radio-item-input type="radio">
        <label for="input-flower-Asia-Taipei" id="input-flower-Asia-Taipei-label" data-radio-item-label>Asia - Taipei</label>
    </div>
    <div id="input-flower-Asia-Paris-item" class="radio" data-radio-item-wrapper>
        <input id="input-flower-Asia-Paris" value="Asia/Paris" name="flower" data-radio-item-input type="radio">
        <label for="input-flower-Asia-Paris" id="input-flower-Asia-Paris-label" data-radio-item-label>Europe - Paris</label>
    </div>
    <div id="input-flower-UTC-item" class="radio" data-radio-item-wrapper>
        <input id="input-flower-UTC" value="UTC" name="flower" checked="checked" data-radio-item-input type="radio">
        <label for="input-flower-UTC" id="input-flower-UTC-label" data-radio-item-label>UTC</label>
    </div>
</div>
HTML;
        // phpcs:enable
        self::assertHtmlFormatEquals($html, $this->instance->renderInput());
    }
}

<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\HTMLElement;
use Windwalker\Form\Test\Stub\StubField;

/**
 * Tests the label, wrapper and input attribute helpers shared by all fields.
 */
class FieldLabelAndWrapperTest extends TestCase
{
    protected StubField $instance;

    #[Before]
    protected function createStubField(): void
    {
        $this->instance = new StubField('flower', 'Flower');
    }

    #[Test]
    public function labelChangesLabelText(): void
    {
        $this->instance->label('Sakura');

        self::assertSame('Sakura', $this->instance->getLabelName());
    }

    #[Test]
    public function modifyLabelPassesLabelElementToHandler(): void
    {
        $this->instance->modifyLabel(
            function (HTMLElement $label) {
                $label->setAttribute('title', 'tip');
            }
        );

        self::assertSame('tip', $this->instance->getLabelAttribute('title'));
    }

    #[Test]
    public function labelAttributesCanBeSetCheckedAndRemoved(): void
    {
        self::assertNull($this->instance->getLabelAttribute('data-foo'));
        self::assertFalse($this->instance->hasLabelAttribute('data-foo'));

        $this->instance->setLabelAttribute('data-foo', 'bar');

        self::assertTrue($this->instance->hasLabelAttribute('data-foo'));
        self::assertSame('bar', $this->instance->getLabelAttribute('data-foo'));
        self::assertSame('bar', $this->instance->getLabelAttributes()['data-foo']);

        $this->instance->removeLabelAttribute('data-foo');

        self::assertFalse($this->instance->hasLabelAttribute('data-foo'));

        $this->instance->labelAttr('data-baz', 'yoo');

        self::assertSame('yoo', $this->instance->getLabelAttribute('data-baz'));
    }

    #[Test]
    public function labelClassCanBeSetAddedAndRemoved(): void
    {
        $this->instance->setLabelClass('a b');
        $this->instance->addLabelClass('c');

        self::assertSame('a b c', $this->instance->getLabelClass());

        $this->instance->removeLabelClass('b');

        self::assertSame('a c', $this->instance->getLabelClass());
    }

    #[Test]
    public function modifyWrapperPassesWrapperElementToHandler(): void
    {
        $this->instance->modifyWrapper(
            function (HTMLElement $wrapper) {
                $wrapper->setAttribute('title', 'tip');
            }
        );

        self::assertSame('tip', $this->instance->getWrapperAttribute('title'));
        self::assertSame($this->instance->getWrapper(), $this->instance->compileWrapper());
    }

    #[Test]
    public function wrapperAttributesCanBeSetCheckedAndRemoved(): void
    {
        self::assertNull($this->instance->getWrapperAttribute('data-foo'));
        self::assertFalse($this->instance->hasWrapperAttribute('data-foo'));

        $this->instance->setWrapperAttribute('data-foo', 'bar');

        self::assertTrue($this->instance->hasWrapperAttribute('data-foo'));
        self::assertSame('bar', $this->instance->getWrapperAttribute('data-foo'));
        self::assertSame('bar', $this->instance->getWrapperAttributes()['data-foo']);

        $this->instance->removeWrapperAttribute('data-foo');

        self::assertFalse($this->instance->hasWrapperAttribute('data-foo'));

        $this->instance->wrapperAttr('data-baz', 'yoo');

        self::assertSame('yoo', $this->instance->getWrapperAttribute('data-baz'));
    }

    #[Test]
    public function wrapperClassCanBeSetAddedAndRemoved(): void
    {
        $this->instance->setWrapperClass('a b');
        $this->instance->addWrapperClass('c');

        self::assertSame('a b c', $this->instance->getWrapperClass());

        $this->instance->removeWrapperClass('b');

        self::assertSame('a c', $this->instance->getWrapperClass());
    }

    #[Test]
    public function inputAttributesCanBeCheckedRemovedAndSetByAlias(): void
    {
        $this->instance->attr('data-foo', 'bar');

        self::assertTrue($this->instance->hasAttribute('data-foo'));
        self::assertSame('bar', $this->instance->getAttribute('data-foo'));

        $this->instance->removeAttribute('data-foo');

        self::assertFalse($this->instance->hasAttribute('data-foo'));
    }

    #[Test]
    public function setClassReplacesInputClass(): void
    {
        $this->instance->setClass('foo bar');

        self::assertSame('foo bar', $this->instance->getClass());

        $this->instance->setClass('yoo');

        self::assertSame('yoo', $this->instance->getClass());
    }
}

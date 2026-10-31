<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use DOMException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\DOMElement;

class DOMTokenListTest extends TestCase
{
    #[Test]
    public function addsAndRemovesTokensWithoutDuplicates(): void
    {
        $element = DOMElement::create('div', ['class' => 'alpha beta']);
        $list = $element->classList;

        self::assertSame($list, $list->add('beta', 'gamma'));
        self::assertSame('alpha beta gamma', $list->value);
        self::assertSame($list, $list->remove('alpha', 'missing'));
        self::assertSame('beta gamma', $list->value);
        self::assertTrue($list->contains('gamma'));
        self::assertFalse($list->contains('alpha'));
    }

    #[Test]
    public function returnsTokenByIndexOrNull(): void
    {
        $list = DOMElement::create('div', ['class' => 'alpha beta'])->classList;

        self::assertSame('alpha', $list->item(0));
        self::assertSame('beta', $list->item(1));
        self::assertNull($list->item(2));
    }

    #[Test]
    public function togglesTokensWithAndWithoutForce(): void
    {
        $list = DOMElement::create('div', ['class' => 'alpha'])->classList;

        self::assertFalse($list->toggle('alpha'));
        self::assertTrue($list->toggle('beta'));
        self::assertTrue($list->toggle('beta', true));
        self::assertFalse($list->toggle('beta', false));
        self::assertSame('', $list->value);
    }

    #[Test]
    public function checksSupportedRelTokens(): void
    {
        $list = DOMElement::create('link')->relList;

        self::assertTrue($list->supports('stylesheet'));
        self::assertFalse($list->supports('not-a-rel-token'));
    }

    #[Test]
    public function rejectsSupportChecksWithoutTokenMetadata(): void
    {
        $list = DOMElement::create('div')->classList;

        $this->expectException(DOMException::class);
        $this->expectExceptionMessageIs(
            'Failed to execute \'supports\' on \'DOMTokenList\': DOMTokenList has no supported tokens.'
        );

        $list->supports('alpha');
    }
}

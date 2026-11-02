<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test\HTML5;

use Dom\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\HTML5\OutputRules;
use Windwalker\DOM\HTML5\Traverser;
use Windwalker\DOM\HTML5Factory;

#[CoversClass(Traverser::class)]
class TraverserTest extends TestCase
{
    #[Test]
    public function walksNodesFragmentsAndNodeLists(): void
    {
        $document = HTML5Factory::create();
        $element = $document->createElement('div');
        $element->appendChild($document->createTextNode('hello'));

        self::assertSame('<div>hello</div>', self::render($element));
        self::assertSame('hello', self::render($element->childNodes));

        $fragment = $document->createDocumentFragment();
        $fragment->appendChild($element->cloneNode(true));

        self::assertSame('<div>hello</div>', self::render($fragment));
        self::assertSame('', self::render($document->createDocumentFragment()));
    }

    #[Test]
    public function writesDocumentDoctypeAndContents(): void
    {
        $document = HTML5Factory::create();
        $document->body->appendChild($document->createElement('main'));

        self::assertSame(
            '<!DOCTYPE html>' . PHP_EOL . '<html><head></head><body><main></main></body></html>' . PHP_EOL,
            self::render($document)
        );
    }

    #[Test]
    public function recognizesHtmlSvgAndMathmlNamespaces(): void
    {
        $document = HTML5Factory::create();

        self::assertTrue(self::traverser($document->createElement('div'))->isLocalElement($document->createElement('div')));
        self::assertTrue(
            self::traverser($document->createElementNS('http://www.w3.org/2000/svg', 'svg'))
                ->isLocalElement($document->createElementNS('http://www.w3.org/2000/svg', 'svg'))
        );
        self::assertTrue(
            self::traverser($document->createElementNS('http://www.w3.org/1998/Math/MathML', 'math'))
                ->isLocalElement($document->createElementNS('http://www.w3.org/1998/Math/MathML', 'math'))
        );
        self::assertFalse(
            self::traverser($document->createElementNS('urn:example', 'custom'))
                ->isLocalElement($document->createElementNS('urn:example', 'custom'))
        );
        self::assertFalse(
            self::traverser($document->createElement('div'))
                ->isLocalElement($document->createElementNS(null, 'div'))
        );
    }

    private static function render(Node|\Dom\NodeList $node): string
    {
        $stream = fopen('php://temp', 'w+b');
        new Traverser($node, $stream, new OutputRules($stream))->walk();
        rewind($stream);
        $output = stream_get_contents($stream);
        fclose($stream);

        return $output;
    }

    private static function traverser(Node $node): Traverser
    {
        $stream = fopen('php://temp', 'w+b');

        return new Traverser($node, $stream, new OutputRules($stream));
    }
}

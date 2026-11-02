<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test\HTML5;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\HTML5\OutputRules;
use Windwalker\DOM\HTML5\Traverser;
use Windwalker\DOM\HTML5Factory;

#[CoversClass(OutputRules::class)]
class OutputRulesTest extends TestCase
{
    #[Test]
    public function serializesAttributesTextVoidTagsAndSvgNames(): void
    {
        $document = HTML5Factory::create();
        $root = $document->createElement('div');
        $root->setAttribute('title', 'a "quote" & ampersand');
        $root->appendChild($document->createTextNode('A < B & C'));
        $root->appendChild($document->createElement('input'));
        $svg = $document->createElementNS(OutputRules::NAMESPACE_SVG, 'svg');
        $path = $document->createElementNS(OutputRules::NAMESPACE_SVG, 'path');
        $path->setAttribute('viewbox', '0 0 1 1');
        $svg->appendChild($path);
        $root->appendChild($svg);

        self::assertSame(
            '<div title="a &quot;quote&quot; &amp; ampersand">A &lt; B &amp; C<input><svg><path viewBox="0 0 1 1" /></svg></div>',
            self::render($root)
        );
    }

    #[Test]
    public function writesRawScriptContentAndPreservesEmptyAttributesMatchingCustomRules(): void
    {
        $document = HTML5Factory::create();
        $script = $document->createElement('script');
        $script->appendChild($document->createTextNode('if (a < b) { run(); }'));
        $input = $document->createElement('input');
        $input->setAttribute('data-empty', '');
        $fragment = $document->createDocumentFragment();
        $fragment->appendChild($script);
        $fragment->appendChild($input);

        self::assertSame(
            '<script>if (a < b) { run(); }</script><input data-empty="">',
            self::render($fragment, ['customRule' => true])
        );
    }

    #[Test]
    public function encodesEntitiesWhenConfigured(): void
    {
        $document = HTML5Factory::create();
        $element = $document->createElement('p');
        $element->appendChild($document->createTextNode('©'));

        self::assertSame('<p>&copy;</p>', self::render($element, ['encode_entities' => true]));
    }

    private static function render(\Dom\Node $node, array $options = []): string
    {
        $stream = fopen('php://temp', 'w+b');
        $rules = new OutputRules($stream, $options);

        if ($options['customRule'] ?? false) {
            $rules->addRule(
                [
                    'nodeNamespace' => OutputRules::NAMESPACE_HTML,
                    'attrName' => 'data-empty'
                ]
            );
        }

        new Traverser($node, $stream, $rules)->walk();
        rewind($stream);
        $output = stream_get_contents($stream);
        fclose($stream);

        return $output;
    }
}

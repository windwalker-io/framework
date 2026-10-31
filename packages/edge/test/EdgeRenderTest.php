<?php

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Edge\Cache\EdgeArrayCache;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeStringLoader;

/**
 * Renders inline templates (EdgeStringLoader treats the layout name as the template source)
 * so directives can be verified end to end without touching the filesystem.
 */
class EdgeRenderTest extends TestCase
{
    protected Edge $instance;

    #[Before]
    public function setUpEdge(): void
    {
        $this->instance = new Edge(new EdgeStringLoader(), new EdgeArrayCache());
    }

    #[Test]
    public function renderConditionalsLoopsAndSwitch(): void
    {
        $template = <<<'EDGE'
        @if($n > 5) big @elseif($n > 2) mid @else small @endif
        @unless($n > 5) unless @endunless
        @foreach($items as $k => $v)
        {{ $k }}={{ $v }};
        @endforeach
        @forelse($none as $z) never @empty empty @endforelse
        @for($i = 0; $i < 3; $i++) {{ $i }} @endfor
        @while($w-- > 0) w @endwhile
        @switch($n)
        @case(1) one @break
        @case(3) three @break
        @default other
        @endswitch
        @foreach([1, 2, 3, 4] as $i)
        @continue($i === 2)
        @break($i === 4)
        i{{ $i }}
        @endforeach
        EDGE;

        $result = $this->instance->render($template, ['n' => 3, 'items' => ['p' => 1, 'q' => 2], 'none' => [], 'w' => 2]);

        self::assertSame(
            ['mid', 'unless', 'p=1;', 'q=2;', 'empty', '0', '1', '2', 'w', 'w', 'three', 'i1', 'i3'],
            preg_split('/\s+/', trim($result))
        );
    }

    #[Test]
    public function renderEchoAndHtmlHelpers(): void
    {
        $template = <<<'EDGE'
        {{ $html }}|{!! $html !!}|{{ $missing ?? 'dflt' }}|@{{ $html }}
        <i @checked($on) @disabled(!$on) @selected($on) @bool($on)></i>
        <p @class(['a', 'b' => $on, 'c' => !$on])></p>
        {{-- hidden --}}
        @verbatim {{ raw }} @endverbatim
        @php($x = 5){{ $x }}
        @json(['a' => 1], 0)
        EDGE;

        $result = $this->instance->render($template, ['html' => '<b>', 'on' => true]);

        self::assertStringContainsString('&lt;b&gt;|<b>|dflt|{{ $html }}', $result);
        self::assertStringContainsString('<i checked  selected true></i>', $result);
        self::assertStringContainsString('<p class="a b"></p>', $result);
        self::assertStringNotContainsString('hidden', $result);
        self::assertStringContainsString('{{ raw }}', $result);
        self::assertStringContainsString('5', $result);
        self::assertStringContainsString('{&quot;a&quot;:1}', $result);
    }

    #[Test]
    public function renderPushAndStackOnce(): void
    {
        $template = <<<'EDGE'
        @push('js') A @endpush
        @push('js') B @endpush
        @pushOnce('js', 'once') C @endPushOnce
        @pushOnce('js', 'once') D @endPushOnce
        @pushIf(true, 'css') E @endPushIf
        [@stack('js')] [@stack('css')]
        EDGE;

        $result = $this->instance->render($template);

        self::assertSame('[ABC][E]', preg_replace('/\s+/', '', $result));
    }

    #[Test]
    public function escapeEncodesHtmlAndHandlesNull(): void
    {
        self::assertSame('&lt;a&gt;', $this->instance->escape('<a>'));
        self::assertSame('', $this->instance->escape(null));
    }

    #[Test]
    public function renderEachRendersLayoutPerItem(): void
    {
        $result = $this->instance->renderEach('[{{ $row }}]', ['a', 'b'], 'row');

        self::assertSame('[a][b]', preg_replace('/\s+/', '', $result));
    }

    #[Test]
    public function globalsAreAvailableToTemplatesAndCanBeRemoved(): void
    {
        $this->instance->addGlobal('site', 'Windwalker');

        self::assertSame('Windwalker', $this->instance->getGlobal('site'));
        self::assertSame('d', $this->instance->getGlobal('none', 'd'));
        self::assertSame('Hi Windwalker', trim($this->instance->render('Hi {{ $site }}')));

        $this->instance->removeGlobal('site');

        self::assertNull($this->instance->getGlobal('site'));
        self::assertSame([], $this->instance->getGlobals());
    }
}

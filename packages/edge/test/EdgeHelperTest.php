<?php

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Edge\EdgeHelper;

class EdgeHelperTest extends TestCase
{
    #[Test]
    public function toJsEncodesDataAsBase64JsonParse(): void
    {
        $js = EdgeHelper::toJS(['a' => 1, 'b' => "it's"]);

        self::assertMatchesRegularExpression("/^JSON\\.parse\\(atob\\('([A-Za-z0-9+\\/=]+)'\\)\\)$/", $js);

        preg_match("/atob\\('(.+)'\\)/", $js, $m);

        self::assertSame(['a' => 1, 'b' => "it's"], json_decode(base64_decode($m[1]), true));
    }
}

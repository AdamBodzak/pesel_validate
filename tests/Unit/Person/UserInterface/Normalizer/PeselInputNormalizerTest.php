<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\UserInterface\Normalizer;

use App\Person\UserInterface\Normalizer\PeselInputNormalizer;
use PHPUnit\Framework\TestCase;

final class PeselInputNormalizerTest extends TestCase
{
    /**
     * @dataProvider inputProvider
     */
    public function testRemovesWhitespace(string $input, string $expected): void
    {
        self::assertSame($expected, (new PeselInputNormalizer())->normalize($input));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function inputProvider(): iterable
    {
        yield 'already clean' => ['44051401359', '44051401359'];
        yield 'leading and trailing spaces' => ['  44051401359  ', '44051401359'];
        yield 'spaces between digits' => ['440 514 013 59', '44051401359'];
        yield 'tab and new line' => ["\t44051401359\n", '44051401359'];
        yield 'non-breaking space' => ["440514\u{00A0}01359", '44051401359'];
        yield 'other characters are kept' => ['440514-01359', '440514-01359'];
        yield 'invalid UTF-8 is returned unchanged' => ["4405140135\xFF", "4405140135\xFF"];
    }
}

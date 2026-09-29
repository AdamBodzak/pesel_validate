<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\UserInterface\Form\DataTransformer;

use App\Person\UserInterface\Form\DataTransformer\PeselInputDataTransformer;
use App\Person\UserInterface\Normalizer\PeselInputNormalizer;
use PHPUnit\Framework\TestCase;

final class PeselInputDataTransformerTest extends TestCase
{
    private PeselInputDataTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new PeselInputDataTransformer(new PeselInputNormalizer());
    }

    public function testTransformsModelValueToViewAsIs(): void
    {
        self::assertSame('44051401359', $this->transformer->transform('44051401359'));
        self::assertSame('', $this->transformer->transform(null));
    }

    public function testNormalizesSubmittedValue(): void
    {
        self::assertSame('44051401359', $this->transformer->reverseTransform(' 440 514 013 59 '));
    }

    /**
     * @dataProvider emptyValueProvider
     */
    public function testReturnsNullForEmptySubmittedValue(?string $value): void
    {
        self::assertNull($this->transformer->reverseTransform($value));
    }

    /**
     * @return iterable<string, array{?string}>
     */
    public static function emptyValueProvider(): iterable
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
        yield 'whitespace only' => ['   '];
    }
}

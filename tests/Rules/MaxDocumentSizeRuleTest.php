<?php

declare(strict_types=1);

namespace DocumentValidation\Tests\Rules;

use DocumentValidation\Document;
use DocumentValidation\Exceptions\InvalidRuleConfiguration;
use DocumentValidation\Rules\MaxDocumentSizeRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaxDocumentSizeRule::class)]
final class MaxDocumentSizeRuleTest extends TestCase
{
    public function testPassesWhenContentIsUnderTheLimit(): void
    {
        $rule = new MaxDocumentSizeRule(maxBytes: 10);

        self::assertSame([], $rule->validate(self::documentWithContent('hello')));
    }

    public function testPassesWhenContentIsExactlyAtTheLimit(): void
    {
        $rule = new MaxDocumentSizeRule(maxBytes: 5);

        // Boundary case: 5 bytes against a 5 byte limit is allowed.
        self::assertSame([], $rule->validate(self::documentWithContent('hello')));
    }

    public function testFailsWhenContentExceedsTheLimit(): void
    {
        $rule = new MaxDocumentSizeRule(maxBytes: 4);

        self::assertSame(
            ['Document size of 5 bytes exceeds the maximum of 4 bytes.'],
            $rule->validate(self::documentWithContent('hello')),
        );
    }

    public function testMeasuresBytesRatherThanCharacters(): void
    {
        // Four multibyte characters = 8 bytes, which is over a 7 byte limit.
        $rule = new MaxDocumentSizeRule(maxBytes: 7);

        self::assertNotSame([], $rule->validate(self::documentWithContent('éééé')));
    }

    #[DataProvider('nonPositiveLimitProvider')]
    public function testRejectsNonPositiveLimit(int $maxBytes): void
    {
        $this->expectException(InvalidRuleConfiguration::class);

        new MaxDocumentSizeRule($maxBytes);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function nonPositiveLimitProvider(): iterable
    {
        yield 'zero'     => [0];
        yield 'negative' => [-1];
    }

    private static function documentWithContent(string $content): Document
    {
        return new Document('doc-1', 'acme', $content);
    }
}

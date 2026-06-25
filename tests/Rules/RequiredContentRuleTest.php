<?php

declare(strict_types=1);

namespace DocumentValidation\Tests\Rules;

use DocumentValidation\Document;
use DocumentValidation\Rules\RequiredContentRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequiredContentRule::class)]
final class RequiredContentRuleTest extends TestCase
{
    public function testPassesWhenContentIsPresent(): void
    {
        $rule = new RequiredContentRule();

        self::assertSame([], $rule->validate(new Document('doc-1', 'acme', 'real content')));
    }

    #[DataProvider('blankContentProvider')]
    public function testFailsWhenContentIsBlank(string $content): void
    {
        $rule = new RequiredContentRule();

        self::assertSame(
            ['Document content must not be empty.'],
            $rule->validate(new Document('doc-1', 'acme', $content)),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function blankContentProvider(): iterable
    {
        yield 'empty string'      => [''];
        yield 'spaces'            => ['     '];
        yield 'tabs and newlines' => ["\t\n  \n"];
    }
}

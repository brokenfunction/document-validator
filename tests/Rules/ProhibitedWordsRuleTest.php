<?php

declare(strict_types=1);

namespace DocumentValidation\Tests\Rules;

use DocumentValidation\Document;
use DocumentValidation\Exceptions\InvalidRuleConfiguration;
use DocumentValidation\Rules\ProhibitedWordsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProhibitedWordsRule::class)]
final class ProhibitedWordsRuleTest extends TestCase
{
    public function testPassesWhenNoProhibitedWordIsPresent(): void
    {
        $rule = new ProhibitedWordsRule('secret', 'confidential');

        self::assertSame([], $rule->validate(self::documentWithContent('a perfectly ordinary memo')));
    }

    public function testDetectsProhibitedWordCaseInsensitively(): void
    {
        $rule = new ProhibitedWordsRule('secret');

        self::assertSame(
            ["Content contains the prohibited word 'secret'."],
            $rule->validate(self::documentWithContent('This is SECRET information.')),
        );
    }

    public function testMatchesWholeWordsOnly(): void
    {
        $rule = new ProhibitedWordsRule('ass');

        // "class" and "assignment" embed the letters but are not the word itself.
        self::assertSame([], $rule->validate(self::documentWithContent('the class assignment is ready')));
    }

    public function testReportsEachDistinctProhibitedWordFound(): void
    {
        $rule = new ProhibitedWordsRule('secret', 'confidential');

        $errors = $rule->validate(self::documentWithContent('This confidential and secret file.'));

        self::assertCount(2, $errors);
        self::assertContains("Content contains the prohibited word 'secret'.", $errors);
        self::assertContains("Content contains the prohibited word 'confidential'.", $errors);
    }

    public function testHandlesWordsContainingRegexMetacharacters(): void
    {
        // The '.' must be treated literally, not as "any character".
        $rule = new ProhibitedWordsRule('a.b');

        self::assertNotSame([], $rule->validate(self::documentWithContent('see a.b here')));
        self::assertSame([], $rule->validate(self::documentWithContent('see axb here')));
    }

    public function testNormalisesConfiguredWords(): void
    {
        // Surrounding whitespace is trimmed and case-insensitive duplicates merge,
        // so "secret" is only reported once.
        $rule = new ProhibitedWordsRule('  secret  ', 'SECRET');

        self::assertCount(1, $rule->validate(self::documentWithContent('a secret matter')));
    }

    public function testRejectsWhenNoUsableWordIsProvided(): void
    {
        $this->expectException(InvalidRuleConfiguration::class);

        new ProhibitedWordsRule('   ', '');
    }

    private static function documentWithContent(string $content): Document
    {
        return new Document('doc-1', 'acme', $content);
    }
}

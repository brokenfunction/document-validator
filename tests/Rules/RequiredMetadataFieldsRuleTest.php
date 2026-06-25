<?php

declare(strict_types=1);

namespace DocumentValidation\Tests\Rules;

use DocumentValidation\Document;
use DocumentValidation\Exceptions\InvalidRuleConfiguration;
use DocumentValidation\Rules\RequiredMetadataFieldsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequiredMetadataFieldsRule::class)]
final class RequiredMetadataFieldsRuleTest extends TestCase
{
    public function testPassesWhenAllRequiredFieldsArePresent(): void
    {
        $rule = new RequiredMetadataFieldsRule('author', 'department');

        $document = self::documentWithMetadata([
            'author'     => 'Jane',
            'department' => 'Legal',
        ]);

        self::assertSame([], $rule->validate($document));
    }

    public function testReportsEachMissingField(): void
    {
        $rule = new RequiredMetadataFieldsRule('author', 'department', 'project');

        $document = self::documentWithMetadata(['author' => 'Jane']);

        self::assertSame(
            [
                "Required metadata field 'department' is missing.",
                "Required metadata field 'project' is missing.",
            ],
            $rule->validate($document),
        );
    }

    public function testTreatsNullAndBlankValuesAsMissing(): void
    {
        $rule = new RequiredMetadataFieldsRule('a', 'b', 'c');

        $document = self::documentWithMetadata([
            'a' => null,
            'b' => '',
            'c' => '   ',
        ]);

        self::assertCount(3, $rule->validate($document));
    }

    public function testTreatsZeroAsAPresentValue(): void
    {
        // 0 (or "0") is a legitimate value; only true absence/emptiness should fail.
        $rule = new RequiredMetadataFieldsRule('count', 'code');

        $document = self::documentWithMetadata([
            'count' => 0,
            'code'  => '0',
        ]);

        self::assertSame([], $rule->validate($document));
    }

    public function testDeduplicatesRepeatedFieldConfiguration(): void
    {
        $rule = new RequiredMetadataFieldsRule('author', 'author');

        $document = self::documentWithMetadata([]); // author missing

        // Even though "author" was configured twice, it is reported once.
        self::assertSame(
            ["Required metadata field 'author' is missing."],
            $rule->validate($document),
        );
    }

    public function testRejectsEmptyFieldList(): void
    {
        $this->expectException(InvalidRuleConfiguration::class);

        new RequiredMetadataFieldsRule();
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private static function documentWithMetadata(array $metadata): Document
    {
        return new Document('doc-1', 'acme', 'content', $metadata);
    }
}

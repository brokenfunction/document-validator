<?php

declare(strict_types=1);

namespace DocumentValidation\Tests;

use DocumentValidation\Document;
use DocumentValidation\Exceptions\InvalidRuleConfiguration;
use DocumentValidation\RuleFactory;
use DocumentValidation\Rules\RequiredContentRule;
use DocumentValidation\ValidationRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuleFactory::class)]
final class RuleFactoryTest extends TestCase
{
    public function testBuildsMaxDocumentSizeRuleWithItsOption(): void
    {
        $rule = new RuleFactory()->create(['type' => 'max_document_size', 'max_bytes' => 4]);

        // Behaviour confirms the option was wired through, not just the type.
        self::assertSame(
            ['Document size of 5 bytes exceeds the maximum of 4 bytes.'],
            $rule->validate(new Document('d', 'acme', 'hello')),
        );
    }

    public function testBuildsRequiredMetadataFieldsRuleWithItsOption(): void
    {
        $rule = new RuleFactory()->create(['type' => 'required_metadata_fields', 'fields' => ['author']]);

        self::assertSame(
            ["Required metadata field 'author' is missing."],
            $rule->validate(new Document('d', 'acme', 'body', [])),
        );
    }

    public function testBuildsProhibitedWordsRuleWithItsOption(): void
    {
        $rule = new RuleFactory()->create(['type' => 'prohibited_words', 'words' => ['secret']]);

        self::assertSame(
            ["Content contains the prohibited word 'secret'."],
            $rule->validate(new Document('d', 'acme', 'a secret matter')),
        );
    }

    public function testBuildsRequiredContentRule(): void
    {
        $rule = new RuleFactory()->create(['type' => 'required_content']);

        self::assertCount(1, $rule->validate(new Document('d', 'acme', '   ')));
    }

    public function testCreateManyBuildsRulesInOrder(): void
    {
        $rules = new RuleFactory()->createMany([
            ['type' => 'required_content'],
            ['type' => 'max_document_size', 'max_bytes' => 10],
        ]);

        self::assertCount(2, $rules);
        self::assertContainsOnlyInstancesOf(ValidationRule::class, $rules);
    }

    public function testCreateManyOnEmptyConfigYieldsNoRules(): void
    {
        self::assertSame([], new RuleFactory()->createMany([]));
    }

    public function testRegisterAddsACustomRuleType(): void
    {
        $factory = new RuleFactory();
        $factory->register('always_fail', static fn (array $spec): ValidationRule => new class () implements ValidationRule {
            public function validate(Document $document): array
            {
                return ['nope'];
            }
        });

        self::assertSame(['nope'], $factory->create(['type' => 'always_fail'])->validate(new Document('d', 'acme', 'x')));
    }

    public function testRegisterCanOverrideADefaultType(): void
    {
        $factory = new RuleFactory();
        $factory->register('required_content', static fn (array $spec): ValidationRule => new RequiredContentRule());

        self::assertInstanceOf(RequiredContentRule::class, $factory->create(['type' => 'required_content']));
    }

    public function testUnknownTypeIsRejected(): void
    {
        $this->expectException(InvalidRuleConfiguration::class);

        new RuleFactory()->create(['type' => 'does_not_exist']);
    }

    #[DataProvider('malformedSpecProvider')]
    public function testMalformedSpecsAreRejected(array $spec): void
    {
        $this->expectException(InvalidRuleConfiguration::class);

        new RuleFactory()->create($spec);
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function malformedSpecProvider(): iterable
    {
        yield 'missing type'                => [[]];
        yield 'non-string type'             => [['type' => 123]];
        yield 'size without max_bytes'      => [['type' => 'max_document_size']];
        yield 'size with non-int max_bytes' => [['type' => 'max_document_size', 'max_bytes' => 'big']];
        yield 'metadata without fields'     => [['type' => 'required_metadata_fields']];
        yield 'metadata with empty fields'  => [['type' => 'required_metadata_fields', 'fields' => []]];
        yield 'metadata with non-string'    => [['type' => 'required_metadata_fields', 'fields' => [42]]];
        yield 'words with non-string item'  => [['type' => 'prohibited_words', 'words' => [42]]];
    }

    public function testCreateManyRejectsNonArraySpec(): void
    {
        $this->expectException(InvalidRuleConfiguration::class);

        /** @phpstan-ignore-next-line intentionally malformed input */
        new RuleFactory()->createMany([['type' => 'required_content'], 'not-an-array']);
    }
}

<?php

declare(strict_types=1);

namespace DocumentValidation\Tests;

use DocumentValidation\Document;
use DocumentValidation\DocumentValidator;
use DocumentValidation\RegistryRuleProvider;
use DocumentValidation\ValidationRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentValidator::class)]
final class DocumentValidatorTest extends TestCase
{
    public function testDocumentIsValidWhenEveryRulePasses(): void
    {
        $provider = new RegistryRuleProvider();
        $provider->register('acme', self::passingRule(), self::passingRule());

        $result = new DocumentValidator($provider)->validate(self::document('acme'));

        self::assertTrue($result->isValid);
        self::assertSame([], $result->errors);
    }

    public function testAggregatesErrorsFromEveryRuleInOrder(): void
    {
        $provider = new RegistryRuleProvider();
        $provider->register(
            'acme',
            self::failingRule('first problem'),
            self::passingRule(),
            self::failingRule('second problem', 'third problem'),
        );

        $result = new DocumentValidator($provider)->validate(self::document('acme'));

        self::assertFalse($result->isValid);
        self::assertSame(
            ['first problem', 'second problem', 'third problem'],
            $result->errors,
        );
    }

    public function testTenantWithNoConfiguredRulesIsTreatedAsValid(): void
    {
        $provider = new RegistryRuleProvider();
        $provider->register('acme', self::failingRule('should never run'));

        // The document belongs to a different tenant, so none of acme's rules apply.
        $result = new DocumentValidator($provider)->validate(self::document('globex'));

        self::assertTrue($result->isValid);
    }

    public function testRunsTheRuleAgainstTheGivenDocument(): void
    {
        // Records the document it received, so we can assert it's passed through untouched.
        $recordingRule = new class () implements ValidationRule {
            public ?Document $seen = null;

            public function validate(Document $document): array
            {
                $this->seen = $document;
                return [];
            }
        };

        $provider = new RegistryRuleProvider();
        $provider->register('acme', $recordingRule);

        $document = self::document('acme');
        new DocumentValidator($provider)->validate($document);

        self::assertSame($document, $recordingRule->seen);
    }

    // --- helpers ------------------------------------------------------------

    private static function document(string $tenantId): Document
    {
        return new Document('doc-1', $tenantId, 'content');
    }

    private static function passingRule(): ValidationRule
    {
        return self::failingRule(); // no messages == passing
    }

    private static function failingRule(string ...$messages): ValidationRule
    {
        return new class ($messages) implements ValidationRule {
            /** @param list<string> $messages */
            public function __construct(private array $messages)
            {
            }

            public function validate(Document $document): array
            {
                return $this->messages;
            }
        };
    }
}

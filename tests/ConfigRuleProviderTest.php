<?php

declare(strict_types=1);

namespace DocumentValidation\Tests;

use DocumentValidation\ConfigRuleProvider;
use DocumentValidation\Document;
use DocumentValidation\DocumentValidator;
use DocumentValidation\RuleFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigRuleProvider::class)]
final class ConfigRuleProviderTest extends TestCase
{
    public function testReturnsNoRulesForUnknownTenant(): void
    {
        $provider = new ConfigRuleProvider(new RuleFactory(), []);

        self::assertSame([], $provider->rulesFor('nobody'));
    }

    public function testBuildsTheRulesConfiguredForATenant(): void
    {
        $provider = new ConfigRuleProvider(new RuleFactory(), [
            'acme' => [
                ['type' => 'required_content'],
                ['type' => 'max_document_size', 'max_bytes' => 1024],
            ],
        ]);

        self::assertCount(2, $provider->rulesFor('acme'));
    }

    public function testValidatesEndToEndFromConfig(): void
    {
        // The whole point: declarative config flows through the factory and the
        // validator behaves exactly as if the rules had been wired up by hand.
        $provider = new ConfigRuleProvider(new RuleFactory(), [
            'acme' => [
                ['type' => 'required_content'],
                ['type' => 'max_document_size', 'max_bytes' => 8],
                ['type' => 'required_metadata_fields', 'fields' => ['author']],
            ],
        ]);

        $content = 'this content is well over eight bytes';
        $document = new Document(id: 'doc-1', tenantId: 'acme', content: $content, metadata: []);

        $result = new DocumentValidator($provider)->validate($document);

        self::assertFalse($result->isValid);
        self::assertSame(
            [
                sprintf('Document size of %d bytes exceeds the maximum of 8 bytes.', strlen($content)),
                "Required metadata field 'author' is missing.",
            ],
            $result->errors,
        );
    }
}

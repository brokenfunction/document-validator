<?php

declare(strict_types=1);

namespace DocumentValidation\Tests;

use DocumentValidation\Document;
use DocumentValidation\RegistryRuleProvider;
use DocumentValidation\ValidationRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegistryRuleProvider::class)]
final class RegistryRuleProviderTest extends TestCase
{
    public function testReturnsAnEmptyListForUnknownTenant(): void
    {
        $provider = new RegistryRuleProvider();

        self::assertSame([], $provider->rulesFor('nobody'));
    }

    public function testReturnsRulesRegisteredForATenant(): void
    {
        $ruleA = self::noopRule();
        $ruleB = self::noopRule();

        $provider = new RegistryRuleProvider();
        $provider->register('acme', $ruleA, $ruleB);

        self::assertSame([$ruleA, $ruleB], $provider->rulesFor('acme'));
    }

    public function testRegistrationsAccumulateAcrossCalls(): void
    {
        $ruleA = self::noopRule();
        $ruleB = self::noopRule();

        $provider = new RegistryRuleProvider();
        $provider->register('acme', $ruleA);
        $provider->register('acme', $ruleB);

        self::assertSame([$ruleA, $ruleB], $provider->rulesFor('acme'));
    }

    public function testRulesAreScopedPerTenant(): void
    {
        $acmeRule = self::noopRule();
        $globexRule = self::noopRule();

        $provider = new RegistryRuleProvider();
        $provider->register('acme', $acmeRule);
        $provider->register('globex', $globexRule);

        self::assertSame([$acmeRule], $provider->rulesFor('acme'));
        self::assertSame([$globexRule], $provider->rulesFor('globex'));
    }

    private static function noopRule(): ValidationRule
    {
        return new class () implements ValidationRule {
            public function validate(Document $document): array
            {
                return [];
            }
        };
    }
}

<?php

declare(strict_types=1);

namespace DocumentValidation;

final readonly class ConfigRuleProvider implements RuleProvider
{
    /**
     * @param array<string, list<array<string, mixed>>> $configByTenant Rule specs keyed by tenant id.
     */
    public function __construct(
        private RuleFactory $factory,
        private array $configByTenant,
    ) {
    }

    public function rulesFor(string $tenantId): array
    {
        return $this->factory->createMany($this->configByTenant[$tenantId] ?? []);
    }
}

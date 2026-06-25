<?php

declare(strict_types=1);

namespace DocumentValidation;

final class RegistryRuleProvider implements RuleProvider
{
    /** @var array<string, list<ValidationRule>> */
    private array $rulesByTenant = [];

    public function register(string $tenantId, ValidationRule ...$rules): void
    {
        foreach ($rules as $rule) {
            $this->rulesByTenant[$tenantId][] = $rule;
        }
    }

    public function rulesFor(string $tenantId): array
    {
        return $this->rulesByTenant[$tenantId] ?? [];
    }
}

<?php

declare(strict_types=1);

namespace DocumentValidation;

interface RuleProvider
{
    /** @return list<ValidationRule> */
    public function rulesFor(string $tenantId): array;
}

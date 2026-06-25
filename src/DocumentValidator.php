<?php

declare(strict_types=1);

namespace DocumentValidation;

final readonly class DocumentValidator
{
    public function __construct(
        private RuleProvider $ruleProvider,
    ) {
    }

    public function validate(Document $document): ValidationResult
    {
        $errors = [];

        foreach ($this->ruleProvider->rulesFor($document->tenantId) as $rule) {
            foreach ($rule->validate($document) as $message) {
                $errors[] = $message;
            }
        }

        return ValidationResult::fromErrors($errors);
    }
}

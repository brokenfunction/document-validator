<?php

declare(strict_types=1);

namespace DocumentValidation\Rules;

use DocumentValidation\Document;
use DocumentValidation\Exceptions\InvalidRuleConfiguration;
use DocumentValidation\ValidationRule;

final class RequiredMetadataFieldsRule implements ValidationRule
{
    /** @var list<string> */
    private readonly array $requiredFields;

    public function __construct(string ...$requiredFields)
    {
        if ($requiredFields === []) {
            throw new InvalidRuleConfiguration('At least one required metadata field must be specified.');
        }

        $this->requiredFields = array_values(array_unique($requiredFields));
    }

    public function validate(Document $document): array
    {
        $errors = [];

        foreach ($this->requiredFields as $field) {
            $value = $document->metadataValue($field);

            // Absent, null or blank counts as missing; a real 0 does not.
            if ($value === null || (is_string($value) && trim($value) === '')) {
                $errors[] = sprintf("Required metadata field '%s' is missing.", $field);
            }
        }

        return $errors;
    }
}

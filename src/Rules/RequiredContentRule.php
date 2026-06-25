<?php

declare(strict_types=1);

namespace DocumentValidation\Rules;

use DocumentValidation\Document;
use DocumentValidation\ValidationRule;

final class RequiredContentRule implements ValidationRule
{
    public function validate(Document $document): array
    {
        if (trim($document->content) === '') {
            return ['Document content must not be empty.'];
        }

        return [];
    }
}

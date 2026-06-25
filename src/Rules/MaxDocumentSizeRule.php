<?php

declare(strict_types=1);

namespace DocumentValidation\Rules;

use DocumentValidation\Document;
use DocumentValidation\Exceptions\InvalidRuleConfiguration;
use DocumentValidation\ValidationRule;

final class MaxDocumentSizeRule implements ValidationRule
{
    public function __construct(
        private readonly int $maxBytes,
    ) {
        if ($maxBytes < 1) {
            throw new InvalidRuleConfiguration('Maximum size must be a positive number of bytes.');
        }
    }

    public function validate(Document $document): array
    {
        $size = $document->sizeInBytes();

        if ($size > $this->maxBytes) {
            return [sprintf('Document size of %d bytes exceeds the maximum of %d bytes.', $size, $this->maxBytes)];
        }

        return [];
    }
}

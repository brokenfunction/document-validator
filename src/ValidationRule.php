<?php

declare(strict_types=1);

namespace DocumentValidation;

interface ValidationRule
{
    /** @return list<string> error messages, empty if the document passed */
    public function validate(Document $document): array;
}

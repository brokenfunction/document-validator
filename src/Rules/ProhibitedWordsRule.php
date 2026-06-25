<?php

declare(strict_types=1);

namespace DocumentValidation\Rules;

use DocumentValidation\Document;
use DocumentValidation\Exceptions\InvalidRuleConfiguration;
use DocumentValidation\ValidationRule;

final class ProhibitedWordsRule implements ValidationRule
{
    /** @var list<string> */
    private readonly array $prohibitedWords;

    public function __construct(string ...$prohibitedWords)
    {
        // Trim, drop blanks, de-duplicate case-insensitively.
        $cleaned = [];
        foreach ($prohibitedWords as $word) {
            $word = trim($word);
            if ($word !== '') {
                $cleaned[strtolower($word)] = $word;
            }
        }

        if ($cleaned === []) {
            throw new InvalidRuleConfiguration('At least one non-empty prohibited word must be provided.');
        }

        $this->prohibitedWords = array_values($cleaned);
    }

    public function validate(Document $document): array
    {
        $errors = [];

        foreach ($this->prohibitedWords as $word) {
            // Whole-word, case-insensitive match (so "ass" doesn't flag "class").
            $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';

            if (preg_match($pattern, $document->content) === 1) {
                $errors[] = sprintf("Content contains the prohibited word '%s'.", $word);
            }
        }

        return $errors;
    }
}

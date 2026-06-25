<?php

declare(strict_types=1);

namespace DocumentValidation;

use InvalidArgumentException;

final readonly class ValidationResult
{
    /** @param list<string> $errors */
    private function __construct(
        public bool $isValid,
        public array $errors,
    ) {
    }

    public static function valid(): self
    {
        return new self(true, []);
    }

    /** @param list<string> $errors */
    public static function invalid(array $errors): self
    {
        if ($errors === []) {
            throw new InvalidArgumentException('An invalid result must carry at least one error message.');
        }

        return new self(false, array_values($errors));
    }

    /** @param list<string> $errors */
    public static function fromErrors(array $errors): self
    {
        return $errors === [] ? self::valid() : self::invalid($errors);
    }
}

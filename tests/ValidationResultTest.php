<?php

declare(strict_types=1);

namespace DocumentValidation\Tests;

use DocumentValidation\ValidationResult;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationResult::class)]
final class ValidationResultTest extends TestCase
{
    public function testValidResultHasNoErrors(): void
    {
        $result = ValidationResult::valid();

        self::assertTrue($result->isValid);
        self::assertSame([], $result->errors);
    }

    public function testInvalidResultCarriesErrors(): void
    {
        $result = ValidationResult::invalid(['boom']);

        self::assertFalse($result->isValid);
        self::assertSame(['boom'], $result->errors);
    }

    public function testInvalidResultRequiresAtLeastOneError(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ValidationResult::invalid([]);
    }

    public function testFromErrorsIsValidWhenEmpty(): void
    {
        $result = ValidationResult::fromErrors([]);

        self::assertTrue($result->isValid);
        self::assertSame([], $result->errors);
    }

    public function testFromErrorsIsInvalidWhenNotEmpty(): void
    {
        $result = ValidationResult::fromErrors(['a', 'b']);

        self::assertFalse($result->isValid);
        self::assertSame(['a', 'b'], $result->errors);
    }

    public function testFromErrorsReindexesSparseInput(): void
    {
        // A caller might hand us a non-sequential array; the result should expose
        // a clean, zero-based list.
        $result = ValidationResult::fromErrors([2 => 'a', 5 => 'b']);

        self::assertSame(['a', 'b'], $result->errors);
    }
}

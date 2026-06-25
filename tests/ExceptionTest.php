<?php

declare(strict_types=1);

namespace DocumentValidation\Tests;

use DocumentValidation\Exceptions\DocumentValidationException;
use DocumentValidation\Exceptions\InvalidDocument;
use DocumentValidation\Exceptions\InvalidRuleConfiguration;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDocument::class)]
#[CoversClass(InvalidRuleConfiguration::class)]
final class ExceptionTest extends TestCase
{
    public function testInvalidDocumentIsCatchableAsMarkerAndSplType(): void
    {
        $exception = new InvalidDocument('bad document');

        // Catchable both as the library's group marker and as the SPL base type.
        self::assertInstanceOf(DocumentValidationException::class, $exception);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    public function testInvalidRuleConfigurationIsCatchableAsMarkerAndSplType(): void
    {
        $exception = new InvalidRuleConfiguration('bad config');

        self::assertInstanceOf(DocumentValidationException::class, $exception);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}

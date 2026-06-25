<?php

declare(strict_types=1);

namespace DocumentValidation\Tests;

use DocumentValidation\Document;
use DocumentValidation\Exceptions\InvalidDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
final class DocumentTest extends TestCase
{
    public function testExposesItsProperties(): void
    {
        $document = new Document(
            id: 'doc-1',
            tenantId: 'acme',
            content: 'hello',
            metadata: ['author' => 'Jane'],
        );

        self::assertSame('doc-1', $document->id);
        self::assertSame('acme', $document->tenantId);
        self::assertSame('hello', $document->content);
        self::assertSame(['author' => 'Jane'], $document->metadata);
    }

    public function testContentAndMetadataDefaultToEmpty(): void
    {
        $document = new Document('doc-1', 'acme');

        self::assertSame('', $document->content);
        self::assertSame([], $document->metadata);
    }

    public function testSizeInBytesCountsBytesNotCharacters(): void
    {
        // "é" is two bytes in UTF-8, so a single-character string is two bytes.
        $document = new Document('doc-1', 'acme', 'é');

        self::assertSame(2, $document->sizeInBytes());
    }

    public function testMetadataValueReturnsNullWhenAbsent(): void
    {
        $document = new Document('doc-1', 'acme', '', ['author' => 'Jane']);

        self::assertSame('Jane', $document->metadataValue('author'));
        self::assertNull($document->metadataValue('department'));
    }

    #[DataProvider('blankIdentityProvider')]
    public function testRejectsBlankIdentity(string $id, string $tenantId): void
    {
        $this->expectException(InvalidDocument::class);

        new Document($id, $tenantId);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function blankIdentityProvider(): iterable
    {
        yield 'empty id'          => ['', 'acme'];
        yield 'whitespace id'     => ['   ', 'acme'];
        yield 'empty tenant'      => ['doc-1', ''];
        yield 'whitespace tenant' => ['doc-1', "\t"];
    }
}

<?php

declare(strict_types=1);

namespace DocumentValidation;

use DocumentValidation\Exceptions\InvalidDocument;

final readonly class Document
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $id,
        public string $tenantId,
        public string $content = '',
        public array $metadata = [],
    ) {
        if (trim($id) === '') {
            throw new InvalidDocument('Document id must not be empty.');
        }

        if (trim($tenantId) === '') {
            throw new InvalidDocument('Document tenantId must not be empty.');
        }
    }

    public function sizeInBytes(): int
    {
        return strlen($this->content);
    }

    public function metadataValue(string $field): mixed
    {
        return $this->metadata[$field] ?? null;
    }
}

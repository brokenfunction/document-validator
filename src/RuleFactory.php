<?php

declare(strict_types=1);

namespace DocumentValidation;

use Closure;
use DocumentValidation\Exceptions\InvalidRuleConfiguration;
use DocumentValidation\Rules\MaxDocumentSizeRule;
use DocumentValidation\Rules\ProhibitedWordsRule;
use DocumentValidation\Rules\RequiredContentRule;
use DocumentValidation\Rules\RequiredMetadataFieldsRule;

/** Builds rules from declarative specs, e.g. ['type' => 'max_document_size', 'max_bytes' => 1024]. */
final class RuleFactory
{
    /** @var array<string, Closure(array<string, mixed>): ValidationRule> */
    private array $builders = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    /** @param Closure(array<string, mixed>): ValidationRule $builder */
    public function register(string $type, Closure $builder): void
    {
        $this->builders[$type] = $builder;
    }

    /** @param array<string, mixed> $spec */
    public function create(array $spec): ValidationRule
    {
        $type = $spec['type'] ?? null;

        if (!is_string($type) || $type === '') {
            throw new InvalidRuleConfiguration('Each rule spec must declare a non-empty string "type".');
        }

        $builder = $this->builders[$type]
            ?? throw new InvalidRuleConfiguration(sprintf('Unknown rule type "%s".', $type));

        return $builder($spec);
    }

    /**
     * @param  list<array<string, mixed>> $specs
     * @return list<ValidationRule>
     */
    public function createMany(array $specs): array
    {
        $rules = [];

        foreach (array_values($specs) as $index => $spec) {
            if (!is_array($spec)) {
                throw new InvalidRuleConfiguration(sprintf('Rule spec #%d must be an array.', $index));
            }

            $rules[] = $this->create($spec);
        }

        return $rules;
    }

    private function registerDefaults(): void
    {
        $this->register(
            'required_content',
            static fn (array $spec): ValidationRule => new RequiredContentRule(),
        );

        $this->register('max_document_size', static function (array $spec): ValidationRule {
            $maxBytes = $spec['max_bytes'] ?? null;

            if (!is_int($maxBytes)) {
                throw new InvalidRuleConfiguration('Rule "max_document_size" requires an integer "max_bytes".');
            }

            return new MaxDocumentSizeRule($maxBytes);
        });

        $this->register('required_metadata_fields', static function (array $spec): ValidationRule {
            $fields = self::stringList($spec['fields'] ?? null, 'required_metadata_fields', 'fields');

            return new RequiredMetadataFieldsRule(...$fields);
        });

        $this->register('prohibited_words', static function (array $spec): ValidationRule {
            $words = self::stringList($spec['words'] ?? null, 'prohibited_words', 'words');

            return new ProhibitedWordsRule(...$words);
        });
    }

    /** @return list<string> */
    private static function stringList(mixed $value, string $type, string $key): array
    {
        if (!is_array($value) || $value === []) {
            throw new InvalidRuleConfiguration(sprintf('Rule "%s" requires a non-empty array "%s".', $type, $key));
        }

        $strings = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                throw new InvalidRuleConfiguration(sprintf('Rule "%s" expects "%s" to contain only strings.', $type, $key));
            }

            $strings[] = $item;
        }

        return $strings;
    }
}

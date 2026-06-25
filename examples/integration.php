<?php

declare(strict_types=1);

use DocumentValidation\Document;
use DocumentValidation\DocumentValidator;
use DocumentValidation\Exceptions\DocumentValidationException;
use DocumentValidation\RegistryRuleProvider;
use DocumentValidation\Rules\MaxDocumentSizeRule;
use DocumentValidation\Rules\ProhibitedWordsRule;
use DocumentValidation\Rules\RequiredContentRule;
use DocumentValidation\Rules\RequiredMetadataFieldsRule;
use DocumentValidation\ValidationResult;

require __DIR__ . '/../bootstrap.php';

function report(string $heading, ValidationResult $result): void
{
    echo PHP_EOL . $heading . PHP_EOL;

    if ($result->isValid) {
        echo '  PASSED - the document is valid.' . PHP_EOL;
        return;
    }

    echo '  FAILED with ' . count($result->errors) . ' error(s):' . PHP_EOL;
    foreach ($result->errors as $message) {
        echo '    - ' . $message . PHP_EOL;
    }
}

// Each tenant gets its own set of rules.
$rules = new RegistryRuleProvider();

$rules->register(
    'acme',
    new RequiredContentRule(),
    new MaxDocumentSizeRule(maxBytes: 1_024),
    new RequiredMetadataFieldsRule('author', 'department'),
    new ProhibitedWordsRule('confidential', 'secret'),
);

$rules->register(
    'globex',
    new MaxDocumentSizeRule(maxBytes: 64),
);

$validator = new DocumentValidator($rules);

echo "Rules configured for tenant 'acme':" . PHP_EOL;
foreach ($rules->rulesFor('acme') as $rule) {
    echo '  - ' . new ReflectionClass($rule)->getShortName() . PHP_EOL;
}

// Passes every acme rule.
$validDocument = new Document(
    id: 'doc-001',
    tenantId: 'acme',
    content: 'Quarterly figures look healthy and on target.',
    metadata: ['author' => 'Jane Doe', 'department' => 'Finance'],
);

report('Validating doc-001 (expected: PASS)', $validator->validate($validDocument));

// Too long, contains banned words, and missing the 'department' field.
$invalidDocument = new Document(
    id: 'doc-002',
    tenantId: 'acme',
    content: str_repeat('This SECRET memo is strictly confidential. ', 40),
    metadata: ['author' => 'John Roe'],
);

report('Validating doc-002 (expected: FAIL)', $validator->validate($invalidDocument));

// Unconfigured tenant: no rules, so nothing to fail.
$unknownTenantDocument = new Document('doc-003', 'initech', 'anything goes here');
report('Validating doc-003 for an unconfigured tenant (expected: PASS)', $validator->validate($unknownTenantDocument));

// Malformed input is rejected at construction.
try {
    new Document(id: '', tenantId: 'acme', content: 'orphaned content');
} catch (DocumentValidationException $e) {
    echo PHP_EOL . 'Rejected a malformed document as expected: ' . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . 'Done.' . PHP_EOL;

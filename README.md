# Document Validation

A small, tenant-aware document validator.

## How it works

A rule is just a class implementing `ValidationRule`: it looks at a `Document` and
returns a list of error messages (empty means it passed). `DocumentValidator` asks
a `RuleProvider` for the rules configured for that document's tenant, runs them all,
and collects every error into a `ValidationResult`.

Where the rules come from sits behind `RuleProvider`, so it's swappable:
`RegistryRuleProvider` wires them up in code, `ConfigRuleProvider` builds them from
declarative config via `RuleFactory`. The validator can't tell the difference, so a
database-backed provider would drop in the same way. Built-in rules cover max size,
required metadata, prohibited words, and empty content.

Bad configuration throws up front (the `Exceptions/` types, all catchable as
`DocumentValidationException`); a document that simply fails validation is reported
in the result, never thrown.

## Running

Requires PHP 8.4+.

```
composer install
composer test    # PHPUnit
composer demo    # examples/integration.php
composer stan    # PHPStan, max level
composer cs      # PSR-12 check
```

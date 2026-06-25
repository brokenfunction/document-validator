<?php

declare(strict_types=1);

/**
 * Autoloading bootstrap shared by the test suite and the example script.
 *
 * It prefers Composer's autoloader when dependencies are installed, but falls
 * back to a tiny PSR-4 loader so the project still runs with nothing more than a
 * PHP binary (and, for the tests, phpunit.phar). That makes the deliverable easy
 * to try out in a sandbox without a full `composer install`.
 */

$composerAutoload = __DIR__ . '/vendor/autoload.php';

if (is_file($composerAutoload)) {
    require $composerAutoload;
    return;
}

spl_autoload_register(static function (string $class): void {
    // Most specific prefix first so test classes are not mistaken for src classes.
    $prefixes = [
        'DocumentValidation\\Tests\\' => __DIR__ . '/tests/',
        'DocumentValidation\\'        => __DIR__ . '/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
        }

        return;
    }
});

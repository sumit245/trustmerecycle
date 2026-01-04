<?php
// Check script to verify PHP environment for TrustMeRecycle project

echo "=== TrustMeRecycle Environment Check ===\n\n";

// Check PHP version
$phpVersion = phpversion();
echo "PHP Version: $phpVersion\n";
$phpValid = version_compare($phpVersion, '8.2', '>=');
echo "PHP 8.2+ Requirement: " . ($phpValid ? "✓ SATISFIED" : "✗ NOT SATISFIED") . "\n\n";

// Check required extensions
$extensions = [
    'intl' => 'Required for Laravel Number::format() method',
    'mysqli' => 'Required for MySQL database connections',
    'pdo' => 'Required for database operations',
    'curl' => 'Required for HTTP requests',
    'openssl' => 'Required for secure connections',
    'gd' => 'Required for image processing (optional but recommended)',
];

echo "Extension Checks:\n";
$allExtensionsOk = true;
foreach ($extensions as $ext => $description) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "✓ LOADED" : "✗ MISSING";
    if (!$loaded && in_array($ext, ['intl', 'mysqli', 'pdo', 'curl', 'openssl'])) {
        $allExtensionsOk = false;
    }
    echo "- $ext: $status - $description\n";
}
echo "\n";

// Summary
echo "=== SUMMARY ===\n";
echo "PHP Version OK: " . ($phpValid ? "YES" : "NO") . "\n";
echo "Required Extensions OK: " . ($allExtensionsOk ? "YES" : "NO") . "\n";

if ($phpValid && $allExtensionsOk) {
    echo "\n✓ Your environment is ready for TrustMeRecycle!\n";
    echo "You can now run: composer install\n";
} else {
    echo "\n✗ Your environment needs updates before installing TrustMeRecycle.\n";
    if (!$phpValid) {
        echo "- Upgrade PHP to version 8.2 or higher\n";
    }
    if (!$allExtensionsOk) {
        echo "- Install missing PHP extensions\n";
    }
}
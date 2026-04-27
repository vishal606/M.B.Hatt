<?php
require_once __DIR__ . '/src/init.php';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.");
}

try {
    $sql = file_get_contents(__DIR__ . '/database/update_categories.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            Database::execute($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "\nDatabase updated successfully!\n";
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
}

<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    echo "Adding data_prevista column to tasques table...\n";
    $pdo->exec("ALTER TABLE tasques ADD COLUMN data_prevista DATE");
    echo "Column added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists. Skipping.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>

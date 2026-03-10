<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    // Force drop to ensure clean state with correct collation
    $pdo->exec("DROP DATABASE IF EXISTS gestio_explotacio");
    
    $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
    
    // Remove comments to avoid issues with some drivers if not handled correctly, 
    // though PDO usually handles multiple queries if emulation is on.
    // For safety, let's just try executing it.
    
    $pdo->exec($sql);
    echo "Base de dades inicialitzada correctament.\n";
    
} catch (Throwable $e) {
    echo "Error inicialitzant la base de dades: " . $e->getMessage() . "\n";
}

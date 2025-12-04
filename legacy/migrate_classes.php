<?php
require_once 'includes/config.php';

try {
    echo "Adding location columns to classes table...\n";

    $sql = "ALTER TABLE classes 
            ADD COLUMN latitude DECIMAL(10, 8) DEFAULT NULL,
            ADD COLUMN longitude DECIMAL(11, 8) DEFAULT NULL,
            ADD COLUMN radius INT DEFAULT 50";

    $pdo->exec($sql);
    echo "Successfully added columns: latitude, longitude, radius.\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Columns already exist. Skipping.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
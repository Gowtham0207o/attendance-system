<?php
// public/api/project/add.php

// Set JSON header
header('Content-Type: application/json');

// Include config for DB connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/attendance-system/config/config.php';

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Read POST data
    $name        = trim($_POST['name'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $start_date  = trim($_POST['start_date'] ?? '');

    // Validation
    if (empty($name) || empty($start_date)) {
        echo json_encode([
            'success' => false,
            'message' => 'Project name and start date are required.'
        ]);
        exit;
    }

    // Insert into DB
    $stmt = $pdo->prepare("
        INSERT INTO projects (name, location, start_date)
        VALUES (:name,  :location, :start_date)
    ");
 $newId = $pdo->lastInsertId();
    $stmt->execute([
        ':name'        => $name,
        ':location'    => $location,
        ':start_date'  => $start_date
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Project added successfully.',
          'new_project' => [
            'id' => $newId,
            'name' => $name
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
}

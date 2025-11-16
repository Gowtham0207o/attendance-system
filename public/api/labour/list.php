<?php
// labour/list.php

// Enable error reporting (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

// Include DB connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/attendance-system/config/config.php';


try {
    // Connect to MySQL
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get filter from query string
    $filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

    // Prepare SQL query
    if ($filter !== '') {
        $stmt = $pdo->prepare("SELECT * FROM labours WHERE name LIKE :f OR skill_type LIKE :f ORDER BY name ASC");
        $stmt->execute([':f' => "%$filter%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM labours ORDER BY name ASC");
    }

    $labours = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $labours
    ]);

} catch (PDOException $e) {
    // Return error as JSON
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    http_response_code(500);
}

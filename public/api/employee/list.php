<?php
// employee/list.php

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

    // Get optional search filter
    $filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

    // ✅ Query: fetch all active employees (non-labours)
    if ($filter !== '') {
        $stmt = $pdo->prepare("
            SELECT id, name, role, salary, email, phone 
            FROM employees
            WHERE name LIKE :f OR role LIKE :f 
            ORDER BY name ASC
        ");
        $stmt->execute([':f' => "%$filter%"]);
    } else {
        $stmt = $pdo->query("
            SELECT id, name, role, salary, email, phone
            FROM employees
            ORDER BY name ASC
        ");
    }

    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $employees
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    http_response_code(500);
}

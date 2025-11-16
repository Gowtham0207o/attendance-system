<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/attendance-system/config/config.php';
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query("SELECT id, name FROM projects ORDER BY id DESC");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $projects]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

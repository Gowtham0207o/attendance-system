<?php
// public/api/team/list.php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name FROM teams ORDER BY name ASC");
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data'    => $teams
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

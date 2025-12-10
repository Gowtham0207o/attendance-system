<?php
// public/api/team/add.php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
// Read raw body and try to decode JSON first
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

// If JSON parse fails or input empty, fallback to $_POST
$data = is_array($body) ? $body : $_POST;


$name = trim($data['name'] ?? $data['Name'] ?? '');
$description = trim($data['description'] ?? $data['desc'] ?? '');

if ($name === '') {
    echo json_encode(['success' => false, 'message' => 'Team name is required']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO teams (name, description, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$name, $description]);

    echo json_encode([
        'success' => true,
        'id'      => $pdo->lastInsertId(),
        'name'    => $name
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

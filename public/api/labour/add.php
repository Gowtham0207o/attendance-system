<?php
// public/api/labour/add.php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

// Read JSON body if sent
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
$data = is_array($body) ? $body : $_POST;

$name  = trim($data['name'] ?? '');
$username = trim($data['username'] ?? '');
$role  = trim($data['role'] ?? '');
$skill = trim($data['skill'] ?? '');
$phone = trim($data['phone'] ?? '');

$errors = [];
// if ($name === '') $errors[] = 'Name is required';
// if ($role === '') $errors[] = 'Role is required';
if ($username === '') $username = preg_replace('/\s+/', '', strtolower($name));

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $pdo = db(); // uses app/bootstrap.php db() helper
    $stmt = $pdo->prepare("INSERT INTO labours (name, username, address, skill_type, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $username, $role, $skill, $phone]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Labour added',
        'new_labour' => [
            'id' => (int)$newId,
            'name' => $name,
            'role' => $role,
            'skill' => $skill,
            'phone' => $phone
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}

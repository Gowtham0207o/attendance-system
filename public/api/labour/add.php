<?php
// public/api/labour/add.php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

// Read raw JSON body if present
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
$data = is_array($body) ? $body : $_POST;

// Normalize incoming keys (accept camelCase or snake_case)
$name     = trim($data['name'] ?? $data['Name'] ?? '');
$username = trim($data['username'] ?? $data['userName'] ?? $data['user'] ?? '');
$address     = trim($data['address'] ?? '');
$skill    = trim($data['skill'] ?? '');
$phone    = trim($data['phone'] ?? '');
$teamId   = trim($data['teamId'] ?? $data['team_id'] ?? $data['team'] ?? '');
$salary   = trim($data['salary'] ?? $data['Salary'] ?? '');

$errors = [];

// Basic validation - change required fields as you need
// if ($name === '') $errors[] = 'Name is required';
// if ($teamId === '') $errors[] = 'teamId is required';

// Auto-generate username if not provided
if ($username === '') {
    // basic pattern: name+role, lowercase, no spaces
    $username = strtolower(preg_replace('/\s+/', '', $name . $skill));
}

// If there are validation errors, return 400 with details
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $pdo = db(); // expects db() helper in app/bootstrap.php returning PDO

    // Ensure your `labours` table has these columns: name, username, role, skill_type, phone, salary, team_id, created_at
    $sql = "INSERT INTO labours
            (name, username, address, skill_type, phone, salary, team_id, created_at)
            VALUES (:name, :username, :address, :skill_type, :phone, :salary, :team_id, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'       => $name,
        ':username'   => $username,
        ':address'       => $address,
        ':skill_type' => $skill !== '' ? $skill : null,
        ':phone'      => $phone !== '' ? $phone : null,
        ':salary'     => $salary !== '' ? $salary : null,
        ':team_id'    => $teamId
    ]);

    $newId = (int)$pdo->lastInsertId();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Labour added',
        'new_labour' => [
            'id'      => $newId,
            'name'    => $name,
            'username'=> $username,
            'address'    => $address,
            'skill'   => $skill,
            'phone'   => $phone,
            'salary'  => $salary,
            'teamId'  => $teamId
        ]
    ]);
    exit;
} catch (PDOException $e) {
    // In production you may want to log $e->getMessage() and return a generic message instead
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}

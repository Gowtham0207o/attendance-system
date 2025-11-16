<?php
// public/api/employee/add.php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

// Read JSON body (if any)
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

// If JSON decode produced an array, prefer it; otherwise fall back to $_POST
$data = is_array($body) ? $body : $_POST;

// Trim values
$name     = trim($data['name'] ?? '');
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$role     = trim($data['role'] ?? 'engineer');
$salary   = $data['salary'] ?? 0;

// Basic validation
$errors = [];
if ($name === '')    $errors[] = 'Name is required';
if ($username === '') $errors[] = 'Username is required';
if (!is_numeric($salary)) $salary = 0;

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Hash password (use password_hash in real apps)
if ($password === '') {
    $password = '123456';
}
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = db()->prepare("INSERT INTO employees (name, username, password, role, salary) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $username, $hash, $role, $salary]);

    $newId = db()->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Employee added successfully',
        'employee_id' => $newId
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}

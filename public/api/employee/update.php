<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$salary = $data['salary'] ?? null;
$status = $data['status'] ?? 1;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing employee ID']);
    exit;
}

$stmt = db()->prepare("UPDATE employees SET salary_amount=?, status=? WHERE id=?");
$stmt->execute([$salary, $status, $id]);

echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);

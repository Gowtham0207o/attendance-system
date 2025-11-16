<?php
require_once __DIR__ . '/../../../app/bootstrap.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$project_id = $data['project_id'] ?? null;
$labour_id = $data['labour_id'] ?? null;
$shift = $data['shift'] ?? 'day';
$status = $data['status'] ?? 'present';
$date = $data['date'] ?? date('Y-m-d');
$created_by = $_SESSION['user_id'] ?? 1;

if (!$project_id || !$labour_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields $labour']);
    exit;
}

try {
    $stmt = db()->prepare("
        INSERT INTO attendance (project_id, labour_id, date, shift, status, marked_by)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE status = VALUES(status)
    ");
    $stmt->execute([$project_id, $labour_id, $date, $shift, $status, $created_by]);

    echo json_encode(['success' => true, 'message' => 'Attendance marked successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
// public/api/attendance/fetch_summary.php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json');

$date = $_GET['date'] ?? null;
if (!$date) {
    echo json_encode(['success' => false, 'message' => 'Invalid date']);
    exit;
}

try {
    $pdo = db();

    // Determine which date column exists: attendance_date or date
    $cols = $pdo->query("SHOW COLUMNS FROM attendance")->fetchAll(PDO::FETCH_COLUMN);
    $dateCol = in_array('attendance_date', $cols) ? 'attendance_date' : (in_array('date', $cols) ? 'date' : null);

    if (!$dateCol) {
        echo json_encode(['success' => false, 'message' => 'No date column found in attendance table']);
        exit;
    }

    // Use safe alias names (avoid reserved words)
    $sql = "
        SELECT 
            SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present_count,
            SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
            SUM(CASE WHEN status = 'Leave'  THEN 1 ELSE 0 END) AS leave_count
        FROM attendance
        WHERE {$dateCol} = :date
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'date' => $date,
        'present' => intval($row['present_count'] ?? 0),
        'absent'  => intval($row['absent_count'] ?? 0),
        'leave'   => intval($row['leave_count'] ?? 0)
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
